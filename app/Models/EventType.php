<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'event_types';
    protected $primaryKey = 'event_type_id';
    public $timestamps = true;

    protected $fillable = [
        'type_key',
        'label',
        'icon_class',
    ];

    /**
     * Relationship: Event has an event type
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'event_type_id', 'event_type_id');
    }
}
