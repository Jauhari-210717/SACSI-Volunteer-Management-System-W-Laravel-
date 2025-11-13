<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ImportLog;
use Carbon\Carbon;

class CleanupPendingImports extends Command
{
    protected $signature = 'imports:cleanup-pending';
    protected $description = 'Mark old Pending imports as Abandoned if they are stale';

    public function handle()
    {
        // Define threshold: Pending for more than 2 hours
        $threshold = Carbon::now()->subHours(2);

        // Fetch stale imports
        $staleImports = ImportLog::where('status', 'Pending')
                                 ->where('created_at', '<', $threshold)
                                 ->get();

        foreach ($staleImports as $import) {
            $import->update([
                'status'  => 'Abandoned',
                'admin_id'=> $import->admin_id, // preserve the original admin who started it
                'remarks' => 'Pending import automatically abandoned by scheduler.'
            ]);

            $this->info("Import ID: {$import->import_id} marked as Abandoned.");
        }

        $this->info("Cleanup complete. Total: " . $staleImports->count());
        return 0;
    }
}
