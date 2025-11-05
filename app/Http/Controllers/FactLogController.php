<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Volunteer;
use App\Models\VolunteerProfile;

class FactLogController extends Controller
{
    /**
     * Log an action into fact_logs
     *
     * @param string $factTypeName Name of the fact type (e.g., 'Volunteer Created', 'Import Verified')
     * @param string|null $entityType e.g., 'Volunteer'
     * @param int|null $entityId ID of the entity
     * @param string|null $action e.g., 'created', 'updated'
     * @param string|null $details Optional description/details
     * @param int|null $importId Optional import log ID
     */
    public function logAction(
        string $factTypeName, 
        ?string $entityType = null, 
        ?int $entityId = null, 
        ?string $action = null, 
        ?string $details = null, 
        ?int $importId = null
    ): void {
        $admin = Auth::guard('admin')->user();

        // Ensure fact type exists or create it
        $factType = FactType::firstOrCreate(
            ['type_name' => $factTypeName],
            ['description' => $factTypeName]
        );

        // Create the fact log
        FactLog::create([
            'fact_type_id' => $factType->fact_type_id,
            'admin_id' => $admin?->admin_id,
            'import_id' => $importId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'details' => $details,
            'logged_at' => now(),
        ]);
    }

    /**
     * Display a paginated list of logs with optional filters
     */
    public function index(Request $request)
    {
        $query = FactLog::with(['factType', 'admin']);

        if ($request->filled('fact_type')) {
            $query->whereHas('factType', fn($q) =>
                $q->where('type_name', 'like', '%' . $request->fact_type . '%')
            );
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('details', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%')
                  ->orWhere('entity_type', 'like', '%' . $request->search . '%');
            });
        }

        $logs = $query->orderBy('logged_at', 'desc')->paginate(20);

        return view('fact_logs.index', compact('logs'));
    }

    /**
     * Delete a specific fact log entry
     */
    public function destroy(int $id)
    {
        $log = FactLog::findOrFail($id);
        $admin = Auth::guard('admin')->user();

        if ($admin?->role !== 'super_admin') {
            return redirect()->back()->with('error', 'Only super admins can delete logs.');
        }

        $log->delete();

        // Log the deletion action
        $this->logAction(
            'Fact Log Management',
            'fact_logs',
            $id,
            'delete_log',
            "Fact log ID {$id} deleted by {$admin->username}"
        );

        return redirect()->back()->with('success', 'Log deleted successfully.');
    }
}
