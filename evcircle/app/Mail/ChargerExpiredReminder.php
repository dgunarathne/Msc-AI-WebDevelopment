<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EvCharger;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChargerExpiredReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $charger;

    /**
     * Create a new message instance.
     */
    public function __construct(EvCharger $charger)
    {
        $this->charger = $charger;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your EV Charger Has Expired')
                    ->view('charger-expired')
                    ->with([
                        'charger' => $this->charger
                    ]);
    }
}
