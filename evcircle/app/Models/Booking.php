<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'charger_id',
        'seller_id',
        'cancel_note',
        'from_time',
        'to_time',
        'status',
        'date',
        'amount',
    ];
public function usere()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
public function reviews()
{
    return $this->hasMany(Review::class, 'booking_id', 'id');
}

   public function evcharger()
{
    return $this->belongsTo(EvCharger::class, 'charger_id');
}

}
