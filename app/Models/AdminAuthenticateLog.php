<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminAuthenticateLog extends Model
{
    use HasFactory;

    protected $table = 'admin_authenticate_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false; // because login_time is manually set

    protected $fillable = [
        'admin_id',        // can be nullable for failed login
        'login_time',
        'ip_address',
        'status',
        'failure_reason'
    ];

    // Make sure login_time defaults to current timestamp
    protected $attributes = [
        'login_time' => null,
    ];

    // Relationship: admin account
    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id', 'admin_id');
    }
}
