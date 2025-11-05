<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactType extends Model
{
    use HasFactory;

    protected $table = 'fact_types'; // pluralized
    protected $primaryKey = 'fact_type_id';

    protected $fillable = [
        'type_name',
        'description',
    ];

    public function factLogs()
    {
        return $this->hasMany(FactLog::class, 'fact_type_id', 'fact_type_id');
    }
}
