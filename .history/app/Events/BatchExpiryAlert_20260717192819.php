<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatchExpiryAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $batchCode;
    public $namaBarang;
    public $status; // 'expired' atau 'warning'
    public $pesan;
    public function __construct($batchCode, $namaBarang, $status, $pesan)
    {
        $this->batchCode = $batchCode;
        $this->namaBarang = $namaBarang;
        $this->status = $status;
        $this->pesan = $pesan;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn()
    {
        // Kita buat channel khusus bernama "global-alerts"
        return new Channel('global-alerts');
    }
    public function broadcastAs()
    {
        return 'batch.expiry.alert';
    }
}
