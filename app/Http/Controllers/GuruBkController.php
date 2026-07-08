<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Presensi;
use App\Services\KehadiranService;
use Illuminate\Http\Request;

class GuruBkController extends Controller
{
    public function __construct(protected KehadiranService $kehadiran) {}

    public function listMenunggu()
    {
        $izin = Izin::with('siswa.kelas')
            ->where('status', 'menunggu')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => [
                'izin_id' => $i->id,
                'nama_siswa' => $i->siswa?->nama,
                'kelas' => $i->siswa?->kelas?->nama,
                'tipe' => $i->tipe,
                'tanggal' => $i->tanggal_mulai.' s.d '.$i->tanggal_selesai,
                'keterangan' => $i->keterangan,
                'foto_surat' => $i->file_lampiran,
                'status' => $i->status,
            ]);

        return response()->json([
            'jumlah_menunggu_verifikasi' => $izin->count(),
            'detail_surat_izin' => $izin,
        ]);
    }

    public function verifikasi(Request $request)
    {
        $request->validate([
            'izin_id' => 'required|exists:izin,id',
            'aksi' => 'required|in:disetujui,ditolak',
            'catatan_penolakan' => 'required_if:aksi,ditolak|nullable|string',
        ]);

        $izin = Izin::findOrFail($request->izin_id);

        if ($request->aksi === 'ditolak') {
            $izin->update([
                'status' => 'ditolak',
                'catatan_penolakan' => $request->catatan_penolakan,
                'diverifikasi_oleh' => $request->user()->id,
            ]);
        } else {
            $izin->update([
                'status' => 'disetujui',
                'diverifikasi_oleh' => $request->user()->id,
            ]);
        }

        return response()->json(['message' => 'Izin '.$request->aksi.'.', 'izin' => $izin]);
    }

    public function perluPerhatian()
    {
        return response()->json([
            'siswa_perlu_perhatian' => $this->kehadiran->perluPerhatian(80),
        ]);
    }

    public function tingkatKehadiranSekolah()
    {
        $siswas = \App\Models\Siswa::all();
        if ($siswas->isEmpty()) {
            return response()->json(['tingkat_kehadiran_sekolah' => 0]);
        }

        $total = 0;
        foreach ($siswas as $s) {
            $total += $this->kehadiran->hitung($s)['persen_kehadiran'];
        }

        return response()->json([
            'tingkat_kehadiran_sekolah' => round($total / $siswas->count(), 2),
        ]);
    }
}
