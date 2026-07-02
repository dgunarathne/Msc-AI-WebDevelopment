<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BecomeSellerReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->subject('Welcome to EV Circle')
                ->view('become')
                ->with(['name' => $this->name]);
    }
}
