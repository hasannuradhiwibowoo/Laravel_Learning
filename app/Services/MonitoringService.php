<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Presensi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MonitoringService
{
    /**
     * Daftar kelas/jadwal yang BELUM dilakukan presensi pada tanggal & putaran tertentu.
     */
    public function kelasBelumPresensi(string $putaran, string $tanggal): Collection
    {
        $hariIni = Carbon::parse($tanggal)->locale('id')->dayName;

        $jadwals = Jadwal::with(['kelas', 'guru', 'mataPelajaran'])
            ->where('putaran', $putaran)
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get();

        return $jadwals->filter(function (Jadwal $j) use ($tanggal) {
            return ! Presensi::where('jadwal_id', $j->id)
                ->where('tanggal', $tanggal)
                ->exists();
        })->values();
    }

    /**
     * Cek jadwal yang sudah lewat jam_mulai + toleransi tapi belum presensi
     * (kandidat pengiriman WA otomatis ke guru).
     */
    public function lewatToleransi(string $putaran, string $tanggal, int $menitToleransi = 15): Collection
    {
        $sekarang = Carbon::now();

        return $this->kelasBelumPresensi($putaran, $tanggal)
            ->filter(function (Jadwal $j) use ($sekarang, $menitToleransi) {
                $batas = Carbon::parse($j->jam_mulai)->addMinutes($menitToleransi);
                return $sekarang->greaterThanOrEqualTo($batas);
            })->values();
    }
}
