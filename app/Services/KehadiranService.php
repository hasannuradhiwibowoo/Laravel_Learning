<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Siswa;

class KehadiranService
{
    /**
     * Hitung statistik kehadiran seorang siswa.
     * persen_kehadiran = (H + I + S) / total * 100  (Alpa tidak dihitung hadir).
     */
    public function hitung(Siswa $siswa): array
    {
        $presensis = Presensi::where('siswa_id', $siswa->id)->get();

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

    /**
     * Ambil daftar siswa dengan persentase kehadiran di bawah threshold.
     */
    public function perluPerhatian(float $threshold = 80): array
    {
        $hasil = [];
        foreach (Siswa::with('kelas')->get() as $siswa) {
            $stat = $this->hitung($siswa);
            if ($stat['persen_kehadiran'] < $threshold) {
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
}
