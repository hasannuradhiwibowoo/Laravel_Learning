<?php

namespace App\Services;

use App\Models\LogWhatsapp;
use Illuminate\Database\Eloquent\Model;

class WhatsAppService
{
    /**
     * STUB pengiriman WhatsApp.
     * Menyimpan ke tabel log_whatsapp tanpa benar-benar mengirim.
     * Saat API key Fonnte/Wablas ready, ganti bagian TODO dengan HTTP call.
     */
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

        // TODO: integrasi nyata saat API key ready
        // $res = \Illuminate\Support\Facades\Http::withToken(config('services.fonnte.token'))
        //     ->post('https://api.fonnte.com/send', ['target' => $tujuan, 'message' => $pesan]);
        // $log->update(['status' => $res->successful() ? 'terkirim' : 'gagal', 'respon_api' => $res->body()]);

        return $log;
    }
}
