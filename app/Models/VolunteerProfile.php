<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerProfile extends Model
{
    use HasFactory;

    protected $table = 'volunteer_profiles';
    protected $primaryKey = 'id'; // primary key

    protected $fillable = [
        'volunteer_id',
        'import_id',
        'full_name',
        'school_id',         // updated field name
        'course',
        'year_level',
        'contact_number',
        'emergency_contact', // updated field name
        'email',
        'fb_messenger',      // updated field name
        'barangay',
        'district',
        'certificates',
        'status',
        'class_schedule',
        'notes',
    ];

    /**
     * Relation to Volunteer
     */
    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class, 'volunteer_id', 'id');
    }

    /**
     * Relation to ImportLog
     */
    public function importLog()
    {
        return $this->belongsTo(ImportLog::class, 'import_id', 'import_id');
    }
}
