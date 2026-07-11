<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IzinMenungguUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jumlah;
    public $nama_siswa;

    public function __construct($jumlah, $nama_siswa)
    {
        $this->jumlah = $jumlah;
        $this->nama_siswa = $nama_siswa;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('bk.notifications')];
    }

    public function broadcastAs(): string
    {
        return 'IzinMenungguUpdated';
    }
}
