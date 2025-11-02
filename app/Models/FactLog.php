<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactLog extends Model
{
    use HasFactory;

    protected $table = 'fact_logs';
    protected $primaryKey = 'fact_id';
    public $timestamps = false;

    protected $fillable = [
        'fact_type_id',
        'admin_id',
        'entity_type',
        'entity_id',
        'action',
        'details',
        'timestamp',
    ];

    // Relationship: fact belongs to a fact type
    public function factType()
    {
        return $this->belongsTo(FactType::class, 'fact_type_id', 'fact_type_id');
    }

    // Relationship: fact belongs to an admin
    public function admin()
    {
        return $this->belongsTo(AdminAccount::class, 'admin_id', 'admin_id');
    }
}
