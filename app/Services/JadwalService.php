<?php

namespace App\Services;

use App\Models\Jadwal;
use Illuminate\Support\Collection;

class JadwalService
{
    /**
     * Jadwal mengajar seorang guru pada putaran tertentu (P1/P2).
     */
    public function untukGuru(int $guruId, string $putaran): Collection
    {
        return Jadwal::with(['kelas', 'mataPelajaran'])
            ->where('guru_id', $guruId)
            ->where('putaran', $putaran)
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Jadwal pada hari & putaran tertentu (untuk monitoring waka).
     */
    public function untukHari(string $hari, string $putaran): Collection
    {
        return Jadwal::with(['kelas', 'guru', 'mataPelajaran'])
            ->where('hari', $hari)
            ->where('putaran', $putaran)
            ->orderBy('jam_mulai')
            ->get();
    }
}
