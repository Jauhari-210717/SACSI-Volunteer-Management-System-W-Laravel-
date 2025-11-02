<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactType extends Model
{
    use HasFactory;

    protected $table = 'fact_type';
    protected $primaryKey = 'fact_type_id';
    public $timestamps = true; // use timestamps

    protected $fillable = [
        'type_name',
        'description',
    ];

    // Relationship: a fact type has many fact logs
    public function factLogs()
    {
        return $this->hasMany(FactLog::class, 'fact_type_id', 'fact_type_id');
    }
}
