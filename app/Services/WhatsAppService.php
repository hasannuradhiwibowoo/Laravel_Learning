<?php

namespace App\Services;

use App\Models\LogWhatsapp;
use Illuminate\Database\Eloquent\Model;

class WhatsAppService
{
    public function kirim(
        string $tujuan,
        string $pesan,
        ?string $namaTujuan = null,
        ?Model $related = null
    ): LogWhatsapp {
        $log = LogWhatsapp::create([
            'tujuan' => $tujuan,
            'nama_tujuan' => $namaTujuan,
            'pesan' => $pesan,
            'status' => 'terkirim', // STUB: anggap berhasil
            'respon_api' => 'STUB: belum terhubung ke gateway Fonnte/Wablas',
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related ? $related->id : null,
        ]);

        return $log;
    }
}
