<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'siswa_id', 'jadwal_id', 'guru_id', 'tanggal', 'status', 'terkunci', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'terkunci' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
