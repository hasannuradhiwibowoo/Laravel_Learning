<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Pengaturan;
use App\Services\MonitoringService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WakaController extends Controller
{
    public function __construct(
        protected MonitoringService $monitoring,
        protected WhatsAppService $wa,
    ) {}

    public function setPutaran(Request $request)
    {
        $request->validate([
            'putaran_aktif' => 'required|in:P1,P2',
        ]);

        $pengaturan = Pengaturan::aktif();
        if (! $pengaturan) {
            $pengaturan = Pengaturan::create(['putaran_aktif' => $request->putaran_aktif]);
        } else {
            $pengaturan->update(['putaran_aktif' => $request->putaran_aktif]);
        }

        return response()->json([
            'message' => 'Putaran aktif diubah.',
            'putaran_aktif' => $pengaturan->putaran_aktif,
        ]);
    }

    public function monitoring(Request $request)
    {
        $putaran = Pengaturan::aktif()?->putaran_aktif ?? 'P1';
        $tanggal = $request->input('tanggal', now()->toDateString());

        $belum = $this->monitoring->kelasBelumPresensi($putaran, $tanggal)
            ->map(fn ($j) => [
                'jam' => $j->jam_mulai->format('H:i').'-'.$j->jam_selesai->format('H:i'),
                'kelas' => $j->kelas?->nama,
                'mapel' => $j->mataPelajaran?->nama,
                'nama_guru' => $j->guru?->nama,
                'ruang' => $j->ruang,
                'status' => 'belum presensi',
            ]);

        return response()->json([
            'putaran_aktif' => $putaran,
            'tanggal' => $tanggal,
            'kelas_belum_presensi' => $belum,
        ]);
    }

    public function triggerWa(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'tanggal' => 'required|date',
        ]);

        $jadwal = Jadwal::with(['guru', 'kelas', 'mataPelajaran'])->findOrFail($request->jadwal_id);

        if (! $jadwal->guru?->no_hp) {
            return response()->json([
                'status_kirim_wa' => 'Gagal: guru tidak punya nomor HP.',
            ], 422);
        }

        $pesan = "Pengingat: Presensi kelas {$jadwal->kelas?->nama} mapel {$jadwal->mataPelajaran?->nama} "
            ."di ruang {$jadwal->ruang} tanggal {$request->tanggal} belum diisi. Mohon segera input.";

        $log = $this->wa->kirim(
            tujuan: $jadwal->guru->no_hp,
            pesan: $pesan,
            namaTujuan: $jadwal->guru->nama,
            related: $jadwal
        );

        return response()->json([
            'status_kirim_wa' => 'Pesan WA (stub) tercatat di log. ID: '.$log->id,
            'log' => $log,
        ]);
    }
}
