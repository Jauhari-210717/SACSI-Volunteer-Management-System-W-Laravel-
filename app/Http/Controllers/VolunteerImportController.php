<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\VolunteerProfile;
use App\Models\ImportLog;
use App\Models\FactLog;
use App\Models\Course;
use App\Models\Location;

class VolunteerImportController extends Controller
{
    public function index()
    {
        $validEntries = session('validEntries', []);
        $invalidEntries = session('invalidEntries', []);
        $uploadedFileName = session('uploaded_file_name', null);
        $uploadedFilePath = session('uploaded_file_path', null);

        // Ensure class_schedule exists in all entries
        foreach ($validEntries as &$entry) {
            if (!isset($entry['class_schedule'])) {
                $entry['class_schedule'] = 'No class schedule';
            }
        }
        foreach ($invalidEntries as &$entry) {
            if (!isset($entry['class_schedule'])) {
                $entry['class_schedule'] = 'No class schedule';
            }
        }

        // 🔥 ADD THESE LINES — dynamic dropdown data
        $courses = Course::orderBy('course_name')->get();

        $barangays = Location::orderBy('barangay')->get();

        $districts = Location::select('district_id')
            ->distinct()
            ->orderBy('district_id')
            ->get();

        // Import logs
        $importLogs = ImportLog::orderBy('created_at', 'desc')->get();

        // 🔥 RETURN EVERYTHING TO THE VIEW
        return view('volunteer_import.volunteer_import', compact(
            'validEntries',
            'invalidEntries',
            'uploadedFileName',
            'uploadedFilePath',
            'importLogs',
            'courses',
            'barangays',
            'districts'
        ));
    }

    /**
     * Convert Google Drive (Google Forms) link → direct download link
     */
    private function convertDriveLinkToDownloadUrl($url)
    {
        if (!$url) return '';

        // Pattern: ?id=FILEID
        if (preg_match('/id=([^&]+)/', $url, $m)) {
            return "https://drive.google.com/uc?export=download&id={$m[1]}";
        }

        // Pattern: /d/FILEID/
        if (preg_match('#/d/([^/]+)/#', $url, $m)) {
            return "https://drive.google.com/uc?export=download&id={$m[1]}";
        }

        return $url; // fallback
    }

    /**
     * Extract Google Drive (Google Forms) link and transfer to storage/profile_picture
     */
    private function downloadDriveImage($url)
    {
        try {
            if (!$url) return null;

            // Ensure volunteer folder exists
            Storage::disk('public')->makeDirectory('profile_pictures/volunteers');

            $contents = file_get_contents($url);
            if (!$contents) return null;

            $fileName = 'pp_' . uniqid() . '.jpg';

            Storage::disk('public')->put(
                'profile_pictures/volunteers/' . $fileName,
                $contents
            );

            // Save path relative to /storage
            return 'profile_pictures/volunteers/' . $fileName;

        } catch (\Exception $e) {
            Log::error('Image download failed: '.$e->getMessage());
            return null;
        }
    }

    /**
     * Preview
     */
    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);
        $file = $request->file('csv_file');
        $filename = $file->getClientOriginalName();
        $path = $file->store('uploads', 'public');
        session([
            'uploaded_file_name' => $filename,
            'uploaded_file_path' => $path,
            'csv_imported'       => true,
        ]);
        $admin = Auth::guard('admin')->user();
        $adminName = $admin->name ?? $admin->username ?? "Unknown Admin";
        /* ============================================================
        ⭐ FIX: Properly mark ANY previously active preview as Abandoned
        ============================================================ */
        $previousId = session('import_log_id');
        if ($previousId) {
            $previousLog = ImportLog::find($previousId);
            if ($previousLog && $previousLog->status === 'Pending') {
                $previousLog->update([
                    'status'  => 'Abandoned',
                    'remarks' => "Admin {$adminName} abandoned preview on " . now()->format('M d, Y h:i A'),
                ]);
            }
        }
        // Create new Pending preview log
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
        session(['import_log_id' => $importLog->import_id]);
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        if (empty($rows)) {
            $importLog->update([
                'remarks' => "Preview completed for Import #{$importLog->import_id}: No rows were found in the uploaded file.",
                'total_records' => 0
            ]);
            return back()->with('error', 'CSV file is empty.');
        }
        $header = array_map('strtolower', array_map('trim', array_shift($rows)));
        $valid = [];
        $invalid =[];
        $duplicates = [];
        $seenKeys = [];
        foreach ($rows as $i => $row) {
            $data = $this->normalizeRow($row, $header);
            $errors = $this->validateRow($data);
            // Add profile picture (converted from URL)
            if (!empty($data['profile_picture'])) {
                $data['profile_picture_local'] = $this->downloadDriveImage($data['profile_picture']);
            }
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
        /* ============================================================
        ⭐ NORMALIZATION FIX — PREVENTS "Undefined array key"
        This ensures your Blade NEVER crashes.
        ============================================================ */
        $normalizePictureFields = function (&$arr) {
            foreach ($arr as &$e) {
                if (!array_key_exists('profile_picture_local', $e)) {
                    $e['profile_picture_local'] = '';
                }
                if (!array_key_exists('profile_picture', $e)) {
                    $e['profile_picture'] = '';
                }
            }
            unset($e);
        };
        $normalizePictureFields($valid);
        $normalizePictureFields($invalid);
        $normalizePictureFields($duplicates);
        /* ============================================================ */
        session([
            'validEntries'     => $valid,
            'invalidEntries'   => $invalid,
            'duplicateEntries' => $duplicates,
        ]);
        $importLog->update([
            'total_records'   => count($rows),
            'valid_count'     => count($valid),
            'invalid_count'   => count($invalid),
            'duplicate_count' => count($duplicates),
            'status'          => 'Pending',
            'remarks'         => "Preview summary for Import #{$importLog->import_id}: "
                                . count($valid) . " valid, "
                                . count($invalid) . " invalid, "
                                . count($duplicates) . " duplicates.",
        ]);
        if ($admin) {
            $this->logFact(
                'Preview Import',
                $admin->admin_id,
                'Volunteer Import',
                $importLog->import_id,
                'Previewed',
                "Previewed CSV (Import #{$importLog->import_id}): "
                    . count($valid) . " valid, "
                    . count($invalid) . " invalid, "
                    . count($duplicates) . " duplicates."
            );
        }
        /* ============================================================
        BUILD TOP SINGLE-LINE STATUS + DETAILS MODAL LINK
        ============================================================ */

        // Build the HTML that will appear INSIDE the modal
        $details = "
            <div style='font-size:1rem; line-height:1.55; color:#333;'>

                <strong>Valid:</strong> " . count($valid) . "<br>
                <strong>Invalid:</strong> " . count($invalid) . "<br>
                <strong>Duplicates:</strong> " . count($duplicates) . "<br><br>
        ";

        // Conditional summary INSIDE the modal
        if (count($invalid) === 0 && count($valid) > 0) {

            $redirectAnchor = '#import-Section-valid';

            $details .= "
                <strong style='color:#28a745;'>All entries are valid.</strong><br>
                Click below to jump to the valid table:<br>
                <a href='#import-Section-valid'>View valid entries →</a>
            ";

        } elseif (count($valid) === 0 && count($invalid) > 0) {

            $redirectAnchor = '#import-Section-invalid';

            $details .= "
                <strong style='color:#B2000C;'>All entries are invalid.</strong><br>
                Fix all invalid entries before submitting.<br>
                <a href='#import-Section-invalid'>View invalid entries →</a>
            ";

        } else {

            $redirectAnchor = '#import-Section-invalid';

            $details .= "
                <strong style='color:#d38b00;'>Some entries are invalid.</strong><br>
                Fix the invalid entries before submitting the valid ones.<br>
                <a href='#import-Section-invalid'>Go to invalid entries →</a>
            ";
        }

        $details .= "</div>";

        // ✅ Only escape double quotes for the data-attribute (keep <strong>, <br>, <a> intact)
        $encodedDetails = str_replace('"', '&quot;', $details);

        // Final top-line message
        $message = "
            <div style='display:flex; align-items:center; flex-wrap:wrap; gap:12px;
                        font-size:1.05rem; font-weight:600; margin-bottom:6px;'>

                <span style='color:#28a745;'>✅ " . count($valid) . " valid</span>
                <span style='color:#B2000C;'>❌ " . count($invalid) . " invalid</span>
                <span style='color:#d38b00;'>⚠️ " . count($duplicates) . " duplicates</span>

                <span style='color:#999;'>|</span>

                <a href='#'
                class='preview-details-link'
                data-details=\"{$encodedDetails}\"
                style='color:#007bff; text-decoration:none; font-size:0.95rem;'>
                    + Show details
                </a>

            </div>
        ";


        return redirect(url()->previous() . $redirectAnchor)
            ->with('success', $message);
    }

    /**
     * normalizeRow
     */
    private function normalizeRow(array $row, array $header): array
    {
        /**
         * Flexible header mapping (case-insensitive)
         */
        $mapping = [
            'full_name' => 'full_name','fullname'=>'full_name','full name'=>'full_name',
            'first name'=>'first_name','firstname'=>'first_name',
            'middle name'=>'middle_name','middlename'=>'middle_name',
            'last name'=>'last_name','lastname'=>'last_name','surname'=>'last_name','family name'=>'last_name',

            'id number'=>'id_number','school id'=>'id_number','school id number'=>'id_number','id'=>'id_number',

            'contact number'=>'contact_number','contact_number'=>'contact_number',
            'phone'=>'contact_number','phone number'=>'contact_number',

            'emergency number'=>'emergency_contact','emergency_contact'=>'emergency_contact',
            'emergency contact'=>'emergency_contact',

            'email address' => 'email',
            'email' => 'email',
            'school email address' => 'email',
            'school email' => 'email',
            'school email id' => 'email',
            'adzu email' => 'email',

            'fb link'=>'fb_messenger','facebook profile link'=>'fb_messenger',
            'messenger'=>'fb_messenger','fb'=>'fb_messenger',

            'barangay'=>'barangay','brgy'=>'barangay','district'=>'district',

            'course'=>'course','strand'=>'course','program'=>'course',

            'year'=>'year_level','year level'=>'year_level','yearlevel'=>'year_level',

            'monday schedule'=>'monday','monday'=>'monday',
            'tuesday schedule'=>'tuesday','tuesday'=>'tuesday',
            'wednesday schedule'=>'wednesday','wednesday'=>'wednesday',
            'thursday schedule'=>'thursday','thursday'=>'thursday',
            'friday schedule'=>'friday','friday'=>'friday',
            'saturday schedule'=>'saturday','saturday'=>'saturday',

            'class schedule'=>'class_schedule','class_schedule'=>'class_schedule',

            'certificates'=>'certificates','upload certificates'=>'certificates',

            // PROFILE PICTURE
            'profile picture'=>'profile_picture','profile_photo'=>'profile_picture',
        ];

        $normalized = [];

        // ---------- fix “9-11” → “09:00-11:00” ----------
        $fixTime = function ($timeStr) {
            $s = trim($timeStr);
            if (!$s) return "";

            $expand = function ($x) {
                $x = trim($x);
                return preg_match('/^\d{1,2}$/', $x) ? $x . ":00" : $x;
            };

            if (!str_contains($s, '-')) return $expand($s);

            [$a,$b] = explode('-', $s);
            return $expand($a) . "-" . $expand($b);
        };

        $sortRanges = function (&$arr) {
            usort($arr, function ($a, $b) {
                [$ah,$am] = explode(':', explode('-', $a)[0]);
                [$bh,$bm] = explode(':', explode('-', $b)[0]);
                return ($ah*60 + $am) <=> ($bh*60 + $bm);
            });
        };

        /**
         * Parse CSV
         */
        foreach ($header as $i => $col) {

            $keyRaw = strtolower(trim($col));

            if (in_array($keyRaw, ['timestamp','time submitted','date'])) {
                continue;
            }

            $key = $mapping[$keyRaw] ?? null;

            if (!$key) {
                $cleanSpaces = preg_replace('/\s+/', ' ', $keyRaw);
                $key = $mapping[$cleanSpaces] ?? null;
            }

            if (!$key) {
                $keyUnder = str_replace([' ', '-'], '_', $keyRaw);
                $key = $mapping[$keyUnder] ?? $keyUnder;
            }

            $value = trim((string)($row[$i] ?? ''));

            $value = preg_replace('/#.*/', '', $value);
            $value = trim($value);

            if (in_array($value, ['-', 'N/A'])) $value = '';

            if ($key === 'id_number') $value = strtoupper($value);

            if (in_array($key, ['contact_number','emergency_contact']))
                $value = preg_replace('/[^\d+]/', '', $value);

            if ($key === 'district')
                $value = preg_replace('/\D/', '', $value);

            /** ⭐ NEW: Google Drive → Direct Download conversion */
            if ($key === 'profile_picture') {
                $value = $this->convertDriveLinkToDownloadUrl($value);
            }

            $days = ['monday','tuesday','wednesday','thursday','friday','saturday'];

            if (in_array($key, $days)) {
                if ($value === '' || str_contains(strtolower($value), 'no class')) {
                    $normalized[$key] = [];
                    continue;
                }

                $value = str_replace(';',' ', $value);
                $value = preg_replace('/\s+/', ' ', $value);
                $parts = array_filter(explode(' ', $value));
                $parts = array_unique(array_map($fixTime, $parts));

                $sortRanges($parts);
                $normalized[$key] = $parts;
                continue;
            }

            if ($key === 'class_schedule') continue;

            $normalized[$key] = $value;
        }

        // Merge names → full_name
        $fn = $normalized['first_name'] ?? '';
        $mn = $normalized['middle_name'] ?? '';
        $ln = $normalized['last_name'] ?? '';
        $existing = $normalized['full_name'] ?? '';

        if (!$existing && ($fn || $ln)) {
            $normalized['full_name'] =
                trim(
                    ucwords(strtolower($fn)) . ' ' .
                    ($mn ? strtoupper($mn[0]) . '. ' : '') .
                    ucwords(strtolower($ln))
                );
        }

        if (!empty($normalized['full_name']))
            $normalized['full_name'] = ucwords(strtolower($normalized['full_name']));

        if (!empty($normalized['barangay']))
            $normalized['barangay'] = ucwords(strtolower($normalized['barangay']));

        if (!empty($normalized['barangay'])) {
            $districtId = DB::table('locations')
                ->whereRaw('LOWER(barangay)=?', [strtolower($normalized['barangay'])])
                ->value('district_id');

            if ($districtId)
                $normalized['district'] = $districtId;
        }

        if (!empty($normalized['course'])) {
            $c = trim($normalized['course']);

            $match = DB::table('courses')
                ->whereRaw('LOWER(course_name)=?', [strtolower($c)])
                ->value('course_name');

            $normalized['course'] = $match ? $match : ucwords(strtolower($c));
        }

        if (!empty($normalized['year_level'])) {
            $yl = strtolower($normalized['year_level']);
            if (str_contains($yl,'1')) $normalized['year_level'] = "1";
            elseif (str_contains($yl,'2')) $normalized['year_level'] = "2";
            elseif (str_contains($yl,'3')) $normalized['year_level'] = "3";
            elseif (str_contains($yl,'4')) $normalized['year_level'] = "4";
        }

        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $result = [];

        foreach ($days as $d) {
            $slots = $normalized[strtolower($d)] ?? [];
            $result[] = $d . ': ' . (empty($slots) ? 'No Class' : implode(' ', $slots));
        }

        $normalized['class_schedule'] = implode(' ', $result);

        foreach ([ 'full_name','id_number','email','contact_number','emergency_contact',
                'fb_messenger','barangay','district','course','year_level',
                'class_schedule','certificates','profile_picture'
        ] as $k)
            if (!isset($normalized[$k])) $normalized[$k] = '';

        return $normalized;
    }

    /**
     * Validate
     */
    private function validateRow(array $data)
    {
        $errors = [];

        if (empty($data['full_name']) ||
            !preg_match("/^[A-Za-zÑñ\s\.\'-]+$/u",$data['full_name'])) {
            $errors['full_name'] =
                'Full Name is required and can only contain letters, spaces, dots, hyphens, or apostrophes.';
        }

        if (empty($data['id_number']) ||
            !preg_match('/^\d{6,7}$/',$data['id_number'])) {
            $errors['id_number'] = 'School ID must be 6 or 7 digits.';
        }

        if (empty($data['course']) ||
            !preg_match('/^[A-Za-z\s]+$/u',$data['course'])) {
            $errors['course'] = 'Course is required and must be alphabetic.';
        }

        if (empty($data['year_level']) ||
            !in_array((string)$data['year_level'],['1','2','3','4'])) {
            $errors['year_level'] = 'Year must be 1, 2, 3, or 4.';
        }

        foreach (['contact_number','emergency_contact'] as $field) {
            if (empty($data[$field]) ||
                !preg_match('/^(09\d{9}|\+639\d{9})$/',$data[$field])) {
                $errors[$field] =
                    ucfirst(str_replace('_',' ',$field)) . ' must be a valid Philippine mobile number.';
            }
        }

        if (empty($data['email']) ||
            !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
            !preg_match('/@(gmail\.com|adzu\.edu\.ph)$/i',$data['email'])) {
            $errors['email'] =
                'Email must be valid and end with @gmail.com or @adzu.edu.ph.';
        }

        if (!empty($data['fb_messenger'])) {
            $fb = $data['fb_messenger'];
            $host = strtolower(parse_url($fb, PHP_URL_HOST) ?? '');
            if (!filter_var($fb, FILTER_VALIDATE_URL) ||
                strpos($host,'facebook.com')===false) {
                $errors['fb_messenger'] =
                    'FB/Messenger must be a valid Facebook link.';
            }
        }

        $district = trim(preg_replace('/\D/','',$data['district'] ?? ''));
        $barangay = trim($data['barangay'] ?? '');

        if (empty($district))
            $errors['district'] = 'No district selected.';

        if (empty($barangay))
            $errors['barangay'] = 'No barangay selected.';
        else {
            $exists = DB::table('locations')
                ->where('barangay',$barangay)
                ->where('district_id',$district)
                ->exists();

            if (!$exists)
                $errors['barangay'] =
                    "Barangay \"$barangay\" not found in District \"$district\".";
        }

        if (empty($data['class_schedule'])) {
            $errors['class_schedule'] = 'Class schedule is required.';
        }
        elseif (!preg_match('/^[A-Za-z0-9\s,:-]+$/',$data['class_schedule'])) {
            $errors['class_schedule'] =
                'Class schedule contains invalid characters.';
        }

        /**
         * ⭐ NEW: PROFILE PICTURE VALIDATION
         */
        if (!empty($data['profile_picture'])) {

            if (!filter_var($data['profile_picture'], FILTER_VALIDATE_URL)) {
                $errors['profile_picture'] = 'Invalid profile picture URL format.';
            }

            if (!preg_match('/drive\.google\.com/', $data['profile_picture'])) {
                $errors['profile_picture'] = 'Profile picture must be a Google Drive link.';
            }

            if (!preg_match('/export=download/', $data['profile_picture'])) {
                $errors['profile_picture'] =
                    'Profile picture link must be a valid downloadable Drive link.';
            }
        }

        return empty($errors) ? null : $errors;
    }


    /**
     * Update/Correct Volunteer Fields
     */
    public function updateVolunteerEntry(Request $request, $index, $type)
    {
        $entries = session($type . 'Entries', []);
        if (!isset($entries[$index])) {
            return back()->with('error', '⚠️ Entry not found.');
        }

        $entry = $entries[$index];
        $before = $entry;  // ⭐ Preserve original values BEFORE updates
        $input = array_map('trim', $request->all());

        // ⭐ PATCH — Normalize district JUST FOR COMPARISON
        if (isset($before['district'])) {
            $before['district'] = preg_replace('/\D/', '', $before['district']);
        }
        if (isset($input['district'])) {
            $input['district'] = preg_replace('/\D/', '', $input['district']);
        }

        // ⭐ PATCH — Normalize class_schedule JUST FOR COMPARISON
        if (isset($before['class_schedule'])) {
            $before['class_schedule'] = preg_replace('/\s+/', ' ', trim($before['class_schedule']));
        }
        if (isset($input['class_schedule'])) {
            $input['class_schedule'] = preg_replace('/\s+/', ' ', trim($input['class_schedule']));
        }

        // Normalize contact numbers and ID
        foreach (['contact_number', 'emergency_contact'] as $field) {
            if (!empty($input[$field])) {
                $input[$field] = preg_replace('/[^\d+]/', '', $input[$field]);
            }
        }
        if (!empty($input['id_number'])) {
            $input['id_number'] = strtoupper($input['id_number']);
        }

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
            'class_schedule' => ['required','string','regex:/^[\w\s,:()\.\-\/]+$/']
        ];

        $messages = [
            'year_level.in' => 'Year must be 1, 2, 3, or 4.',
            'district.required' => 'No district selected.',
            'barangay.required' => 'No barangay selected.',
            'class_schedule.required' => 'Class schedule is required.',
            'class_schedule.regex' => 'Class schedule contains invalid characters.',
        ];

        $validator = \Validator::make($input, $rules, $messages);
        $errors = $validator->fails() ? $validator->errors()->toArray() : [];

        // FB/Messenger validation
        if (!empty($input['fb_messenger'])) {
            $fb = $input['fb_messenger'];
            if (!filter_var($fb, FILTER_VALIDATE_URL) ||
                stripos(parse_url($fb, PHP_URL_HOST) ?: '', 'facebook.com') === false) {
                $errors['fb_messenger'] = ['FB/Messenger must be a valid Facebook link'];
            }
        }

        // Barangay + District validation
        if (!empty($input['barangay'])) {
            $districtId = $input['district_id'] ?? null;
            if (!$districtId) {
                $errors['district'] = ['No district selected.'];
            } else {
                $exists = DB::table('locations')
                    ->where('barangay', $input['barangay'])
                    ->where('district_id', $districtId)
                    ->exists();
                if (!$exists) {
                    $errors['barangay'] = ["Barangay \"{$input['barangay']}\" and District ID \"{$districtId}\" not found."];
                }
            }
        }

        // ⭐ Detect REAL changes only
        $updatedFields = [];
        foreach ($input as $field => $value) {
            if (isset($errors[$field])) continue;

            $oldValue = $before[$field] ?? '';
            $newValue = $value;

            // ⭐ DISTRICT comparison patch
            if ($field === 'district') {
                $oldValue = preg_replace('/\D/', '', $oldValue);
                $newValue = preg_replace('/\D/', '', $newValue);
            }

            // ⭐ CLASS SCHEDULE comparison patch
            if ($field === 'class_schedule') {
                $oldValue = preg_replace('/\s+/', ' ', trim($oldValue));
                $newValue = preg_replace('/\s+/', ' ', trim($newValue));
            }

            if ($newValue !== $oldValue) {
                $updatedFields[$field] = $value;
            }

            // Apply updates to session (unchanged)
            $entries[$index][$field] = $value;
        }

        $entries[$index]['errors'] = $errors;
        session([$type . 'Entries' => $entries]);

        // Update DB (unchanged)
        $volunteerId = $entries[$index]['volunteer_id'] ?? null;
        if ($volunteerId && !empty($updatedFields)) {
            $volunteer = VolunteerProfile::find($volunteerId);
            if ($volunteer) {
                $volunteer->update(array_merge($updatedFields, ['status' => 'active']));
            }
        }

        // Log changes (unchanged)
        $adminId = Auth::guard('admin')->id();
        $labels = [
            'full_name'=>'Full Name','id_number'=>'School ID','course'=>'Course','year_level'=>'Year',
            'contact_number'=>'Contact #','emergency_contact'=>'Emergency #','email'=>'Email',
            'fb_messenger'=>'FB/Messenger','barangay'=>'Barangay','district'=>'District',
            'class_schedule'=>'Class Schedule'
        ];

        if ($adminId && !empty($updatedFields)) {
            $fieldDetails = [];
            foreach ($updatedFields as $field => $value) {
                if (isset($labels[$field])) {
                    $fieldDetails[] = "{$labels[$field]}='{$value}'";
                }
            }

            $fullName = $before['full_name'] ?? 'Unknown';
            $entityIdForLog = $entries[$index]['volunteer_id']
                ?? $entries[$index]['row_number']
                ?? ($index + 1);

            $entityTypeForLog = isset($entries[$index]['volunteer_id'])
                ? 'VolunteerProfile'
                : 'Volunteer Import';

            $this->logFact(
                'Update Entry',
                $adminId,
                $entityTypeForLog,
                $entityIdForLog,
                'Updated',
                "Updated entry #".($index+1)." '{$fullName}': ".implode(', ', $fieldDetails)."."
            );
        }

        // Build flash message
        $rowNumber   = $index + 1;
        $changesMade = false;

        // Collect detailed lines
        $detailLines = [];

        // Field changes
        foreach ($labels as $field => $label) {
            if (array_key_exists($field, $updatedFields)) {
                $changesMade = true;
                $oldValue = $before[$field] ?? '';
                $newValue = $updatedFields[$field];

                if ($oldValue !== $newValue) {
                    $detailLines[] =
                        "✅ <strong>{$label}:</strong> " .
                        "<span style='color:#007bff;'>{$newValue}</span>";
                } else {
                    $detailLines[] =
                        "ℹ️ <strong>{$label}:</strong> No change";
                }
            }
        }

        // Validation errors
        if (!empty($errors)) {
            $changesMade = true;
            foreach ($errors as $field => $msgs) {
                if (isset($labels[$field])) {
                    $val = $input[$field] ?? '';
                    $detailLines[] =
                        "⚠️ <strong>{$labels[$field]}:</strong> {$val} (" .
                        implode(', ', (array) $msgs) . ")";
                }
            }
        }

        // No changes
        if (!$changesMade) {
            $detailLines[] = "ℹ️ No changes were made.";
        }

        // Format details safely for HTML attribute
        $entryName   = $before['full_name'] ?? 'No Name';
        $detailsHtml = implode('<br>', $detailLines);
        $detailsAttr = htmlspecialchars($detailsHtml, ENT_QUOTES, 'UTF-8');

        // Build message
        $message = "
            <div style='text-align:left; font-size:1rem; line-height:1.55; color:#333;'>

                <!-- SUMMARY -->
                <div style='display:flex; flex-wrap:wrap; align-items:center; gap:10px;
                            font-weight:700; font-size:1.05rem;'>

                    <span style='color:#28a745;'>
                        ✅ Updated entry #{$rowNumber}
                        <span style=\"color:#B2000C; font-weight:600;\">{$entryName}</span>
                    </span>
        ";

        // Only show “View details” if we have details
        if (!empty($detailLines)) {
            $message .= "
                    <a href='#'
                    class='deleted-details-link'
                    data-details=\"{$detailsAttr}\"
                    style='margin-left:auto; margin-right:15px; font-size:0.95rem; font-weight:600;
                            color:#007bff; text-decoration:none;'>
                    View details
                    </a>
            ";
        }

        $message .= "</div></div>";

        // ALWAYS store in `success` (OPTION A)
        return redirect()->route('volunteer.import.index')
            ->with('success', $message)
            ->with('last_updated_table', $type)
            ->with('last_updated_index', $index);

    }

     /**
     * Move from Invalid -> Valid
     */
    public function moveInvalidToValid(Request $request)
    {
        $invalid = session('invalidEntries', []);
        $valid   = session('validEntries', []);
        $movedEntries   = [];
        $skippedEntries = [];
        $adminId = auth()->guard('admin')->id();

        $selectedIndices = $request->input('selected_invalid', []);

        if (!empty($selectedIndices)) {
            foreach ($selectedIndices as $index) {

                if (!isset($invalid[$index])) {
                    continue;
                }

                $entry = $invalid[$index];

                // Skip if row still has errors
                if (!empty($entry['errors'] ?? [])) {
                    $skippedEntries[] = [
                        'name'  => $entry['full_name'] ?? 'N/A',
                        'index' => $index
                    ];
                    continue;
                }

                // Move entry to valid
                unset($entry['errors'], $entry['error_message']);
                $valid[] = $entry;

                $movedEntries[] = [
                    'name'  => $entry['full_name'] ?? 'N/A',
                    'index' => $index
                ];

                unset($invalid[$index]);

                // Logging action
                $this->logFact(
                    'Move to Valid',
                    $adminId,
                    'Volunteer Import',
                    $entry['volunteer_id'] ?? $entry['row_number'] ?? null,
                    'Moved',
                    "Moved Volunteer Entry #".($index+1)." {$entry['full_name']} from invalid to valid."
                );
            }

            // Reindex arrays
            $invalid = array_values($invalid);
            $valid   = array_values($valid);

            session([
                'invalidEntries' => $invalid,
                'validEntries'   => $valid,
            ]);
        }

        // MESSAGE HANDLING

        $messageParts = [];
        $movedCount   = count($movedEntries);
        $skippedCount = count($skippedEntries);

        // ⭐ BULK MOVE MESSAGE
        if ($movedCount > 1) {
            $messageParts[] =
                "✨ <strong>{$movedCount} entries</strong> corrected and moved.";
        }

        // ⭐ SINGLE MOVE MESSAGE
        elseif ($movedCount === 1) {
            $e = $movedEntries[0];
            $messageParts[] =
                "✅ Moved Volunteer Entry #".($e['index']+1)." {$e['name']} to valid.";
        }

        // ⭐ SKIPPED MESSAGE
        if ($skippedCount > 0) {
            $skippedList = implode(', ', array_column($skippedEntries, 'name'));
            $messageParts[] = "⚠️ Could not move: {$skippedList}.";
        }

        // ⭐ NONE SELECTED OR NONE MOVED
        if ($movedCount === 0 && $skippedCount === 0) {
            $messageParts[] = "ℹ️ No invalid entries selected to move.";
        }

        return redirect()
            ->route('volunteer.import.index')
            ->with('success', implode(' ', $messageParts))
            ->with('last_updated_table', 'valid')
            ->with('show_success_modal', true);
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
            return back()
                ->withFragment('invalid-entries-table')
                ->with('success', "ℹ️ No valid entry selected to move.")
                ->with('show_success_modal', true);
        }

        $entry = $valid[$index];
        unset($valid[$index]);

        // Restore original index OR append
        if (isset($entry['original_index'])) {
            $invalid[$entry['original_index']] = $entry;
        } else {
            $invalid[] = $entry;
        }

        ksort($invalid);
        $invalid = array_values($invalid);

        session([
            'validEntries'   => array_values($valid),
            'invalidEntries' => $invalid,
            'last_updated_table' => 'invalid',
            'last_updated_index' => isset($entry['original_index'])
                ? $entry['original_index']
                : count($invalid) - 1,
        ]);

        // Log
        $this->logFact(
            'Move Back to Invalid',
            $adminId,
            'Volunteer Import',
            $entry['volunteer_id'] ?? $entry['row_number'] ?? null,
            'Moved Back',
            "Moved Volunteer Entry #".($index+1)." {$entry['full_name']} from valid to invalid."
        );

        return back()
            ->withFragment('invalid-entries-table')
            ->with(
                'success',
                "⚠️ Moved Volunteer Entry #".($index+1)." {$entry['full_name']} back to invalid."
            )
            ->with('show_success_modal', true);
    }
    
    /**
     * Delete Entries
     */
    public function deleteEntries(Request $request)
    {
        $tableType = $request->input('table_type'); // invalid / valid / logs
        $selected  = $request->input('selected', []);
        $adminId   = auth()->guard('admin')->id();

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

                        $volunteerId = $deletedData[$index]['volunteer_id'] ?? $deletedData[$index]['row_number'] ?? null;
                        $name        = $deletedData[$index]['full_name'] ?? 'No Name';

                        $this->logFact(
                            'Delete Entry',
                            $adminId,
                            'Volunteer Import',
                            $volunteerId,
                            'Deleted',
                            "Deleted Volunteer Entry #".($index+1)." {$name}"
                        );
                    }
                }
                session([$tableType . 'Entries' => array_values($entries)]);
                break;


            case 'logs':
                $deletedEntries = ImportLog::whereIn('import_id', $selected)->get();
                foreach ($deletedEntries as $entry) {
                    $deletedData[] = $entry->toArray();
                    $name = $entry->file_name ?? 'No Name';

                    $this->logFact(
                        'Delete Import Log',
                        $adminId,
                        'Volunteer Import',
                        $entry->import_id,
                        'Deleted',
                        "Deleted Import Log '{$name}' (ID {$entry->import_id})"
                    );
                }
                ImportLog::whereIn('import_id', $selected)->delete();
                break;


            default:
                return back()->with('error', '⚠️ Invalid table type.');
        }

        if (!empty($deletedData)) {

            // allow undo
            session(['deletedEntriesUndo' => [
                'tableType' => $tableType,
                'data'      => $deletedData,
                'timestamp' => now()
            ]]);

        $formatted = [];
        foreach ($deletedData as $index => $item) {
            $name = $item['full_name'] ?? ($item['file_name'] ?? 'No Name');

            // Single-line, no double quotes → safe for data- attribute
            $formatted[] =
                "Entry #" . ($index+1) . ": <span style='color:#B2000C; font-weight:600;'>{$name}</span>";
        }

        $total = count($formatted);

        // Start main container
        $message = "
        <div style='text-align:left; font-size:1rem; line-height:1.55; color:#333;'>

            <!-- ALWAYS VISIBLE SUMMARY -->
            <div style='display:flex; flex-wrap:wrap; align-items:center; gap:10px;
                        font-weight:700; font-size:1.05rem;'>

                <span style='color:#B2000C;'>🗑️ Deleted {$total} entr" . ($total > 1 ? "ies" : "y") . "</span>
        ";


        // =====================================================================
        // IF ONLY 1 ENTRY → show name immediately
        // =====================================================================
        if ($total === 1) {

            // Undo stays on same line
            $message .= "
                <a href='" . route('volunteer.import.undo-delete') . "'
                style='margin-left:auto; padding:4px 10px;
                        font-size:0.9rem; font-weight:600;
                        background:#007bff; color:#fff;
                        border-radius:6px; text-decoration:none;'>
                    Undo
                </a>
            </div>

            <div style='margin:6px 0 6px 2px;'>
                {$formatted[0]}
            </div>
            ";

        } else {

            // For multiple entries, join with <br>
            $detailsHtml = implode("<br>", $formatted);

            // View details + Undo on same line (right-aligned)
            $message .= "
                <a href='#'
                class='deleted-details-link'
                data-details=\"{$detailsHtml}\"
                style='margin-left:auto; font-size:0.95rem; font-weight:600;
                        color:#007bff; text-decoration:none;'>
                View details
                </a>

                <a href='" . route('volunteer.import.undo-delete') . "'
                style='padding:4px 10px; margin-right: 15px; font-size:0.9rem; font-weight:600;
                        background:#007bff; color:#fff; border-radius:6px;
                        text-decoration:none;'>
                    Undo
                </a>
            </div>
            ";
        }

        // Close main wrapper
        $message .= "</div>";
        }

        return back()
            ->with('delete_success', $message)   // delete modal
            ->with('success', $message)          // global banner
            ->with('last_updated_table', $tableType)
            ->with('last_updated_indices', $selected);
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
        $data      = $deleted['data'];

        switch ($tableType) {

            case 'invalid':
            case 'valid':
                $entries = session($tableType . 'Entries', []);
                foreach ($data as $index => $item) {

                    $entries[$index] = $item;

                    $volunteerId = $item['volunteer_id'] ?? $item['row_number'] ?? null;
                    $name        = $item['full_name'] ?? 'No Name';

                    $this->logFact(
                        'Restore Entry',
                        $adminId,
                        'Volunteer Import',
                        $volunteerId,
                        'Restored',
                        "Restored Volunteer Entry #".($index+1)." {$name}"
                    );
                }
                session([$tableType . 'Entries' => array_values($entries)]);
                break;


            case 'logs':
                foreach ($data as $index => $item) {

                    if (!ImportLog::where('import_id', $item['import_id'])->exists()) {

                        ImportLog::create($item);

                        $entityId = $item['import_id'] ?? null;
                        $name     = $item['file_name'] ?? 'No Name';

                        $this->logFact(
                            'Restore Import Log',
                            $adminId,
                            'Volunteer Import',
                            $entityId,
                            'Restored',
                            "Restored Import Log '{$name}' (ID {$entityId})"
                        );
                    }
                }
                break;


            default:
                return back()->with('error', '⚠️ Invalid table type for undo.');
        }

        session()->forget('deletedEntriesUndo');

        // Build pretty restored list
        $formatted = [];
        foreach ($data as $index => $item) {
            $name = $item['full_name'] ?? ($item['file_name'] ?? 'No Name');

            $formatted[] =
                "Entry #" . ($index+1) .
                ": <span style='color:#B2000C; font-weight:600;'>{$name}</span>";
        }

        $total = count($formatted);

        // --------------------------------------
        // Start main message
        // --------------------------------------
        $message = "
        <div style='text-align:left; font-size:1rem; line-height:1.55; color:#333;'>

            <!-- ALWAYS VISIBLE SUMMARY -->
            <div style='display:flex; flex-wrap:wrap; align-items:center; gap:10px;
                        font-weight:700; font-size:1.05rem;'>
                <span style='color:#28a745;'>♻️ Restored {$total} entr" . ($total > 1 ? "ies" : "y") . "</span>
        ";

        // ======================================================
        // If only one restored → show inline immediately
        // ======================================================
        if ($total === 1) {

            $message .= "
            </div>
            <div style='margin:6px 0 6px 2px;'>
                {$formatted[0]}
            </div>
            ";

        } else {

        // ======================================================
        // Multiple entries → show View details link
        // ======================================================
            $detailsHtml = implode("<br>", $formatted);

            $message .= "
                <a href='#'
                class='restored-details-link'
                data-details=\"{$detailsHtml}\"
                style='margin-left:auto; font-size:0.95rem; font-weight:600;
                        color:#007bff; text-decoration:none;'>
                    View details
                </a>
            </div>
            ";
        }

        $message .= "</div>";


        return back()
            ->with('undo_success', $message)     // success modal
            ->with('success', $message)          // global banner
            ->with('last_updated_table', $tableType)
            ->with('last_updated_indices', array_keys($data));
    }
/**
 * Validate and Save Selected Valid Entries
 */
public function validateAndSave(Request $request)
{
    Log::info('DEBUG_SUBMIT: raw selected_valid input', ['raw' => $request->input('selected_valid', [])]);
    Log::info('DEBUG_SUBMIT: session validEntries count', ['count' => count(session('validEntries', []))]);
    Log::info('DEBUG_SUBMIT: session invalidEntries count', ['count' => count(session('invalidEntries', []))]);

    $selectedIndexes = array_values(array_unique(array_map('intval',
        (array)$request->input('selected_valid', [])
    )));

    $validEntries   = session('validEntries', []);
    $invalidEntries = session('invalidEntries', []);
    $fileName       = session('uploaded_file_name', 'N/A');

    $admin = Auth::guard('admin')->user();
    if (!$admin) {
        return back()
            ->with('error_modal', "❌ Admin not authenticated.")
            ->with('error_modal_entries', [
                [
                    'row' => '-',
                    'name' => 'Authentication Failure',
                    'details' => 'Admin guard returned null user.'
                ]
            ]);
    }

    $adminId   = $admin->admin_id;
    $adminName = $admin->name ?? $admin->username ?? "Unknown Admin";

    /* ===============================================================
       BLOCK IF INVALID ENTRIES EXIST — RETURN LIST
    =============================================================== */
    if (!empty($invalidEntries)) {

        $entryList = array_map(function($item){
            return [
                'row'    => $item['row_number'] ?? '?',
                'name'   => $item['full_name'] ?? 'Unknown',
                'details'=> json_encode($item, JSON_PRETTY_PRINT)
            ];
        }, $invalidEntries);

        $rows = implode(', ', array_column($invalidEntries, 'row_number'));

        return back()
            ->with('error_modal', "❌ Cannot upload. Invalid entries found in row(s): <strong>{$rows}</strong>.")
            ->with('error_modal_entries', $entryList);
    }

    /* ===============================================================
       NO SELECTED ROWS
    =============================================================== */
    if (empty($selectedIndexes)) {
        return back()
            ->with('error_modal', "❌ No verified entries selected to save.")
            ->with('error_modal_entries', [
                [
                    'row' => '-',
                    'name' => 'No Selection',
                    'details' => 'selected_valid[] array was empty.'
                ]
            ]);
    }

    /* ===============================================================
       VALIDATE EACH SELECTED ROW
    =============================================================== */
    $entriesToSave = [];
    foreach ($selectedIndexes as $index) {

        if (!isset($validEntries[$index])) continue;

        $entry = $validEntries[$index];

        if ($this->validateRow($entry)) {

            $rowNumber = $entry['row_number'] ?? $index;

            return back()
                ->with('error_modal', "❌ Validation failed for row <strong>{$rowNumber}</strong>.")
                ->with('error_modal_entries', [
                    [
                        'row' => $rowNumber,
                        'name' => $entry['full_name'] ?? 'Unknown',
                        'details' => json_encode($entry, JSON_PRETTY_PRINT)
                    ]
                ]);
        }

        $entriesToSave[] = [
            'index' => $index,
            'data'  => $entry
        ];
    }

    if (empty($entriesToSave)) {
        return back()
            ->with('error_modal', "❌ No valid entries found to save.")
            ->with('error_modal_entries', [
                [
                    'row' => '-',
                    'name' => 'No Valid Entries',
                    'details' => 'entriesToSave array was empty.'
                ]
            ]);
    }

    /* ===============================================================
       TRY SAVING TO DATABASE
    =============================================================== */
    try {

        DB::transaction(function () use ($entriesToSave, $adminId, $adminName, $fileName) {

            $previewId  = session('import_log_id');
            $previewLog = ImportLog::find($previewId);

            $timestamp = now()->format('M d, Y h:i A');

            if ($previewLog) {
                $previewLog->update([
                    'status'          => 'Completed',
                    'total_records'   => count($entriesToSave),
                    'valid_count'     => count($entriesToSave),
                    'invalid_count'   => 0,
                    'duplicate_count' => 0,
                    'remarks'         =>
                        "Imported " . count($entriesToSave) . " row(s) on {$timestamp} by {$adminName}.<br>" .
                        "File: {$fileName}"
                ]);
            }

            foreach ($entriesToSave as $entryData) {

                $entry = $entryData['data'];
                $index = $entryData['index'];

                // Resolve Course ID
                $courseName = preg_replace('/\s+/', ' ', trim($entry['course'] ?? ''));
                $courseId = Course::whereRaw('LOWER(TRIM(course_name)) = ?', [
                    strtolower($courseName)
                ])->value('course_id');

                // Resolve Location
                $barangay    = $entry['barangay'] ?? null;
                $locationId  = $barangay ? Location::where('barangay', $barangay)->value('location_id') : null;
                $location    = $locationId ? Location::find($locationId) : null;

                $volunteer = VolunteerProfile::create([
                    'import_id'      => $previewId,
                    'full_name'      => $entry['full_name'],
                    'id_number'      => $entry['id_number'] ?? "TEMP-" . uniqid(),
                    'course_id'      => $courseId,
                    'year_level'     => $entry['year_level'],
                    'contact_number' => $entry['contact_number'],
                    'emergency_contact' => $entry['emergency_contact'],
                    'email'          => $entry['email'],
                    'fb_messenger'   => $entry['fb_messenger'],
                    'location_id'    => $locationId,
                    'barangay'       => $location->barangay ?? null,
                    'district'       => $location->district_id ?? null,
                    'class_schedule' => $entry['class_schedule'],
                    // Always use the local converted image
                    'profile_picture_url'  => $entry['profile_picture'] ?? null,
                    'profile_picture_path' => $entry['profile_picture_local'] ?? 'defaults/default_user.png',

                    'status' => 'active',
                ]);

                $this->logFact(
                    'Import Verified',
                    $adminId,
                    'VolunteerProfile',
                    $volunteer->volunteer_id,
                    'Imported',
                    "Imported Volunteer Entry #" . ($index + 1) . " – {$entry['full_name']}"
                );
            }
        });

        /* Clear session after success */
        session()->forget([
            'validEntries',
            'invalidEntries',
            'uploaded_file_name',
            'uploaded_file_path',
            'csv_imported',
            'import_log_id'
        ]);

       /* SUCCESS MESSAGE */
        $count     = count($entriesToSave);
        $timestamp = now()->format('M d, Y h:i A');

        $message = "
            <div style='font-size:1.05rem; line-height:1.55;'>
                <strong style='color:#28a745;'>✔ Successfully saved {$count} entries.</strong><br>
                Completed on <strong>{$timestamp}</strong><br>
                File: <strong>{$fileName}</strong>
            </div>
        ";

        return back()->with('submit_success', $message);


    } catch (\Exception $e) {

        Log::error("Import failed!", [
            'message' => $e->getMessage(),
            'trace'   => $e->getTraceAsString()
        ]);

        /* =======================================================
           GENERATE FRIENDLY ERROR MESSAGE
        ======================================================= */

        $msgLower = strtolower($e->getMessage());

        $friendlyMessage = "❌ Import failed unexpectedly.";
        $friendlyEntry   = "System Error";
        $details         = $e->getMessage();

        if (str_contains($msgLower, 'duplicate') || str_contains($msgLower, '1062')) {
            $friendlyMessage = "
                <strong style='color:#B2000C;'>⚠️ Duplicate entries detected.</strong><br>
                One or more School IDs or Emails already exist in the database.
            ";
            $friendlyEntry = "Duplicate Entry";
        }

        elseif (str_contains($msgLower, 'foreign key')) {
            $friendlyMessage = "
                <strong style='color:#B2000C;'>⚠️ Invalid linked data.</strong><br>
                One entry references a course or location that does not exist.
            ";
            $friendlyEntry = "Invalid Reference";
        }

        elseif (str_contains($msgLower, 'sqlstate')) {
            $friendlyMessage = "
                <strong style='color:#B2000C;'>⚠️ Database Error</strong><br>
                A database issue occurred while saving the entries.
            ";
            $friendlyEntry = "Database Error";
        }

        return back()
            ->with('error_modal', $friendlyMessage)
            ->with('error_modal_technical', $e->getMessage() . "\n\n" . $e->getTraceAsString())
            ->with('error_modal_entries', [
                [
                    'row'     => '-',
                    'name'    => $friendlyEntry,
                    'details' => $details
                ]
            ]);
    }
}


    /**
     * Reset Preview
     */
    public function resetImports(Request $request)
    {
        $validCount     = session()->has('validEntries') ? count(session('validEntries')) : 0;
        $invalidCount   = session()->has('invalidEntries') ? count(session('invalidEntries')) : 0;
        $duplicateCount = session()->has('duplicateEntries') ? count(session('duplicateEntries')) : 0;

        $totalCleared = $validCount + $invalidCount + $duplicateCount;

        $fileName          = session('uploaded_file_name', 'N/A');
        $originalImportId  = session('import_log_id');

        $admin        = auth()->guard('admin')->user();
        $currentAdminId = $admin->admin_id ?? null;
        $adminName      = $admin->name ?? $admin->username ?? "Unknown Admin";
        $formattedTime  = now()->format('M d, Y h:i A');
        

        /* ----------------------------------------------------
        ⭐ CANCEL ACTIVE PENDING PREVIEW
        ---------------------------------------------------- */
        if ($originalImportId) {
            $originalLog = ImportLog::find($originalImportId);

            if ($originalLog && $originalLog->status === 'Pending') {

                $cancelRemark = "Import preview was cancelled by {$adminName} on {$formattedTime}.";

                $originalLog->update([
                    'admin_id'      => $originalLog->admin_id ?: $currentAdminId,
                    'total_records' => $originalLog->total_records ?: $totalCleared,
                    'valid_count'   => $originalLog->valid_count ?: $validCount,
                    'invalid_count' => $originalLog->invalid_count ?: $invalidCount,
                    'duplicate_count' => $originalLog->duplicate_count ?: $duplicateCount,
                    'status'        => 'Cancelled',
                    'remarks'       => $cancelRemark,
                ]);

                $this->logFact(
                    'Import Cancelled',
                    $currentAdminId,
                    'ImportLog',
                    $originalImportId,
                    'Cancelled',
                    $cancelRemark
                );
            }
        }


        /* ----------------------------------------------------
        ⭐ CREATE NEW RESET LOG
        ---------------------------------------------------- */
        $resetLog = ImportLog::create([
            'file_name'       => $fileName,
            'admin_id'        => $currentAdminId,
            'total_records'   => $totalCleared,
            'valid_count'     => $validCount,
            'invalid_count'   => $invalidCount,
            'duplicate_count' => $duplicateCount,
            'status'          => 'Reset',
            'remarks'         => "Reset import preview cleared {$totalCleared} row(s) on {$formattedTime} by {$adminName}.",
        ]);

        $this->logFact(
            'Reset Import Preview',
            $currentAdminId,
            'ImportLog',
            $resetLog->import_id,
            'Success',
            "Import preview reset by {$adminName}. Total rows cleared: {$totalCleared}."
        );


        /* ----------------------------------------------------
        ⭐ CLEAR SESSION (NOW INCLUDING DUPLICATES)
        ---------------------------------------------------- */
        session()->forget([
            'validEntries',
            'invalidEntries',
            'duplicateEntries',   // ← YOU MISSED THIS LINE
            'uploaded_file_name',
            'uploaded_file_path',
            'csv_imported',
            'import_log_id',
            'lastUsedTable'
        ]);

        session()->flash('clearLastUsedTable', true);


        $message = "
            <div style='font-size:1.05rem; line-height:1.55; color:#333;'>

                <!-- ALWAYS SHOWN -->
                <div>
                    <span style='font-weight:700; color:#28a745;'>♻️ Import preview reset successfully</span>
                    <a href='#' 
                    onclick=\"var d=this.parentNode.nextElementSibling; 
                                d.style.display=d.style.display==='none'?'block':'none'; 
                                this.innerHTML=d.style.display==='none'?'Show Details':'Hide Details'; 
                                return false;\"
                    style='margin-left:10px; font-size:0.9rem; color:#007bff; text-decoration:none; font-weight:600;'>
                        Show Details
                    </a>
                </div>

                <!-- DETAILS (hidden by default) -->
                <div style='display:none; margin-top:12px; padding-left:4px;'>

                    Performed by: 
                    <strong style='color:#B2000C;'>{$adminName}</strong><br>

                    Time: 
                    <strong>{$formattedTime}</strong><br><br>

                    <span style='font-weight:600;'>Cleared rows:</span>
                    <strong style='color:#B2000C;'>{$totalCleared}</strong><br>

                    <span style='margin-left:10px;'>Valid:</span>
                    <strong>{$validCount}</strong><br>

                    <span style='margin-left:10px;'>Invalid:</span>
                    <strong>{$invalidCount}</strong><br>

                    <span style='margin-left:10px;'>Duplicates:</span>
                    <strong>{$duplicateCount}</strong><br><br>

                    <span style='font-weight:600;'>Reset Log ID:</span>
                    <strong style='color:#B2000C;'>{$resetLog->import_id}</strong>
                </div>

            </div>
        ";

        return redirect()
            ->route('volunteer.import.index')
            ->with('success', $message)
            ->with('resetSuccess', $message)
            ->with('scrollToInvalid', true);
    }

    /**
     * Check if Entries Already Exist in Database (table:volunteer_profile)
     */
    public function checkDuplicates(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'duplicates' => [],
                'message'    => 'No IDs provided.'
            ]);
        }

        $existing = VolunteerProfile::whereIn('id_number', $ids)
                                    ->pluck('id_number')
                                    ->toArray();

        if (!empty($existing)) {
            return response()->json([
                'duplicates' => $existing,
                'message'    =>
                    "⚠️ Cannot submit. The following ID(s) already exist:<br>" .
                    "<strong>" . implode(', ', $existing) . "</strong>"
            ]);
        }

        return response()->json([
            'duplicates' => [],
            'message' => null
        ]);
    }


    /**
     * Update Class Schedule
     */
    public function updateSchedule(Request $request, $id)
    {
        try {
            $scheduleString = $request->input('schedule');
            $type = $request->input('type', 'valid'); // 'valid' or 'invalid'

            if (!$scheduleString || !is_string($scheduleString)) {
                return redirect()->back()->with('error', 'Invalid schedule data.');
            }

            // Choose session array based on type
            $entries = session($type . 'Entries', []);
            if (!isset($entries[$id])) {
                return redirect()->back()->with('error', 'Entry not found in session.');
            }

            $entry = $entries[$id];
            $oldSchedule = $entry['class_schedule'] ?? '';

            $days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

            $normalize = function($schedule) use ($days) {
                $result = [];
                foreach ($days as $day) {
                    if (preg_match("/{$day}:\s*(.*?)(?=(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|$))/is", $schedule, $match)) {
                        $raw = trim($match[1]);
                        $raw = str_ireplace('No Class', '', $raw);
                        $raw = preg_replace('/\s+/', ' ', $raw);
                        $result[$day] = $raw ? explode(' ', $raw) : [];
                    } else {
                        $result[$day] = [];
                    }
                }
                return $result;
            };

            $oldParts = $normalize($oldSchedule);
            $newPartsRaw = $normalize($scheduleString);

            // PATCH: remove empty values before diffing
            $clean = function($arr) {
                return array_values(array_filter($arr, fn($v) => trim($v) !== ""));
            };

            // Normalize new values to HH:MM-HH:MM
            $newParts = [];
            $reformattedCells = [];
            foreach ($days as $day) {
                $newParts[$day] = [];
                foreach ($newPartsRaw[$day] as $idx => $val) {
                    $parts = explode('-', $val);
                    if (count($parts) === 2) {
                        $parts = array_map(fn($p) => preg_match('/^\d{1,2}$/', $p) ? $p.':00' : $p, $parts);
                        $norm = implode('-', $parts);
                    } else {
                        $norm = $val;
                    }
                    $newParts[$day][$idx] = $norm;

                    $oldVal = $oldParts[$day][$idx] ?? null;

                    // PATCH: prevent reformatted "" → "" false detection
                    if ($oldVal && $norm && $oldVal !== $norm && !in_array($norm, $oldParts[$day] ?? [])) {
                        $reformattedCells[$day][] = ['from' => $oldVal, 'to' => $norm];
                    }
                }
            }

            $dayChanges = [];
            $changesMade = false;

            foreach ($days as $day) {
                // PATCH: clean before diff
                $cleanNew = $clean($newParts[$day] ?? []);
                $cleanOld = $clean($oldParts[$day] ?? []);

                $addedDay = array_diff($cleanNew, $cleanOld);
                $removedDay = array_diff($cleanOld, $cleanNew);

                $dayChanges[$day] = ['added' => $addedDay, 'removed' => $removedDay];

                if (count($addedDay) > 0 || count($removedDay) > 0 || !empty($reformattedCells[$day] ?? [])) {
                    $changesMade = true;
                }
            }

            // Update session
            $entries[$id]['class_schedule'] = trim($scheduleString);
            session([$type . 'Entries' => $entries]);

            // Build flash message
            $rowNumber = $entry['row_number'] ?? ($id + 1);

            if (!$changesMade) {
                $message = "<strong>Row #{$rowNumber} for {$entry['full_name']}</strong><br>ℹ️ No changes made";
            } else {
                $message = "<strong>Updated Class Schedule (Row #{$rowNumber}) for {$entry['full_name']}</strong><br>";
                foreach ($days as $day) {
                    $added = $dayChanges[$day]['added'];
                    $removed = $dayChanges[$day]['removed'];
                    $reformatted = $reformattedCells[$day] ?? [];

                    $parts = [];
                    if (!empty($added)) $parts[] = "✅ <span style='color:#007bff;'>Added: ".implode(', ', $added)."</span>";
                    if (!empty($removed)) $parts[] = "⚠️ <span style='color:red;'>Removed: ".implode(', ', $removed)."</span>";
                    if (!empty($reformatted)) {
                        $parts[] = "ℹ️ <span style='color:orange;'>Reformatted: " . implode(', ', array_map(fn($c) => "{$c['from']} → {$c['to']}", $reformatted)) . "</span>";
                    }

                    $message .= "<strong>$day:</strong> " . ($parts ? implode(' | ', $parts) : "ℹ️ No change") . "<br>";
                }
            }

            // ---------------------------------------------------------
            // ⭐ CLEAN & CONSISTENT FACTLOG FORMAT
            // ---------------------------------------------------------
            $adminId = auth()->guard('admin')->id() ?? null;
            $volunteerName = $entry['full_name'] ?? 'Unknown';
            $entityRow = $entry['row_number'] ?? ($id + 1);

            if ($changesMade) {
                // Build structured changes
                $detailParts = [];
                foreach ($days as $day) {
                    $added = $dayChanges[$day]['added'];
                    $removed = $dayChanges[$day]['removed'];
                    $reformatted = $reformattedCells[$day] ?? [];

                    if (!empty($added))       $detailParts[] = "$day Added: " . implode(', ', $added);
                    if (!empty($removed))     $detailParts[] = "$day Removed: " . implode(', ', $removed);
                    foreach ($reformatted as $cell) {
                        $detailParts[] = "$day Reformatted: {$cell['from']} → {$cell['to']}";
                    }
                }

                $detailsString = implode('; ', $detailParts);

                $this->logFact(
                    'Update Schedule',
                    $adminId,
                    'Volunteer Import',
                    $entry['volunteer_id'] ?? $entityRow,
                    'Updated',
                    "Updated Class Schedule for Volunteer Entry #{$entityRow} – {$volunteerName}. {$detailsString}"
                );

            } else {

                $this->logFact(
                    'Update Schedule',
                    $adminId,
                    'Volunteer Import',
                    $entry['volunteer_id'] ?? $entityRow,
                    'No Change',
                    "No changes made to Class Schedule for Volunteer Entry #{$entityRow} – {$volunteerName}."
                );
            }

            return redirect()->back()
                ->with('success', $message)
                ->with('last_updated_table', $type)
                ->with('last_updated_index', $id);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update / Replace Profile Picture
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'index' => 'required|integer',
            'type'  => 'required|in:valid,invalid',
            'file'  => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ]);

        $type  = $request->type;
        $index = $request->index;

        $sessionKey = $type . 'Entries';
        $entries    = session($sessionKey, []);

        if (!isset($entries[$index])) {
            return back()->with('error', 'Entry not found.');
        }

        $entry         = $entries[$index];
        $volunteerName = $entry['full_name'] ?? 'Unknown';
        $rowNum        = $entry['row_number'] ?? ($index + 1);

        /* ===========================
        DELETE OLD PICTURE SAFELY
        =========================== */
        $oldPath = $entry['profile_picture_local'] ?? null;

        // ❗ Never delete the global default image
        if ($oldPath && $oldPath !== "defaults/default_user.png") {
            Storage::disk('public')->delete(ltrim($oldPath, '/'));
        }

        /* ===========================
        STORE NEW PICTURE
        =========================== */
        $uploaded = $request->file('file');
        $path = $uploaded->store('profile_pictures/volunteers', 'public');

        /* ===========================
        UPDATE SESSION
        =========================== */
        $entries[$index]['profile_picture_local'] = $path;
        $entries[$index]['profile_picture'] = null;
        
        session([$sessionKey => $entries]);

        /* ===========================
        UPDATE DB
        =========================== */
        if (!empty($entry['volunteer_id'])) {
            if ($v = VolunteerProfile::find($entry['volunteer_id'])) {
                $v->update([
                    'profile_picture_path' => $path,
                    'profile_picture_url'  => null
                ]);
            }
        }

        /* ===========================
        SUCCESS MESSAGE
        =========================== */
        $message = "
            <div style='font-size:1rem; line-height:1.55;'>
                <strong>Updated Profile Picture</strong><br>
                Entry #{$rowNum} – {$volunteerName}<br><br>
                <strong>New file:</strong> {$uploaded->getClientOriginalName()}
            </div>
        ";

        return back()
            ->with('success', $message)
            ->with('last_updated_table', $type)
            ->with('last_updated_indices', [$index]);
    }


    /**
     * Set Default Profile Picture
     */
    public function setDefaultPicture(Request $request)
    {
        $request->validate([
            'index' => 'required|integer',
            'type'  => 'required|in:valid,invalid',
        ]);

        $type  = $request->type;
        $index = $request->index;

        $sessionKey = $type . 'Entries';
        $entries    = session($sessionKey, []);

        if (!isset($entries[$index])) {
            return back()->with('error', 'Entry not found.');
        }

        $entry = $entries[$index];

        $volunteerName = $entry['full_name'] ?? 'Unknown';
        $rowNum        = $entry['row_number'] ?? ($index + 1);

        $current = $entry['profile_picture_local'] ?? null;

        $default = "defaults/default_user.png";

        /* ===========================
        NO CHANGE
        =========================== */
        if ($current === $default) {
            return back()->with('info', "No changes made for Entry #{$rowNum} – {$volunteerName}");
        }

        /* ===========================
        DELETE OLD PICTURE SAFELY
        =========================== */
        if ($current && $current !== $default) {
            Storage::disk('public')->delete(ltrim($current, '/'));
        }

        /* ===========================
        APPLY DEFAULT (NO COPY!!)
        =========================== */
        $entries[$index]['profile_picture_local'] = $default;
        $entries[$index]['profile_picture'] = null;
        session([$sessionKey => $entries]);

        /* ===========================
        UPDATE DB
        =========================== */
        if (!empty($entry['volunteer_id'])) {
            if ($v = VolunteerProfile::find($entry['volunteer_id'])) {
                $v->update([
                    'profile_picture_path' => $default,
                    'profile_picture_url'  => null
                ]);
            }
        }

        /* ===========================
        SUCCESS MESSAGE
        =========================== */
        $message = "
            <div style='font-size:1rem; line-height:1.55;'>
                <strong>Profile Picture Reset to Default</strong><br>
                Entry #{$rowNum} – {$volunteerName}
            </div>
        ";

    return back()
        
        ->with('picture_message', $message)        // picture modal only
        ->with('last_updated_table', $type)
        ->with('last_updated_indices', [$index]);

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
        $details = null
    ): FactLog {

        // Resolve admin safely
        $admin = Auth::guard('admin')->user();
        $adminId = is_numeric($adminId) ? (int)$adminId : ($admin->admin_id ?? null);

        // Encode details nicely
        $encodedDetails = is_array($details) || is_object($details)
            ? json_encode($details, JSON_UNESCAPED_UNICODE)
            : (string)$details;

        // Determine entity_type + proper entity_id
        if (is_object($entity)) {
            $entityType = class_basename($entity);

            // Use getKey() so models with custom PKs (volunteer_id, import_id, admin_id) work
            $modelKey = method_exists($entity, 'getKey') ? $entity->getKey() : null;

            $entityId = $entityId ?? $modelKey;
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
        ]);
    }


}
