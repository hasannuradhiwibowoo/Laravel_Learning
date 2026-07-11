<?php

namespace App\Services;

use App\Models\Izin;
use App\Models\Presensi;

class PresensiService
{
    public function simpan(array $data): Presensi
    {
        $izin = Izin::where('siswa_id', $data['siswa_id'])
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $data['tanggal'])
            ->where('tanggal_selesai', '>=', $data['tanggal'])
            ->first();

        if ($izin) {
            $data['terkunci'] = true;
            $data['status'] = $izin->tipe === 'sakit' ? 'S' : 'I';
            $data['keterangan'] = $data['keterangan'] ?? 'Otomatis dari izin yang diverifikasi BK';
        }

        return Presensi::updateOrCreate(
            [
                'siswa_id' => $data['siswa_id'],
                'jadwal_id' => $data['jadwal_id'],
                'tanggal' => $data['tanggal'],
            ],
            $data
        );
    }

    public function isTerkunci(int $siswaId, string $tanggal): bool
    {
        return Presensi::where('siswa_id', $siswaId)
            ->where('tanggal', $tanggal)
            ->where('terkunci', true)
            ->exists();
    }
}
