<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'make',
        'model',
        'battery_capacity_kwh',
        'usable_battery_kwh',
        'current_soc_percent',
        'efficiency_wh_per_km',
        'connector_type',
        'max_charge_rate_kw',
        'is_default',
    ];

    protected $casts = [
        'battery_capacity_kwh' => 'float',
        'usable_battery_kwh' => 'float',
        'current_soc_percent' => 'float',
        'efficiency_wh_per_km' => 'float',
        'max_charge_rate_kw' => 'float',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
