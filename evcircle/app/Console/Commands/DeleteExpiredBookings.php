<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EvCharger;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChargerExpiredReminder;
use App\Mail\BecomeSellerReminder;

class CleanUpExpiredBookings extends Command
{
    protected $signature = 'cleanup:expired-bookings';
    protected $description = 'Delete expired pending bookings and notify users about expired chargers or suggest becoming sellers';

    public function handle()
    {
        $now = now();

        /** Step 1: Delete expired pending bookings */
        $deleted = Booking::where('status', 'pending')
            ->whereRaw("STR_TO_DATE(CONCAT(date, ' ', from_time), '%Y-%m-%d %H:%i:%s') < ?", [$now])
            ->delete();

        $this->info("$deleted expired pending bookings have been deleted.");

        /** Step 2: Only run notifications at allowed times */
        $allowedHours = [9, 15, 18]; // 9 AM, 3 PM, 6 PM
        $currentHour = now()->hour;

        if (!in_array($currentHour, $allowedHours)) {
            $this->info("Current hour ($currentHour) is not in allowed run times. Skipping notifications.");
            return;
        }

        /** Step 2a: Notify users of expired EV chargers (only once per day) */
        $expiredChargers = EvCharger::where('active_until', '<', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('last_notified_at')
                  ->orWhereDate('last_notified_at', '<', $now->toDateString());
            })
            ->with('user')
            ->get();

        foreach ($expiredChargers as $charger) {
            $user = $charger->user;

            if ($user && $user->email) {
                Mail::to($user->email)->queue(new ChargerExpiredReminder($charger));
                $this->info("Reminder sent to: {$user->email} for expired charger: {$charger->station_name}");

                $charger->update(['last_notified_at' => $now]);
            }
        }

        /** Step 3: Notify users who are not sellers (once per day) */
        $usersWithoutChargers = User::doesntHave('ev_chargers')
            ->where(function ($q) use ($now) {
                $q->whereNull('last_become_seller_notified_at')
                  ->orWhereDate('last_become_seller_notified_at', '<', $now->toDateString());
            })
            ->get();

        foreach ($usersWithoutChargers as $user) {
            if ($user->email) {
                Mail::to($user->email)->queue(new BecomeSellerReminder($user));
                $this->info("Invitation sent to become seller: {$user->email}");

                $user->update(['last_become_seller_notified_at' => $now]);
            }
        }

        $this->info("Cleanup and notifications completed.");
    }
}
