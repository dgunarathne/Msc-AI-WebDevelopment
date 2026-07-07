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

    /**
     * Base "other people's chargers, nearest first" query shared by
     * UserController::get_chargers/get_chargersg and the ML recommendation
     * endpoint, so the geo-filter/search logic has one source of truth.
     */
    public static function nearbyQuery(int $excludeUserId, ?string $search, ?float $lat, ?float $lng)
    {
        $query = static::query()->where('user_id', '!=', $excludeUserId);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('station_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('location', 'LIKE', '%' . $search . '%')
                  ->orWhere('charger_type', 'LIKE', '%' . $search . '%');
            });
        }

        if ($lat !== null && $lng !== null) {
            $query->selectRaw(
                "*,
                (6371 * acos(cos(radians(?))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians(?))
                + sin(radians(?))
                * sin(radians(latitude)))) AS distance",
                [$lat, $lng, $lat])
            ->having('distance', '<=', 5000000)
            ->orderBy('distance');
        }

        return $query;
    }
}