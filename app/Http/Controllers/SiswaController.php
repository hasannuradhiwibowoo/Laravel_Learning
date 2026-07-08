<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\JurnalKelas;
use App\Models\Presensi;
use App\Services\KehadiranService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SiswaController extends Controller
{
    public function __construct(protected KehadiranService $kehadiran) {}

    public function dashboard(Request $request)
    {
        $siswa = $request->user()->siswa;
        $stat = $this->kehadiran->hitung($siswa);

        $mapels = Jadwal::with('mataPelajaran')
            ->where('kelas_id', $siswa->kelas_id)
            ->get()
            ->unique('mata_pelajaran_id');

        $progressKurikulum = [];
        foreach ($mapels as $j) {
            $latest = JurnalKelas::where('jadwal_id', $j->id)
                ->orderByDesc('tanggal')
                ->first();
            $progressKurikulum[] = [
                'nama_mapel' => $j->mataPelajaran?->nama,
                'persen_progress' => $latest?->progress_kurikulum ?? 0,
            ];
        }

        return response()->json([
            'nama_siswa' => $siswa->nama,
            'kelas' => $siswa->kelas?->nama,
            'persen_kehadiran' => $stat['persen_kehadiran'],
            'progress_kurikulum' => $progressKurikulum,
        ]);
    }

    public function ajukanIzin(Request $request)
    {
        $request->validate([
            'tipe' => 'required|in:izin,sakit',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string',
            'file_lampiran' => 'required|file|mimes:jpg,jpeg,pdf|max:2048',
        ]);

        $siswa = $request->user()->siswa;

        $path = $request->file('file_lampiran')
            ->store('izin', 'public');

        $izin = Izin::create([
            'siswa_id' => $siswa->id,
            'tipe' => $request->tipe,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'file_lampiran' => $path,
            'status' => 'menunggu',
        ]);

        return response()->json([
            'message' => 'Pengajuan izin berhasil dikirim.',
            'izin' => $izin,
        ], 201);
    }

    public function riwayatIzin(Request $request)
    {
        $siswa = $request->user()->siswa;

        $daftarIzin = Izin::where('siswa_id', $siswa->id)
            ->orderByDesc('created_at')
            ->get(['tanggal_mulai', 'tanggal_selesai', 'tipe', 'keterangan', 'status'])
            ->map(fn ($i) => [
                'tanggal' => $i->tanggal_mulai.' s.d '.$i->tanggal_selesai,
                'tipe' => $i->tipe,
                'keterangan' => $i->keterangan,
                'status' => $i->status,
            ]);

        return response()->json(['daftar_izin' => $daftarIzin]);
    }

    public function statistik(Request $request)
    {
        $siswa = $request->user()->siswa;
        $stat = $this->kehadiran->hitung($siswa);

        $tren = Presensi::where('siswa_id', $siswa->id)
            ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan, SUM(status="H") as hadir')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(function ($row) {
                $bulan = Carbon::createFromFormat('Y-m', $row->bulan)->locale('id')->monthName;

                return ['bulan' => $bulan, 'jumlah' => (int) $row->hadir];
            });

        return response()->json([
            'total_hadir' => $stat['hadir'],
            'total_izin' => $stat['izin'],
            'total_sakit' => $stat['sakit'],
            'total_alpa' => $stat['alpa'],
            'tren_bulanan' => $tren,
        ]);
    }
}
