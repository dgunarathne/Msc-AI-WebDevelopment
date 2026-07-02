<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    // Table name (optional if it matches the plural of model name)
    protected $table = 'chargers';

    // Fields that can be mass-assigned
    protected $fillable = [
        'charger_id',
        'owner_type',
        'station_name',
        'latitude',
        'longitude',
        'address',
        'charger_type',
        'power_kw',
        'connector_type',
        'number_of_connectors',
        'price_per_kwh',
        'is_public',
    ];

    // Cast attributes to native types
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'power_kw' => 'integer',
        'number_of_connectors' => 'integer',
        'price_per_kwh' => 'float',
        'is_public' => 'boolean',
    ];
}
