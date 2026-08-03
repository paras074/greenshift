<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoaSignedRealtimeUpdate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Pass your payload containing document_signed, url, and lead_id
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Broadcast on a clear public channel
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('loa-updates'),
        ];
    }

    /**
     * The custom event alias name for your JS listener
     */
    public function broadcastAs(): string
    {
        return 'loa.signed';
    }
}