<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Volunteer;
use App\Models\VolunteerProfile;
use App\Models\ImportLog;
use App\Models\FactType;
use App\Models\FactLog;

class VolunteerImportController extends Controller
{
    /**
     * STEP 0: Show the import page
     */
    public function index()
    {
        $validEntries = session('validEntries', []);
        $invalidEntries = session('invalidEntries', []);
        $uploadedFileName = session('uploaded_file_name', null);
        $uploadedFilePath = session('uploaded_file_path', null);
        $importLogs = ImportLog::orderBy('created_at', 'desc')->get();

        return view('volunteer_import.volunteer_import', compact(
            'validEntries', 'invalidEntries', 'uploadedFileName', 'uploadedFilePath', 'importLogs'
        ));
    }

  public function preview(Request $request)
{
    $request->validate([
        'csv_file' => 'required|mimes:csv,txt|max:2048',
    ]);

    // Clear previous session data
    session()->forget(['validEntries', 'invalidEntries', 'import_log_id']);

    $file = $request->file('csv_file');
    $filename = $file->getClientOriginalName();
    $path = $file->store('uploads', 'public');

    session([
        'uploaded_file_name' => $filename,
        'uploaded_file_path' => $path,
        'csv_imported' => true,
    ]);

    $admin = Auth::guard('admin')->user();

    // Create ImportLog immediately
    $importLog = ImportLog::create([
        'file_name' => $filename,
        'admin_id' => $admin->admin_id ?? null,
        'total_records' => 0,
        'valid_count' => 0,
        'invalid_count' => 0,
        'duplicate_count' => 0,
        'status' => 'Pending',
    ]);

    session(['import_log_id' => $importLog->import_id]);

    // --- Log the fact ---
    $this->logFact(
        'Import Preview',
        $admin->admin_id ?? null,
        'import_logs',
        $importLog->import_id,
        'Created',
        "CSV file uploaded: $filename"
    );

    $rows = array_map('str_getcsv', file($file->getRealPath()));
    if (empty($rows)) {
        $importLog->update(['status' => 'Failed']);
        return back()->with('error', 'CSV file is empty.');
    }

    $header = array_map('trim', array_shift($rows));
    $header = array_map('strtolower', $header);

    $valid = [];
    $invalid = [];

    foreach ($rows as $i => $row) {
        if (count($row) !== count($header)) {
            $invalid[] = [
                'row_number' => $i + 2,
                'data' => $row,
                'error_message' => 'Column count mismatch'
            ];
            continue;
        }

        $data = $this->normalizeRow($row, $header);
        $error = $this->validateRow($data);

        if ($error) {
            $data['row_number'] = $i + 2;
            $data['error_message'] = $error;
            $invalid[] = $data;
        } else {
            $data['row_number'] = $i + 2;
            $valid[] = $data;
        }
    }

    session([
        'validEntries' => $valid,
        'invalidEntries' => $invalid,
    ]);

    $importLog->update([
        'total_records' => count($rows),
        'valid_count' => count($valid),
        'invalid_count' => count($invalid),
        'status' => count($valid) > 0 ? 'Pending' : 'Failed',
    ]);

    $validCount = count($valid);
    $invalidCount = count($invalid);

    $message = "CSV parsed successfully: 
        <span style='color:#007bff;'><i class='fa-solid fa-circle-check'></i> {$validCount} valid row(s)</span>, 
        <span style='color:red;'><i class='fa-solid fa-circle-exclamation'></i> {$invalidCount} invalid row(s)</span>. 
        Please review invalid entries before validation.";

    return back()->with('success', $message);
}



    /**
     * Normalize CSV row keys to match expected database/Blade fields
     */
    private function normalizeRow(array $row, array $header): array
    {
        $mapping = [
            'name' => 'full_name',
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

            $value = trim($row[$index] ?? '');
            $value = in_array($value, ['-', 'N/A']) ? '' : $value;

            // Special normalization
            if ($key === 'id_number') $value = strtoupper($value);

            if (in_array($key, ['contact_number', 'emergency_contact'])) {
                $value = preg_replace('/[^\d+]/', '', $value);
            }

            $normalized[$key] = $value;
        }

        // Ensure all expected keys exist
        $defaults = [
            'full_name', 'id_number', 'email', 'contact_number', 'emergency_contact',
            'fb_messenger', 'barangay', 'district', 'course', 'year_level'
        ];

        foreach ($defaults as $key) {
            if (!isset($normalized[$key])) $normalized[$key] = '';
        }

        return $normalized;
    }

    /**
     * Validate each row
     */
    private function validateRow(array $data): ?string
    {
        if (empty($data['full_name'])) return 'Missing full name.';
        if (empty($data['id_number'])) return 'Missing School ID.';
        if (empty($data['contact_number'])) return 'Missing contact number.';
        if (empty($data['emergency_contact'])) return 'Missing emergency contact.';
        if (empty($data['email'])) return 'Missing email.';
        if (empty($data['fb_messenger'])) return 'Missing FB/Messenger.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return 'Invalid email format.';

        return null;
    }

    /**
     * Update a single volunteer entry
     */
    public function updateVolunteerEntry(Request $request, $index, $type)
    {
        $entries = session($type . 'Entries', []);

        if (!isset($entries[$index])) {
            return back()->with('error', '⚠️ Entry not found.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string',
            'id_number' => 'required|string',
            'course' => 'required|string',
            'year_level' => 'required|string',
            'contact_number' => 'required|string',
            'emergency_contact' => 'required|string',
            'email' => 'required|email',
            'fb_messenger' => 'nullable|string',
            'barangay' => 'nullable|string',
            'district' => 'nullable|string',
        ]);

        // Update session entries
        $entries[$index] = array_merge($entries[$index], $validated);
        session([$type . 'Entries' => $entries]);

        // Update database if volunteer exists
        $volunteer = Volunteer::where('volunteer_code', $validated['id_number'])->first();
        if ($volunteer) {
            $volunteer->update([
                'full_name' => $validated['full_name'],
                'volunteer_code' => $validated['id_number'],
                'contact_number' => $validated['contact_number'],
                'status' => 'Active',
            ]);
        }

        // Row number is 1-based for display
        $rowNumber = $index + 1;

        // Prepare a styled, iconified success message including row number
        $message = "✅ Row #{$rowNumber} updated successfully: <span style='color:#007bff;'>{$validated['full_name']}</span>";

        // Redirect back to import page with persistent last-updated info
        return redirect()->route('volunteer.import.index')
            ->with('success', $message)
            ->with('last_updated_table', $type)
            ->with('last_updated_index', $index);
    }


    /**
     * Move selected invalid entries to valid manually
     */
    public function moveInvalidToValid(Request $request)
    {
        $invalid = session('invalidEntries', []);
        $valid = session('validEntries', []);

        $movedCount = 0;

        if ($request->has('selected_invalid')) {
            foreach ($request->input('selected_invalid') as $index) {
                if (isset($invalid[$index])) {
                    $entry = $invalid[$index];
                    unset($invalid[$index]);
                    unset($entry['error_message']);
                    $valid[] = $entry;
                    $movedCount++;
                }
            }

            session([
                'validEntries' => array_values($valid),
                'invalidEntries' => array_values($invalid),
            ]);
        }

        $message = $movedCount
            ? "✅ Moved <span style='color:#007bff;'>{$movedCount}</span> invalid entr" . ($movedCount > 1 ? "ies" : "y") . " to verified."
            : "ℹ️ No invalid entries selected to move.";

        return back()->with('success', $message);
    }

    /**
     * Move a single valid entry back to invalid manually
     */
    public function moveValidToInvalid(Request $request, $index)
    {
        $valid = session('validEntries', []);
        $invalid = session('invalidEntries', []);

        if (isset($valid[$index])) {
            $entry = $valid[$index];
            unset($valid[$index]);
            $invalid[] = $entry;

            session([
                'validEntries' => array_values($valid),
                'invalidEntries' => array_values($invalid),
            ]);

            session([
                'last_updated_table' => 'invalid',
                'last_updated_index' => count($invalid) - 1,
            ]);

            $message = "⚠️ Moved 1 verified entry back to <span style='color:red;'>invalid</span>.";
        } else {
            $message = "ℹ️ No valid entry selected to move back.";
        }

        return back()->with('success', $message);
    }


    /**
     * Generic deletion function for any table type
     * Table type determines which session key or DB table to operate on
     */
    public function deleteEntries(Request $request)
{
    $tableType = $request->input('table_type'); // invalid / valid / logs
    $selected = $request->input('selected', []);

    if (empty($selected)) {
        return back()->with('error', 'No entries selected for deletion.');
    }

    switch ($tableType) {
        case 'invalid':
            $entries = session('invalidEntries', []);
            foreach ($selected as $index) {
                if (isset($entries[$index])) unset($entries[$index]);
            }
            session(['invalidEntries' => array_values($entries)]);
            return back()->with('success', 'Selected invalid entries deleted successfully.');

        case 'valid':
            $entries = session('validEntries', []);
            foreach ($selected as $index) {
                if (isset($entries[$index])) unset($entries[$index]);
            }
            session(['validEntries' => array_values($entries)]);
            return back()->with('success', 'Selected valid entries deleted successfully.');

        case 'logs':
            \App\Models\ImportLog::whereIn('import_id', $selected)->delete();
            return back()->with('success', 'Selected import logs deleted successfully.');

        default:
            return back()->with('error', 'Invalid table type.');
    }
}

public function validateAndSave(Request $request)
{
    $selectedIndexes = $request->input('selected_valid', []);
    $validEntries = session('validEntries', []);
    $invalidEntries = session('invalidEntries', []);

    $admin = Auth::guard('admin')->user();
    if (!$admin) {
        return back()->with('error_modal', 'Admin not authenticated.');
    }
    $adminId = $admin->admin_id;

    // --- CASE 1: No valid entries at all ---
    if (empty($validEntries)) {
        // Log failed import
        $failedFactType = FactType::firstOrCreate(
            ['type_name' => 'Failed Import'],
            ['description' => 'Log for failed volunteer imports']
        );

        FactLog::create([
            'fact_type_id' => $failedFactType->fact_type_id,
            'import_id' => null,
            'admin_id' => $adminId,
            'entity_type' => 'Volunteer Import',
            'entity_id' => null,
            'action' => 'Failed',
            'details' => json_encode([
                'reason' => 'No valid entries to import',
            ]),
            'logged_at' => now(),
        ]);

        return back()->with('error_modal', 'No verified entries available.');
    }

    // --- CASE 2: There are invalid entries ---
    if (!empty($invalidEntries)) {
        $invalidRows = implode(', ', array_keys($invalidEntries));

        // Log failed import
        $failedFactType = FactType::firstOrCreate(
            ['type_name' => 'Failed Import'],
            ['description' => 'Log for failed volunteer imports']
        );

        FactLog::create([
            'fact_type_id' => $failedFactType->fact_type_id,
            'import_id' => null,
            'admin_id' => $adminId,
            'entity_type' => 'Volunteer Import',
            'entity_id' => null,
            'action' => 'Failed',
            'details' => json_encode([
                'invalid_rows' => array_keys($invalidEntries),
                'invalid_count' => count($invalidEntries),
                'reason' => 'There are invalid entries preventing import'
            ]),
            'logged_at' => now(),
        ]);

        return back()->with('error_modal', "Cannot upload. Invalid entries found in row(s): $invalidRows. Please fix them first.");
    }

    // --- CASE 3: No selected indexes ---
    if (empty($selectedIndexes)) {
        return back()->with('error_modal', 'No verified entries selected to save.');
    }

    // Filter only selected entries
    $entriesToSave = [];
    foreach ($selectedIndexes as $index) {
        if (isset($validEntries[$index])) $entriesToSave[] = $validEntries[$index];
    }

    if (empty($entriesToSave)) {
        return back()->with('error_modal', 'Selected entries not found.');
    }

    try {
        DB::transaction(function () use ($entriesToSave, $adminId) {

            // Create Import Log
            $importLog = ImportLog::create([
                'file_name' => session('uploaded_file_name') ?? 'CSV Upload',
                'admin_id' => $adminId,
                'total_records' => count($entriesToSave),
                'valid_count' => count($entriesToSave),
                'invalid_count' => 0,
                'duplicate_count' => 0,
                'status' => 'Completed',
            ]);

            $duplicates = 0;

            // Ensure FactType for successful import
            $factType = FactType::firstOrCreate(
                ['type_name' => 'Import Verified'],
                ['description' => 'Log for imported verified volunteers']
            );

            foreach ($entriesToSave as $entry) {

                // Create or find volunteer
                $volunteer = Volunteer::firstOrCreate(
                    ['volunteer_code' => $entry['id_number'] ?? 'TEMP-' . uniqid()],
                    [
                        'full_name' => $entry['full_name'] ?? null,
                        'email' => $entry['email'] ?? null,
                        'contact_number' => $entry['contact_number'] ?? null,
                        'status' => 'Active',
                    ]
                );

                if (!$volunteer->wasRecentlyCreated) $duplicates++;

                // Update or create volunteer profile
                VolunteerProfile::updateOrCreate(
                    ['volunteer_id' => $volunteer->id],
                    [
                        'import_id' => $importLog->import_id,
                        'full_name' => $entry['full_name'] ?? null,
                        'id_number' => $entry['id_number'] ?? null,
                        'course' => $entry['course'] ?? null,
                        'year_level' => $entry['year_level'] ?? null,
                        'contact_number' => $entry['contact_number'] ?? null,
                        'emergency_contact' => $entry['emergency_contact'] ?? null,
                        'email' => $entry['email'] ?? null,
                        'fb_messenger' => $entry['fb_messenger'] ?? null,
                        'barangay' => $entry['barangay'] ?? null,
                        'district' => $entry['district'] ?? null,
                        'status' => 'Active',
                    ]
                );

                // Log each imported volunteer
                FactLog::create([
                    'fact_type_id' => $factType->fact_type_id,
                    'import_id' => $importLog->import_id,
                    'admin_id' => $adminId,
                    'entity_type' => 'Volunteer',
                    'entity_id' => $volunteer->id,
                    'action' => 'Imported',
                    'details' => json_encode([
                        'full_name' => $volunteer->full_name,
                        'email' => $volunteer->email,
                        'id_number' => $volunteer->volunteer_code
                    ]),
                    'logged_at' => now(),
                ]);
            }

            // Update duplicate count in import log
            $importLog->update(['duplicate_count' => $duplicates]);
        });

        // Clear sessions
        session()->forget(['validEntries', 'invalidEntries', 'uploaded_file_name', 'uploaded_file_path', 'csv_imported']);

        return back()->with('success_modal', 'Selected verified entries saved successfully.');

    } catch (\Exception $e) {
        Log::error('Import failed: ' . $e->getMessage());
        return back()->with('error_modal', 'Failed to save entries. Check logs.');
    }
}


    /**
     * Clear invalid entries from session only
     */
    public function clearInvalid(Request $request)
    {
        session()->forget('invalidEntries');
        return back()->with('success', 'Invalid entries cleared from preview.');
    }
public function resetImports(Request $request)
{
    $validCount = session()->has('validEntries') ? count(session('validEntries')) : 0;
    $invalidCount = session()->has('invalidEntries') ? count(session('invalidEntries')) : 0;
    $totalCleared = $validCount + $invalidCount;

    $fileName = session('uploaded_file_name', 'N/A');
    $originalImportId = session('import_log_id');

    $currentAdminId = auth()->guard('admin')->id() ?? null;
    $originalLog = null;

    // --- 1. Update original import log ---
    if ($originalImportId) {
        $originalLog = \App\Models\ImportLog::find($originalImportId);
        if ($originalLog) {
            $originalLog->update([
                'admin_id'     => $originalLog->admin_id ?? $currentAdminId, // keep original admin if exists
                'total_records'=> $originalLog->total_records ?: $totalCleared,
                'valid_count'  => $originalLog->valid_count ?: $validCount,
                'invalid_count'=> $originalLog->invalid_count ?: $invalidCount,
                'status'       => 'Cancelled',
                'remarks'      => "This import was reset on "
                                  . now()->format('M d, Y h:i A')
                                  . " by Admin ID: {$currentAdminId} (Reset Log ID: Pending/Will be created)",
            ]);

            // Log fact for original import
            $this->logFact(
                'Import Cancelled',
                $originalLog->admin_id,
                'import_logs',
                $originalLog->import_id,
                'Cancelled',
                "Original import was reset by Admin ID: {$currentAdminId} (Pending Reset Log ID)"
            );
        }
    }

    // --- 2. Create new reset log ---
    $resetLog = \App\Models\ImportLog::create([
        'file_name'       => $fileName,
        'admin_id'        => $currentAdminId, // admin who performed the reset
        'total_records'   => $totalCleared,
        'valid_count'     => $validCount,
        'invalid_count'   => $invalidCount,
        'duplicate_count' => 0,
        'status'          => 'Reset',
        'remarks'         => "Reset import preview, cleared $totalCleared row(s) on "
                             . now()->format('M d, Y h:i A')
                             . " by Admin ID: {$currentAdminId}",
    ]);

    // --- 3. Update original import remarks with actual Reset Log ID ---
    if (!empty($originalLog)) {
        $originalLog->update([
            'remarks' => "This import was reset on "
                         . now()->format('M d, Y h:i A')
                         . " by Admin ID: {$currentAdminId} (Reset Log ID: {$resetLog->import_id})",
        ]);

        // Log fact for original import with actual reset log ID
        $this->logFact(
            'Import Cancelled',
            $originalLog->admin_id,
            'import_logs',
            $originalLog->import_id,
            'Cancelled',
            "Original import was reset by Admin ID: {$currentAdminId}. Reset Log ID: {$resetLog->import_id}"
        );
    }

    // --- 4. Log fact for reset log ---
    $this->logFact(
        'Import Reset',
        $currentAdminId,
        'import_logs',
        $resetLog->import_id,
        'Reset',
        "Reset import preview cleared $totalCleared rows by Admin ID: {$currentAdminId}"
    );

    // --- 5. Clear session ---
    session()->forget([
        'validEntries',
        'invalidEntries',
        'uploaded_file_name',
        'uploaded_file_path',
        'csv_imported',
        'import_log_id',
    ]);

    session()->forget('lastUsedTable');
    session()->flash('clearLastUsedTable', true);

    $message = "♻️ Cleared all imported data. Original import updated and reset log created "
               . "(<span style='color:#B2000C;'>ID: {$resetLog->import_id}</span>).";

    return back()->with('success', $message);
}




private function logFact($factTypeName, $adminId, $entityType, $entityId, $action, $details = null)
{
    // Ensure the fact type exists
    $factType = \App\Models\FactType::firstOrCreate(
        ['type_name' => $factTypeName],
        ['description' => $factTypeName]
    );

    // Create the fact log entry
    \App\Models\FactLog::create([
        'fact_type_id' => $factType->fact_type_id,
        'admin_id' => $adminId,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'action' => $action,
        'details' => $details,
        'timestamp' => now(),
    ]);
}


}
