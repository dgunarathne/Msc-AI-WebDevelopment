<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueRecord extends Model
{
    protected $fillable = [
        'charger_id',
        'current_queue_length',
        'active_sessions',
        'number_of_connectors',
        'avg_session_minutes',
        'last_updated_at',
    ];

    protected $casts = [
        'avg_session_minutes' => 'float',
        'last_updated_at' => 'datetime',
    ];
}
