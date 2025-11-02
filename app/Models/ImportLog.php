<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'import_id';

    protected $fillable = [
        'filename',
        'import_date',
        'admin_id',
        'total_records',
        'status',
    ];

    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id');
    }
}
