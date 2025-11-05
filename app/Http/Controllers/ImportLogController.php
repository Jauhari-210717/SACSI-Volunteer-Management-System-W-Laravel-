<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Volunteer;
use App\Models\VolunteerProfile;
class ImportLogController extends Controller

{
    /**
     * Display a listing of import logs
     */
    public function index()
    {
        $importLogs = ImportLog::with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('import_logs.index', compact('importLogs'));
    }

    /**
     * Store a new import log
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'total_records' => 'required|integer|min:0',
            'valid_count' => 'required|integer|min:0',
            'invalid_count' => 'required|integer|min:0',
            'duplicate_count' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'fact_type' => 'required|in:Import,Validation,Correction',
        ]);

        $admin = Auth::guard('admin')->user();

        ImportLog::create([
            'file_name' => $request->file_name,
            'admin_id' => $admin->admin_id ?? null,
            'fact_type' => $request->fact_type,
            'remarks' => $request->remarks ?? null,
            'total_records' => $request->total_records,
            'valid_count' => $request->valid_count,
            'invalid_count' => $request->invalid_count,
            'duplicate_count' => $request->duplicate_count ?? 0,
            'status' => 'Completed',
            'completed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Import log recorded successfully!');
    }
}
