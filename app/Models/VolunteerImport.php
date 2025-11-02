<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerImport extends Model
{
    use HasFactory;

    protected $primaryKey = 'import_id';

    protected $fillable = [
        'filename',
        'admin_id',
        'total_records',
        'valid_records',
        'invalid_records',
        'status',
    ];

    /** Relationships **/

    // Linked admin (uploader)
    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id');
    }

    // Related import log
    public function log()
    {
        return $this->hasOne(ImportLog::class, 'import_id');
    }

    // Volunteers imported in this batch
    public function volunteers()
    {
        return $this->hasMany(VolunteerProfile::class, 'import_id');
    }

    // Optional: Fact Logs (system events)
    public function factLogs()
    {
        return $this->morphMany(FactLog::class, 'factable');
    }
}
