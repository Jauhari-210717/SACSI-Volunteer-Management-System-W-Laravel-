<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    // Primary key
    protected $primaryKey = 'import_id';

    // Mass assignable fields
    protected $fillable = [
        'file_name',
        'admin_id',
        'total_records',
        'valid_count',
        'invalid_count',
        'duplicate_count',
        'status',
        'remarks',
        'completed_at',
    ];

    // Cast completed_at as datetime
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Admin relationship
     */
    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id', 'admin_id');
    }
}
