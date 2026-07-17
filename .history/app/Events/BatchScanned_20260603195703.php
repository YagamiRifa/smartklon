<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatchScanned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $barcode;
    public $nama_barang;
    public $expiry_date;
    public function __construct($barcode, $nama_barang, $expiry_date)
    {
        $this->barcode = $barcode;
        $this->nama_barang = $nama_barang;
        $this->expiry_date = $expiry_date;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    // Tentukan "saluran" tempat event ini disiarkan
    public function broadcastOn()
    {
        return new Channel('scanner-channel');
    }

    // Nama event yang akan didengarkan oleh JavaScript
    public function broadcastAs()
    {
        return 'batch.scanned';
    }
}
