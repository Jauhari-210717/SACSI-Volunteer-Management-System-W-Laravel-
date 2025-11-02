<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FactLog;
use App\Models\FactType;
use Illuminate\Support\Facades\Auth;

class FactLogController extends Controller
{
    /**
     * Log an action into fact_logs
     */
    public function logAction($factTypeName, $entityType, $entityId, $action, $details = null)
    {
        // Find or create the fact type
        $factType = FactType::firstOrCreate(
            ['type_name' => $factTypeName],
            ['description' => $factTypeName]
        );

        // Create the log
        FactLog::create([
            'fact_type_id' => $factType->fact_type_id,
            'admin_id' => Auth::guard('admin')->id(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'details' => $details,
            'timestamp' => now(),
        ]);
    }

    /**
     * Example: Show all logs with optional filters
     */
    public function index(Request $request)
    {
        $logs = FactLog::with('factType', 'admin')
            ->when($request->fact_type, fn($q) => $q->whereHas('factType', fn($q2) => $q2->where('type_name', $request->fact_type)))
            ->when($request->admin_id, fn($q) => $q->where('admin_id', $request->admin_id))
            ->orderBy('timestamp', 'desc')
            ->get();

        return view('fact_logs.index', compact('logs'));
    }
}
