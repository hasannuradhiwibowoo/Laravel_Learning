<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;

class KehadiranService
{
    public function hitung(Siswa $siswa): array
    {
        $presensis = Presensi::where('siswa_id', $siswa->id)->get();

        return $this->compute($presensis);
    }

    public function hitungBulk(Collection $siswas): Collection
    {
        if ($siswas->isEmpty()) {
            return collect();
        }

        $grouped = Presensi::whereIn('siswa_id', $siswas->pluck('id')->all())
            ->get()
            ->groupBy('siswa_id');

        return $siswas->mapWithKeys(function (Siswa $siswa) use ($grouped) {
            return [$siswa->id => $this->compute($grouped->get($siswa->id, collect()))];
        });
    }

    public function perluPerhatian(float $threshold = 80): array
    {
        $siswas = Siswa::with('kelas')->get();
        $stats = $this->hitungBulk($siswas);

        $hasil = [];
        foreach ($siswas as $siswa) {
            $stat = $stats->get($siswa->id);
            if ($stat && $stat['persen_kehadiran'] < $threshold) {
                $hasil[] = [
                    'siswa_id' => $siswa->id,
                    'nama_siswa' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'kelas' => $siswa->kelas?->nama,
                    'persen_kehadiran' => $stat['persen_kehadiran'],
                    'alpa' => $stat['alpa'],
                ];
            }
        }

        return $hasil;
    }

    private function compute(Collection $presensis): array
    {
        $total = $presensis->count();
        $h = $presensis->where('status', 'H')->count();
        $i = $presensis->where('status', 'I')->count();
        $s = $presensis->where('status', 'S')->count();
        $a = $presensis->where('status', 'A')->count();

        $efektif = $h + $i + $s;
        $persen = $total > 0 ? round(($efektif / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'hadir' => $h,
            'izin' => $i,
            'sakit' => $s,
            'alpa' => $a,
            'persen_kehadiran' => $persen,
        ];
    }
}
