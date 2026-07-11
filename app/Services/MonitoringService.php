<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\JurnalKelas;
use App\Models\Presensi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MonitoringService
{
    public function kelasBelumPresensi(string $putaran, string $tanggal, int $menitToleransi = 15): Collection
    {
        $hariIni = Carbon::parse($tanggal)->locale('id')->dayName;
        $sekarang = Carbon::now();

        return Jadwal::with(['kelas', 'guru', 'mataPelajaran'])
            ->where('putaran', $putaran)
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get()
            ->filter(function (Jadwal $j) use ($tanggal, $menitToleransi, $sekarang) {
                // baru dihitung terlambat setelah lewat toleransi sejak jam mulai
                $batas = Carbon::parse($j->jam_mulai)->addMinutes($menitToleransi);
                if ($sekarang->lessThan($batas)) {
                    return false;
                }

                $adaPresensi = Presensi::where('jadwal_id', $j->id)
                    ->where('tanggal', $tanggal)
                    ->exists();

                $adaJurnal = JurnalKelas::where('jadwal_id', $j->id)
                    ->where('guru_id', $j->guru_id)
                    ->where('tanggal', $tanggal)
                    ->exists();

                return ! $adaPresensi || ! $adaJurnal;
            })
            ->values();
    }
}
