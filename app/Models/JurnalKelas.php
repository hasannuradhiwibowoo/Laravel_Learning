<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalKelas extends Model
{
    protected $table = 'jurnal_kelas';

    protected $fillable = [
        'jadwal_id', 'guru_id', 'tanggal', 'pertemuan_ke',
        'nama_ruang', 'materi', 'progress_kurikulum', 'foto',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'progress_kurikulum' => 'integer',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }
}
