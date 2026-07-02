<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvCharger extends Model
{
     protected $fillable = [
        'user_id',
        'station_name',
        'location',
        'charger_type',
        'power_type',
        'connector_type',
        'power_output',
        'price_per_kwh',
        'latitude',
        'longitude',
        'promo_code',
        'active_until',
        'active_from'
    ];

    /**
     * The user who registered the charger.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function bookings()
{
    return $this->hasMany(Booking::class);
}

}