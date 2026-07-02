<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class BookingReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('New Booking Received')
                    ->markdown('bookingreceived');
    }
}
