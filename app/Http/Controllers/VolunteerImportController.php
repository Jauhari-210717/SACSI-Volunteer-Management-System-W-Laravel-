<?php

namespace App\Http\Controllers;

use App\Models\VolunteerImport;
use App\Models\VolunteerProfile;
use App\Models\ImportLog;
use App\Models\FactLog;
use App\Models\FactType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class VolunteerImportController extends Controller
{
    /**
     * Helper: Log to fact_logs table
     */
    private function logFact(string $factTypeName, ?int $adminId, string $entityType, ?int $entityId, string $action, ?string $details = null): void
    {
        // Ensure FactType exists or create it
        $factType = FactType::firstOrCreate(
            ['type_name' => $factTypeName],
            ['description' => $factTypeName]
        );

        // Record to fact_logs
        FactLog::create([
            'fact_type_id' => $factType->fact_type_id,
            'admin_id' => $adminId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'details' => $details,
            'timestamp' => now(),
        ]);
    }

    /**
     * Display the Volunteer Import Dashboard
     */
    public function index()
    {
        $imports = VolunteerImport::with('admin')->latest()->get();
        $importLogs = ImportLog::with('admin')->latest()->get();

        // ✅ Adjusted view path to match your actual file
        // located at: resources/views/volunteer_import/volunteer_import.blade.php
        return view('volunteer_import.volunteer_import', compact('imports', 'importLogs'));
    }

    /**
     * Handle CSV Upload
     */
    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Store file in storage/app/public/imports
        $path = $file->storeAs('imports', $filename, 'public');

        // Create a new import record
        $import = VolunteerImport::create([
            'filename' => $filename,
            'admin_id' => Auth::id(),
            'status' => 'Pending',
        ]);

        // Log upload activity
        $this->logFact(
            'Import Upload',
            Auth::id(),
            VolunteerImport::class,
            $import->import_id,
            'Upload',
            'Volunteer import file uploaded by ' . Auth::user()->username
        );

        return redirect()->route('volunteer.import.index')
            ->with('success', 'File uploaded successfully and ready for validation.');
    }

    /**
     * Validate the Imported Data (Mock Logic)
     */
    public function validateEntries(VolunteerImport $import)
    {
        $import->update([
            'total_records' => 50,
            'valid_records' => 45,
            'invalid_records' => 5,
            'status' => 'Validated',
        ]);

        $this->logFact(
            'Validation',
            Auth::id(),
            VolunteerImport::class,
            $import->import_id,
            'Validate',
            'Validated volunteer import ID #' . $import->import_id
        );

        return redirect()->route('volunteer.import.index')
            ->with('success', 'Validation complete!');
    }

    /**
     * Submit Validated Records to VolunteerProfiles and Log Import
     */
    public function submitToDatabase(VolunteerImport $import)
    {
        DB::transaction(function () use ($import) {
            // Update related volunteer profiles as active (placeholder logic)
            VolunteerProfile::where('import_id', $import->import_id)
                ->update(['status' => 'Active']);

            // Record submission in import_logs
            ImportLog::create([
                'filename' => $import->filename,
                'admin_id' => $import->admin_id,
                'total_records' => $import->total_records,
                'status' => 'Submitted',
            ]);

            // Update main import record
            $import->update(['status' => 'Submitted']);

            // Log fact
            $this->logFact(
                'Database Submission',
                Auth::id(),
                VolunteerImport::class,
                $import->import_id,
                'Submit',
                'Volunteer records successfully submitted to database (Import ID #' . $import->import_id . ').'
            );
        });

        return redirect()->route('volunteer.import.index')
            ->with('success', 'Records successfully submitted to the volunteer database.');
    }
}
