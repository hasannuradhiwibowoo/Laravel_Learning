<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    protected $table = 'jadwal';

    protected $fillable = [
        'kelas_id', 'mata_pelajaran_id', 'guru_id',
        'hari', 'jam_mulai', 'jam_selesai', 'ruang', 'putaran',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(JurnalKelas::class);
    }
}
