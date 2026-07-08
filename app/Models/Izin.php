<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = [
        'siswa_id', 'tipe', 'tanggal_mulai', 'tanggal_selesai',
        'keterangan', 'file_lampiran', 'status', 'catatan_penolakan', 'diverifikasi_oleh',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
