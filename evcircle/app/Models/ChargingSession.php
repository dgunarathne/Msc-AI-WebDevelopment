<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{
    protected $fillable = [
        'booking_id',
        'charger_id',
        'user_id',
        'queue_wait_seconds',
        'session_start_at',
        'session_end_at',
        'energy_delivered_kwh',
        'soc_start_percent',
        'soc_end_percent',
        'status',
    ];

    protected $casts = [
        'session_start_at' => 'datetime',
        'session_end_at' => 'datetime',
        'energy_delivered_kwh' => 'float',
        'soc_start_percent' => 'float',
        'soc_end_percent' => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
