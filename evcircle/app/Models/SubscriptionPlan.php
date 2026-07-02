<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;
     protected $fillable = [
        'name',
        'duration',
        'days',
        'price',
        'start_date',
        'popular',
    ];
}
