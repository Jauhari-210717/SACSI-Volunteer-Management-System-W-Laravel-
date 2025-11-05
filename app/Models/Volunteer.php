<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $table = 'volunteers';

    protected $fillable = [
        'volunteer_code',
        'registration_date',
        'status',
    ];

    /**
     * Relationship: Volunteer has one Profile
     */
    public function profile()
    {
        return $this->hasOne(VolunteerProfile::class, 'volunteer_id', 'id');
    }

    /**
     * Optional relationship to import logs (if connected later)
     */
    public function importLogs()
    {
        return $this->hasMany(ImportLog::class, 'volunteer_id', 'id');
    }
}
