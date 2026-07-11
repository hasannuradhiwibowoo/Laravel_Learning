<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IzinDiverifikasi implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $siswa_id;
    public $izin_id;
    public $status;
    public $catatan;

    public function __construct($siswa_id, $izin_id, $status, $catatan = null)
    {
        $this->siswa_id = $siswa_id;
        $this->izin_id = $izin_id;
        $this->status = $status;
        $this->catatan = $catatan;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('siswa.' . $this->siswa_id)];
    }

    public function broadcastAs(): string
    {
        return 'IzinDiverifikasi';
    }
}
