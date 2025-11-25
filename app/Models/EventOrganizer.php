<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventOrganizer extends Model
{
    protected $table = 'event_organizers';
    protected $primaryKey = 'organizer_id';

    protected $fillable = [
        'event_id',
        'name',
        'email',
        'contact',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
}
