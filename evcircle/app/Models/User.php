<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 
        'last_name',  
        'mobile',     
        'image_url',
        'email',
        'password',
        'profile_status', 
        'subscription', 
        'income', 
        'changings', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function chargingHistories()
    {
        return $this->hasMany(\App\Models\ChargingHistory::class);
    }
    public function bookings()
{
    return $this->hasMany(Booking::class);
}
public function notifications()
{
    return $this->hasMany(Notification::class);
}
public function ev_chargers()
{
    return $this->hasMany(\App\Models\EvCharger::class);
}



}
