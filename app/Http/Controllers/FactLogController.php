<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FactLog;

class FactLogController extends Controller
{
    /**
     * Log an action into fact_logs
     *
     * @param string $factTypeName
     * @param string|null $entityType
     * @param int|null $entityId
     * @param string|null $action
     * @param string|null $details
     * @param int|null $importId (unused but kept for compatibility)
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

        // FACT TYPE MODEL REMOVED — using entity_type + action instead
        FactLog::create([
            'admin_id'    => $admin?->admin_id,
            'entity_type' => $entityType ?? $factTypeName, // fallback so nothing breaks
            'entity_id'   => $entityId,
            'action'      => $action ?? $factTypeName,
            'details'     => $details,
            'timestamp'   => now(),
        ]);
    }

    /**
     * Display list of logs (updated to no longer use factType relation)
     */
    public function index(Request $request)
    {
        $query = FactLog::with(['admin']); // factType removed

        // fact_type filter REMOVED because table no longer has fact_type_id
        // but keep entity_type filter since it's now your category system
        if ($request->filled('fact_type')) {
            $query->where('entity_type', 'like', '%' . $request->fact_type . '%');
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('details', 'like', "%$search%")
                  ->orWhere('action', 'like', "%$search%")
                  ->orWhere('entity_type', 'like', "%$search%");
            });
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(20);

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

        // Still logs the deletion action with no changes needed
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
