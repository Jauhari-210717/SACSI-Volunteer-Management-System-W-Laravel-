<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Models\VolunteerProfile;
use App\Models\ImportLog;
use App\Models\FactLog;

class VolunteerImportController extends Controller
{
    public function index()
    {
        // Persistent session values
        $validEntries = session('validEntries', []);
        $invalidEntries = session('invalidEntries', []);
        $uploadedFileName = session('uploaded_file_name', null);
        $uploadedFilePath = session('uploaded_file_path', null);

        $importLogs = ImportLog::orderBy('created_at', 'desc')->get();

        return view('volunteer_import.volunteer_import', compact(
            'validEntries', 'invalidEntries', 'uploadedFileName', 'uploadedFilePath', 'importLogs'
        ));
    }


    /**
     * Preview CSV import
     */
    public function preview(Request $request)
    {
        // Validate uploaded CSV
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $filename = $file->getClientOriginalName();
        $path = $file->store('uploads', 'public');

        // Keep uploaded file info in session (for UI)
        session([
            'uploaded_file_name' => $filename,
            'uploaded_file_path' => $path,
            'csv_imported'       => true,
        ]);

        $admin = Auth::guard('admin')->user();

        // Create ImportLog entry immediately (status = Pending)
        $importLog = ImportLog::create([
            'file_name'       => $filename,
            'admin_id'        => $admin->admin_id ?? null,
            'total_records'   => 0,
            'valid_count'     => 0,
            'invalid_count'   => 0,
            'duplicate_count' => 0,
            'status'          => 'Pending',
            'remarks'         => null,
        ]);

        // Save the import ID in session
        session(['import_log_id' => $importLog->import_id]);

        // Read CSV rows
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        if (empty($rows)) {
            $importLog->update([
                'remarks'       => 'Preview completed: 0 rows found.',
                'total_records' => 0,
            ]);
            return back()->with('error', 'CSV file is empty.');
        }

        // Extract header
        $header = array_map('strtolower', array_map('trim', array_shift($rows)));

        $valid = [];
        $invalid = [];
        $duplicates = [];
        $seenKeys = [];

        foreach ($rows as $i => $row) {
            $data = $this->normalizeRow($row, $header);
            $errors = $this->validateRow($data);

            // District & Barangay validation
            $district = trim($data['district'] ?? '');
            $barangay = trim($data['barangay'] ?? '');

            if (empty($district)) $errors['district'] = 'No district selected.';
            if (empty($barangay)) {
                $errors['barangay'] = 'No barangay selected.';
            } elseif (!empty($district)) {
                $exists = DB::table('locations')
                            ->where('barangay', $barangay)
                            ->where('district_id', $district)
                            ->exists();
                if (!$exists) $errors['barangay'] = "Barangay \"$barangay\" not found in District \"$district\".";
            }

            // Column count mismatch guard
            if (count($row) !== count($header)) {
                $errors = array_fill_keys(array_keys($data), true);
            }

            $data['row_number'] = $i + 2;
            $uniqueKey = strtolower($data['email'] ?? $data['full_name'] ?? 'row_' . $i);

            if (in_array($uniqueKey, $seenKeys)) {
                $duplicates[] = $data;
            } elseif (!empty($errors)) {
                $data['errors'] = $errors;
                $invalid[] = $data;
                $seenKeys[] = $uniqueKey;
            } else {
                $valid[] = $data;
                $seenKeys[] = $uniqueKey;
            }
        }

        // Persist preview arrays in session
        session([
            'validEntries'     => $valid,
            'invalidEntries'   => $invalid,
            'duplicateEntries' => $duplicates,
        ]);

        // Update import log counts
        $importLog->update([
            'total_records'   => count($rows),
            'valid_count'     => count($valid),
            'invalid_count'   => count($invalid),
            'duplicate_count' => count($duplicates),
            'status'          => 'Pending',
            'remarks'         => sprintf(
                'Preview completed: %d valid, %d invalid, %d duplicates.',
                count($valid), count($invalid), count($duplicates)
            ),
        ]);

        // Log preview action
        if ($admin) {
            $this->logFact(
                'Preview Import',
                $admin->admin_id,
                'Volunteer Import',
                $importLog->import_id,
                'Previewed',
                [
                    'total'      => count($rows),
                    'valid'      => count($valid),
                    'invalid'    => count($invalid),
                    'duplicates' => count($duplicates),
                ]
            );
        }

        // Build UI message
        $message = "<div style='display:flex; flex-wrap: wrap; gap:10px; align-items:center;'>";
        $message .= "<span style='color:blue;'>✅ " . count($valid) . " valid</span>";
        $message .= "<span style='color:red;'>❌ " . count($invalid) . " invalid</span>";
        $message .= "<span style='color:orange;'>⚠️ " . count($duplicates) . " duplicates</span>";
        $message .= "</div>";

        if (count($invalid) === 0 && count($valid) > 0) {
            $message .= "<div style='margin-top:5px; color:green;'>
                            All entries are valid. You may proceed to submit them. 
                            <a href='#valid-entries-section' style='text-decoration:underline;'>View valid entries</a>
                        </div>";
        } elseif (count($valid) === 0 && count($invalid) > 0) {
            $message .= "<div style='margin-top:5px; color:red;'>
                            All entries are invalid. Please correct the invalid entries before submitting any data.
                        </div>";
        } else {
            $message .= "<div style='margin-top:5px; color:orange;'>
                            Some entries are invalid. Please fix the invalid entries before submitting the valid ones.
                        </div>";
        }

        return back()->with('success', $message);
    }


    /**
     * Helper to create an import log (add this to the same controller)
     */
    protected function createImportLog($filename, $adminId)
    {
        return ImportLog::create([
            'file_name'       => $filename,
            'admin_id'        => $adminId,
            'total_records'   => 0,
            'valid_count'     => 0,
            'invalid_count'   => 0,
            'duplicate_count' => 0,
            'status'          => 'preview',
            'remarks'         => null,
        ]);
    }

    /**
     * Normalize row: strip comments, format numbers, ensure all keys exist
     */
    private function normalizeRow(array $row, array $header): array
    {
        $mapping = [
            'name' => 'full_name',
            'full_name' => 'full_name',
            'full name' => 'full_name',
            'id number' => 'id_number',
            'school id' => 'id_number',
            'id num' => 'id_number',
            'id' => 'id_number',
            'email address' => 'email',
            'email' => 'email',
            'phone' => 'contact_number',
            'contact number' => 'contact_number',
            'contact' => 'contact_number',
            'emergency' => 'emergency_contact',
            'emergency contact' => 'emergency_contact',
            'fb' => 'fb_messenger',
            'fb/messenger' => 'fb_messenger',
            'messenger' => 'fb_messenger',
            'barangay' => 'barangay',
            'district' => 'district',
            'course' => 'course',
            'year' => 'year_level',
            'year level' => 'year_level',
        ];

        $normalized = [];

        foreach ($header as $index => $col) {
            $key = strtolower(trim($col));
            $key = str_replace([' ', '-'], '_', $key);
            if (isset($mapping[$key])) $key = $mapping[$key];

            $value = (string)($row[$index] ?? '');
            $value = preg_replace('/#.*/', '', $value); // Strip inline comments
            $value = trim($value);
            $value = in_array($value, ['-', 'N/A']) ? '' : $value;

            if ($key === 'id_number') $value = strtoupper($value);
            if (in_array($key, ['contact_number', 'emergency_contact'])) {
                $value = preg_replace('/[^\d+]/', '', $value);
            }

            if (in_array($key, ['barangay', 'district'])) $value = trim($value);

            $normalized[$key] = $value;
        }

        // Ensure all expected keys exist
        foreach ([
            'full_name', 'id_number', 'email', 'contact_number', 'emergency_contact',
            'fb_messenger', 'barangay', 'district', 'course', 'year_level'
        ] as $key) {
            if (!isset($normalized[$key])) $normalized[$key] = '';
        }

        return $normalized;
    }

    /**
     * Validate each row's fields
     */
    private function validateRow(array $data)
    {
        $errors = [];

        // Full name
        if (empty($data['full_name']) || !preg_match("/^[A-Za-zÑñ\s\.\'-]+$/u", $data['full_name'])) {
            $errors['full_name'] = 'Full Name is required and can only contain letters, spaces, dots, hyphens, or apostrophes.';
        }

        // School ID
        if (empty($data['id_number']) || !preg_match('/^\d{6,7}$/', $data['id_number'])) {
            $errors['id_number'] = 'School ID must be 6 or 7 digits.';
        }

        // Course
        if (empty($data['course']) || !preg_match('/^[A-Za-z\s]+$/', $data['course'])) {
            $errors['course'] = 'Course is required and can only contain letters and spaces.';
        }

        // Year level
        if (empty($data['year_level']) || !in_array($data['year_level'], ['1','2','3','4'])) {
            $errors['year_level'] = 'Year must be 1, 2, 3, or 4.';
        }

        // Contact numbers
        foreach (['contact_number', 'emergency_contact'] as $field) {
            if (empty($data[$field]) || !preg_match('/^(09\d{9}|\+639\d{9})$/', $data[$field])) {
                $errors[$field] = ucfirst(str_replace('_',' ',$field)) . ' must be a valid Philippine mobile number.';
            }
        }

        // Email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) 
            || !preg_match('/^[A-Za-z0-9._%+-]+@(gmail\.com|adzu\.edu\.ph)$/i', $data['email'])) {
            $errors['email'] = 'Email must be valid and end with @gmail.com or @adzu.edu.ph.';
        }

        // FB/Messenger (optional)
        if (!empty($data['fb_messenger'])) {
            $fb = $data['fb_messenger'];
            if (!filter_var($fb, FILTER_VALIDATE_URL) || stripos(parse_url($fb, PHP_URL_HOST) ?: '', 'facebook.com') === false) {
                $errors['fb_messenger'] = 'FB/Messenger must be a valid Facebook link.';
            }
        }

        // District & Barangay
        $district = trim($data['district'] ?? '');
        $barangay = trim($data['barangay'] ?? '');

        if (empty($district)) $errors['district'] = 'No district selected.';
        if (empty($barangay)) {
            $errors['barangay'] = 'No barangay selected.';
        } elseif (!empty($district)) {
            $exists = DB::table('locations')
                ->where('barangay', $barangay)
                ->where('district_id', $district) // <-- use district_id
                ->exists();
            if (!$exists) $errors['barangay'] = "Barangay \"$barangay\" not found in District \"$district\".";
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Update Individual Entries/Correct Input Erros
    */
    public function updateVolunteerEntry(Request $request, $index, $type)
    {
        $entries = session($type . 'Entries', []);
        if (!isset($entries[$index])) {
            return back()->with('error', '⚠️ Entry not found.');
        }

        $entry = $entries[$index];
        $input = array_map('trim', $request->all());

        // Normalize contact numbers and ID
        foreach (['contact_number', 'emergency_contact'] as $field) {
            if (isset($input[$field])) {
                $input[$field] = preg_replace('/[^\d+]/', '', $input[$field]);
            }
        }
        if (isset($input['id_number'])) $input['id_number'] = strtoupper($input['id_number']);

        // Validation rules
        $rules = [
            'full_name' => ['required','regex:/^[A-Za-zÑñ\s\.\'-]+$/u','max:255'],
            'id_number' => ['required','regex:/^\d{6,7}$/'],
            'course' => 'required|string|max:100',
            'year_level' => ['required','in:1,2,3,4'],
            'contact_number' => ['required','regex:/^(09\d{9}|\+639\d{9})$/'],
            'emergency_contact' => ['required','regex:/^(09\d{9}|\+639\d{9})$/'],
            'email' => ['required','email','regex:/^[A-Za-z0-9._%+-]+@(gmail\.com|adzu\.edu\.ph)$/i'],
            'fb_messenger' => ['nullable'],
            'barangay' => ['required'],
            'district' => ['required'],
        ];

        $messages = [
            'year_level.in' => 'Year must be 1, 2, 3, or 4.',
            'district.required' => 'No district selected.',
            'barangay.required' => 'No barangay selected.',
        ];

        $validator = \Validator::make($input, $rules, $messages);
        $errors = $validator->fails() ? $validator->errors()->toArray() : [];

        // Custom FB/Messenger validation
        if (!empty($input['fb_messenger'])) {
            $fb = $input['fb_messenger'];
            if (!filter_var($fb, FILTER_VALIDATE_URL) || stripos(parse_url($fb, PHP_URL_HOST) ?: '', 'facebook.com') === false) {
                $errors['fb_messenger'] = ['FB/Messenger must be a valid Facebook link'];
            }
        }

        // Custom Barangay + District validation
        if (!empty($input['barangay'])) {
            $barangay = $input['barangay'];
            $districtId = $input['district_id'] ?? null; // hidden field from JS

            if (!$districtId) {
                $errors['district'] = ['No district selected.'];
            } else {
                $exists = DB::table('locations')
                    ->where('barangay', $barangay)
                    ->where('district_id', $districtId)
                    ->exists();
                if (!$exists) {
                    $errors['barangay'] = ["Barangay \"{$barangay}\" and District ID \"{$districtId}\" not found."];
                }
            }
        }

        // Update session entries
        $updatedFields = [];
        foreach ($input as $field => $value) {
            if (!isset($errors[$field])) {
                $entries[$index][$field] = $value;
                $updatedFields[$field] = $value;
            }
        }
        $entries[$index]['errors'] = $errors;
        session([$type . 'Entries' => $entries]);

        // Update DB if volunteer exists
        $volunteerId = $entries[$index]['volunteer_id'] ?? null;
        if (!empty($volunteerId) && !empty($updatedFields)) {
            $volunteer = Volunteer::find($volunteerId);
            if ($volunteer) $volunteer->update(array_merge($updatedFields, ['status' => 'Active']));
        }

        // Log changes in clean human-readable style
        $adminId = Auth::guard('admin')->id();
        if ($adminId && !empty($updatedFields)) {
            $fullName = $updatedFields['full_name'] ?? $entry['full_name'] ?? 'Unknown';
            $fieldDetails = [];

            $labels = [
                'full_name'=>'Full Name','id_number'=>'School ID','course'=>'Course','year_level'=>'Year',
                'contact_number'=>'Contact #','emergency_contact'=>'Emergency #','email'=>'Email',
                'fb_messenger'=>'FB/Messenger','barangay'=>'Barangay','district'=>'District'
            ];

            foreach ($updatedFields as $field => $value) {
                if (isset($labels[$field])) {
                    $fieldDetails[] = "{$labels[$field]}='{$value}'";
                }
            }

            $this->logFact(
                'Update Entry',                  // fact_type
                $adminId,                        // admin ID
                'Volunteer Import',              // entity_type
                $volunteerId ?? null,            // entity_id
                'Updated',                       // action
                "Updated entry #".($index+1)." '{$fullName}': ".implode(', ', $fieldDetails)."." // details
            );

        }

        // Build human-readable flash message
        $labels = [
            'full_name'=>'Full Name','id_number'=>'School ID','course'=>'Course','year_level'=>'Year',
            'contact_number'=>'Contact #','emergency_contact'=>'Emergency #','email'=>'Email',
            'fb_messenger'=>'FB/Messenger','barangay'=>'Barangay','district'=>'District'
        ];

        $rowNumber = $index + 1;
        $validParts = [];
        $invalidParts = [];

        foreach ($updatedFields as $field => $value) {
            if (isset($labels[$field])) $validParts[] = "{$labels[$field]}: {$value}";
        }
        foreach ($errors as $field => $msgs) {
            if (isset($labels[$field])) $invalidParts[] = "{$labels[$field]}: ".($input[$field]??'')." (".implode(', ', (array)$msgs).")";
        }

        $message = "Row #{$rowNumber}<br>";
        if ($validParts) $message .= "✅ Valid: <span style='color:#007bff;'>".implode(' <span style=\"color:#555;font-weight:normal;\">|</span> ',$validParts)."</span>";
        if ($invalidParts) {
            if ($validParts) $message .= "<hr style='margin:4px 0;border:none;border-top:1px solid #555;'>";
            $message .= "⚠️ Invalid: <span style='color:red;'>".implode(' <span style=\"color:#555;font-weight:normal;\">|</span> ',$invalidParts)."</span>";
        }
        if (!$validParts && !$invalidParts) $message .= "ℹ️ No changes were made.";

        $flashType = $validParts ? 'success' : 'error';

        return redirect()->route('volunteer.import.index')
            ->with($flashType, $message)
            ->with('last_updated_table', $type)
            ->with('last_updated_index', $index);
    }

    /**
     * Move from Invalid -> Valid
     */
    public function moveInvalidToValid(Request $request)
    {
        $invalid = session('invalidEntries', []);
        $valid = session('validEntries', []);
        $movedNames = [];
        $skippedNames = [];
        $adminId = auth()->guard('admin')->id();

        $selected = array_map('intval', $request->input('selected_invalid', []));

        if (!empty($selected)) {
            foreach ($selected as $index) {
                if (!array_key_exists($index, $invalid)) continue;

                $entry = $invalid[$index];

                // If it has errors, skip
                if (!empty($entry['errors'] ?? [])) {
                    $skippedNames[] = $entry['full_name'] ?? 'N/A';
                    continue;
                }

                // Move entry
                $entry['original_index'] = $index;
                unset($entry['errors'], $entry['error_message']);

                $valid[] = $entry;
                $movedNames[] = $entry['full_name'] ?? 'N/A';

                unset($invalid[$index]);

                // Log the move
            $this->logFact(
                    'Move to Verified',              // fact_type
                    $adminId,                        // admin ID
                    'Volunteer Import',              // entity_type
                    $entry['volunteer_id'] ?? null, // entity_id
                    'Moved',                         // action
                    "Moved invalid entry '{$entry['full_name']}' (row #{$index}) to valid." // details
                );


            }

            $invalid = array_values($invalid);
            $valid = array_values($valid);

            session([
                'invalidEntries' => $invalid,
                'validEntries' => $valid,
            ]);
        }

        $messageParts = [];
        if ($movedNames) $messageParts[] = "✅ Moved to verified: <span style='color:#007bff;'>" . implode(', ', $movedNames) . "</span>.";
        if ($skippedNames) $messageParts[] = "⚠️ Could not move (invalid fields): <span style='color:red;'>" . implode(', ', $skippedNames) . "</span>.";
        if (!$movedNames && !$skippedNames) $messageParts[] = "ℹ️ No invalid entries selected to move.";

        return back()->with('success', implode(' ', $messageParts));
    }

    /**
     * Move from Valid -> Invalid
     */
    public function moveValidToInvalid(Request $request, $index)
    {
        $valid = session('validEntries', []);
        $invalid = session('invalidEntries', []);
        $adminId = auth()->guard('admin')->id();

        if (!isset($valid[$index])) {
            return back()->withFragment('invalid-entries-table')
                        ->with('invalid_error', "ℹ️ No valid entry selected to move back.");
        }

        $entry = $valid[$index];
        unset($valid[$index]);

        // Add back to invalid
        if (isset($entry['original_index'])) {
            $invalid[$entry['original_index']] = $entry;
        } else {
            $invalid[] = $entry;
        }

        ksort($invalid);
        $invalid = array_values($invalid);

        session([
            'validEntries' => array_values($valid),
            'invalidEntries' => $invalid,
            'last_updated_table' => 'invalid',
            'last_updated_index' => isset($entry['original_index']) ? $entry['original_index'] : count($invalid) - 1,
            'invalid_success' => "⚠️ Moved back to invalid: <span style='color:red;'>{$entry['full_name']}</span>."
        ]);

        // Log the move back
        $this->logFact(
            'Move to Invalid',               // fact_type
            $adminId,                        // admin ID
            'Volunteer Import',              // entity_type
            $entry['volunteer_id'] ?? null, // entity_id
            'Moved Back',                    // action
            "Moved valid entry '{$entry['full_name']}' (row #{$index}) back to invalid." // details
        );


        return back()->withFragment('invalid-entries-table');
    }

    /**
     * Delete Entries
    */
    public function deleteEntries(Request $request)
    {
        $tableType = $request->input('table_type'); // invalid / valid / logs
        $selected = $request->input('selected', []);
        $adminId = auth()->guard('admin')->id();

        if (empty($selected)) {
            return back()->with('error', 'ℹ️ No entries selected for deletion.');
        }

        $deletedData = [];

        switch ($tableType) {
            case 'invalid':
            case 'valid':
                $entries = session($tableType . 'Entries', []);
                foreach ($selected as $index) {
                    if (isset($entries[$index])) {
                        $deletedData[$index] = $entries[$index];
                        unset($entries[$index]);

                        // Log deletion with proper name fallback
                        $volunteerId = $deletedData[$index]['volunteer_id'] ?? null;
                        $name = !empty($deletedData[$index]['full_name']) ? $deletedData[$index]['full_name'] : 'No Name';

                        $this->logFact(
                            'Delete Entry',                  // fact_type
                            $adminId,                        // admin ID
                            'Volunteer Import',              // entity_type
                            $volunteerId ?? null,            // entity_id
                            'Deleted',                       // action
                            "Deleted Entry #".($index+1)." {$name}" // details
                        );
                    }
                }
                session([$tableType . 'Entries' => array_values($entries)]);
                break;

            case 'logs':
                $deletedEntries = ImportLog::whereIn('import_id', $selected)->get();
                foreach ($deletedEntries as $entry) {
                    $deletedData[] = $entry->toArray();
                    $name = !empty($entry->file_name) ? $entry->file_name : 'No Name';

                    $this->logFact(
                        'Delete Import Log',             // fact_type
                        $adminId,                        // admin ID
                        'Volunteer Import',              // entity_type
                        $entry->import_id,               // entity_id
                        'Deleted',                       // action
                        "Deleted import log '{$name}' (ID {$entry->import_id})" // details
                    );
                }
                ImportLog::whereIn('import_id', $selected)->delete();
                break;

            default:
                return back()->with('error', '⚠️ Invalid table type.');
        }

        if (!empty($deletedData)) {
            session(['deletedEntriesUndo' => [
                'tableType' => $tableType,
                'data' => $deletedData,
                'timestamp' => now()
            ]]);

            $deletedList = [];
            foreach ($deletedData as $index => $item) {
                $name = !empty($item['full_name']) ? $item['full_name'] : (!empty($item['file_name']) ? $item['file_name'] : 'No Name');
                $deletedList[] = "#".($index+1)." ".$name;
            }

            $message = "<div style='display:flex; flex-wrap:wrap; gap:5px; align-items:center;' >
                            ✅ Deleted Entry: " . implode(', ', $deletedList) . "
                            <a href='" . route('volunteer.import.undo-delete') . "' 
                            style='margin-left:10px; padding:4px 10px; font-size:0.9em; background:#007bff; color:white; text-decoration:none; border-radius:4px; cursor:pointer;' >
                            Undo
                            </a>
                        </div>";
        } else {
            $message = "ℹ️ No entries were deleted.";
        }

        return back()->with('success', $message);
    }
    
    /**
     * Undo Deleted Entries
    */
    public function undoDelete(Request $request)
    {
        $deleted = session('deletedEntriesUndo');
        $adminId = auth()->guard('admin')->id();

        if (!$deleted || empty($deleted['data']) || !isset($deleted['tableType'])) {
            return back()->with('error', 'ℹ️ Nothing to undo.');
        }

        $tableType = $deleted['tableType'];
        $data = $deleted['data'];

        switch ($tableType) {
            case 'invalid':
            case 'valid':
                $entries = session($tableType . 'Entries', []);
                $entries = array_merge($entries, $data);
                session([$tableType . 'Entries' => array_values($entries)]);
                break;

            case 'logs':
                foreach ($data as $index => $item) {
                    if (!ImportLog::where('import_id', $item['import_id'])->exists()) {
                        ImportLog::create($item);

                        $entityId = $item['import_id'] ?? null;
                        $name = !empty($item['file_name']) ? $item['file_name'] : 'No Name';

                    $this->logFact(
                            'Restore Entry',                 // fact_type
                            $adminId,                        // admin ID
                            'Volunteer Import',              // entity_type
                            $entityId,                       // entity_id
                            'Restored',                       // action
                            "Restored Entry #".($index+1)." {$name}" // details
                        );
                    }
                }
                break;

            default:
                return back()->with('error', '⚠️ Invalid table type for undo.');
        }

        // Log restoration for entries (invalid/valid)
        if ($tableType !== 'logs') {
            foreach ($data as $index => $item) {
                $name = !empty($item['full_name']) ? $item['full_name'] : 'No Name';
                $entityId = $item['volunteer_id'] ?? null;

                $this->logFact(
                    'Restore Import Log',            // fact_type
                    $adminId,                        // admin ID
                    'Volunteer Import',              // entity_type
                    $entityId,                       // entity_id
                    'Restored',                       // action
                    "Restored import log '{$name}' (ID {$entityId})" // details
                );
            }
        }

        session()->forget('deletedEntriesUndo');

        $restoredList = [];
        foreach ($data as $index => $item) {
            $name = !empty($item['full_name']) ? $item['full_name'] : (!empty($item['file_name']) ? $item['file_name'] : 'No Name');
            $restoredList[] = "#".($index+1)." ".$name;
        }

        $message = "<div style='display:flex; flex-wrap:wrap; gap:5px; align-items:center;' >
                        ✅ Restored Entry: " . implode(', ', $restoredList) . "
                    </div>";

        return back()->with('success', $message);
    }

/**
 * Validate and Save Selected Valid Entries
 */
/**
 * Validate and Save Selected Valid Entries
 */
/**
 * Validate and Save Selected Valid Entries
 */
public function validateAndSave(Request $request)
{
    $selectedIndexes = $request->input('selected_valid', []);
    $validEntries = session('validEntries', []);
    $invalidEntries = session('invalidEntries', []);
    $admin = Auth::guard('admin')->user();
    $fileName = session('uploaded_file_name', 'N/A');
    $formattedTime = now()->format('M d, Y h:i A');

    // ✅ Ensure admin is logged in
    if (!$admin) {
        $message = 'Admin not authenticated.';
        return $request->ajax()
            ? response()->json(['error_modal' => $message])
            : back()->with('error_modal', $message);
    }

    $adminId = $admin->admin_id;

    // ✅ Prevent import if any invalid entries exist
    if (!empty($invalidEntries)) {
        $invalidRows = implode(', ', array_column($invalidEntries, 'row_number'));
        $this->logFact(
            'Failed Import',
            $adminId,
            'Volunteer Import',
            null,
            'Failed',
            [
                'invalid_rows' => $invalidRows,
                'invalid_count' => count($invalidEntries),
                'reason' => 'Invalid entries prevent import'
            ]
        );

        $message = "❌ Cannot upload. Invalid entries found in row(s): <strong>{$invalidRows}</strong>. Please fix them first.";
        return $request->ajax()
            ? response()->json(['error_modal' => $message])
            : back()->with('error_modal', $message);
    }

    // ✅ Require at least one selected entry
    if (empty($selectedIndexes)) {
        $message = '❌ No verified entries selected to save.';
        return $request->ajax()
            ? response()->json(['error_modal' => $message])
            : back()->with('error_modal', $message);
    }

    $entriesToSave = [];

    // ✅ Re-validate selected entries before saving
    foreach ($selectedIndexes as $index) {
        if (!isset($validEntries[$index])) continue;

        $entry = $validEntries[$index];
        $errors = $this->validateRow($entry);

        // ❌ Any validation error cancels the entire batch
        if ($errors) {
            $rowNumber = $entry['row_number'] ?? $index;
            $message = "❌ Validation failed for row <strong>{$rowNumber}</strong>. No entries were saved.";
            return $request->ajax()
                ? response()->json(['error_modal' => $message])
                : back()->with('error_modal', $message);
        }

        $entriesToSave[] = $entry;
    }

    try {
        $savedCount = 0;

        // ✅ Wrap everything in a transaction
        DB::transaction(function () use ($entriesToSave, $adminId, &$savedCount) {
            $importLog = ImportLog::create([
                'file_name'       => session('uploaded_file_name') ?? 'CSV Upload',
                'admin_id'        => $adminId,
                'total_records'   => count($entriesToSave),
                'valid_count'     => count($entriesToSave),
                'invalid_count'   => 0,
                'duplicate_count' => 0,
                'status'          => 'Completed',
                'remarks'         => "Successfully imported " . count($entriesToSave) . 
                                    " row(s) from file '" . (session('uploaded_file_name') ?? 'CSV Upload') . 
                                    "' by Admin ID: {$adminId}.",
            ]);


            foreach ($entriesToSave as $entry) {
                $idNumber = $entry['id_number'] ?? null;

                // ❌ Fail entire batch on duplicates
                if ($idNumber && \App\Models\VolunteerProfile::where('id_number', $idNumber)->exists()) {
                    throw new \Exception("Duplicate ID detected: {$idNumber}. No entries were saved.");
                }

                \App\Models\VolunteerProfile::create([
                    'import_id'         => $importLog->import_id,
                    'full_name'         => $entry['full_name'] ?? null,
                    'id_number'         => $idNumber ?? 'TEMP-' . uniqid(),
                    'course_id'         => $entry['course_id'] ?? null,
                    'year_level'        => $entry['year_level'] ?? null,
                    'contact_number'    => $entry['contact_number'] ?? null,
                    'emergency_contact' => $entry['emergency_contact'] ?? null,
                    'email'             => $entry['email'] ?? null,
                    'fb_messenger'      => $entry['fb_messenger'] ?? null,
                    'location_id'       => $entry['location_id'] ?? null,
                    'status'            => 'active',
                ]);

                $savedCount++;

                $this->logFact(
                    'Import Verified',
                    $adminId,
                    'VolunteerProfile',
                    $idNumber,
                    'Imported',
                    [
                        'full_name' => $entry['full_name'] ?? null,
                        'email'     => $entry['email'] ?? null,
                        'id_number' => $idNumber,
                    ],
                    $importLog->import_id
                );
            }
        });

        // ✅ Clear sessions after success
        session()->forget([
            'validEntries',
            'invalidEntries',
            'uploaded_file_name',
            'uploaded_file_path',
            'csv_imported',
            'import_log_id'
        ]);

        $message = "✅ Successfully imported <strong>{$savedCount}</strong> entries from file '<strong>{$fileName}</strong>' on {$formattedTime}.";

        return back()->with('success', $message);

    } catch (\Exception $e) {
        Log::error('Import failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

        $message = "❌ Import failed: {$e->getMessage()}";
        return $request->ajax()
            ? response()->json(['error_modal' => $message])
            : back()->with('error_modal', $message);
    }
}


    /**
     * Reset Import Preview / Remove CSV Import
     */
    public function resetImports(Request $request)
    {
        $validCount = session()->has('validEntries') ? count(session('validEntries')) : 0;
        $invalidCount = session()->has('invalidEntries') ? count(session('invalidEntries')) : 0;
        $totalCleared = $validCount + $invalidCount;
        $fileName = session('uploaded_file_name', 'N/A');
        $originalImportId = session('import_log_id');
        $currentAdminId = auth()->guard('admin')->id() ?? null;
        $formattedTime = now()->format('M d, Y h:i A'); // concise format

        // Cancel original import log
        if ($originalImportId) {
            $originalLog = ImportLog::find($originalImportId);
            if ($originalLog) {
                $originalLog->update([
                    'admin_id'      => $originalLog->admin_id ?? $currentAdminId,
                    'total_records' => $originalLog->total_records ?: $totalCleared,
                    'valid_count'   => $originalLog->valid_count ?: $validCount,
                    'invalid_count' => $originalLog->invalid_count ?: $invalidCount,
                    'status'        => 'Cancelled',
                    'remarks'       => "Reset import preview cleared {$totalCleared} row(s) on {$formattedTime} by Admin ID: {$currentAdminId}",
                ]);

                $this->logFact(
                    'Import Cancelled',
                    $originalLog->admin_id,
                    'ImportLog',
                    $originalLog->import_id,
                    'Cancelled',
                    "Original import reset. {$totalCleared} row(s) cleared."
                );
            }
        }

        // Create new reset import log
        $resetLog = ImportLog::create([
            'file_name'       => $fileName,
            'admin_id'        => $currentAdminId,
            'total_records'   => $totalCleared,
            'valid_count'     => $validCount,
            'invalid_count'   => $invalidCount,
            'duplicate_count' => 0,
            'status'          => 'Reset',
            'remarks'         => "Reset import preview cleared {$totalCleared} row(s) on {$formattedTime} by Admin ID: {$currentAdminId}",
        ]);

        $this->logFact(
            'Reset Import Preview',
            $currentAdminId,
            'ImportLog',
            $resetLog->import_id,
            'Success',
            "Import preview for file '{$resetLog->file_name}' was reset successfully. Total rows cleared: {$totalCleared}"
        );

        // Clear relevant sessions
        session()->forget([
            'validEntries', 
            'invalidEntries', 
            'uploaded_file_name',
            'uploaded_file_path', 
            'csv_imported', 
            'import_log_id', 
            'lastUsedTable'
        ]);
        session()->flash('clearLastUsedTable', true);

        // Flash human-readable message for UI
        $message = "♻️ Import preview reset successfully by Admin ID: {$currentAdminId} on {$formattedTime}.<br>" .
                "Cleared rows: <strong>{$totalCleared}</strong> (Valid: <strong>{$validCount}</strong>, Invalid: <strong>{$invalidCount}</strong>).<br>" .
                "Reset log created: <span style='color:#B2000C;'>ID: {$resetLog->import_id}</span>.";

        return back()->with('success', $message);
    }


    /**
     * Centralized FactLog helper with auto entity type inference
    */
    private function logFact(
        string $factType,
        $adminId = null,
        $entity = null,
        ?int $entityId = null,
        ?string $action = null,
        $details = null,
        ?int $importId = null
        ): FactLog {
        // Resolve current admin safely
        $admin = Auth::guard('admin')->user();
        $adminId = is_numeric($adminId) ? (int) $adminId : ($admin->admin_id ?? null);

        // Encode details safely
        $encodedDetails = is_array($details) || is_object($details)
            ? json_encode($details, JSON_UNESCAPED_UNICODE)
            : (string) $details;

        // Infer entity_type
        if (is_object($entity)) {
            // If a model instance is passed, use class basename
            $entityType = class_basename($entity);
            $entityId = $entityId ?? ($entity->id ?? null);
        } elseif (is_string($entity)) {
            $entityType = $entity;
        } else {
            $entityType = 'Unknown';
        }

        return FactLog::create([
            'fact_type'   => $factType,
            'admin_id'    => $adminId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action'      => $action,
            'details'     => $encodedDetails,
            'import_id'   => $importId,
        ]);
    }
}
