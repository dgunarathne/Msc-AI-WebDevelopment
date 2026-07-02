<?php

namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $bookingId;
    public $status;

    public function __construct($bookingId, $status)
    {
        $this->bookingId = $bookingId;
        $this->status = $status;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('booking-channel');
    }

    public function broadcastAs(): string
    {
        return 'booking.status.updated';
    }
}
