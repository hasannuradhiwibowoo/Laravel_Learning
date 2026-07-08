<?php

namespace App\Http\Controllers;

use App\Models\JurnalKelas;
use App\Models\Pengaturan;
use App\Services\ImageService;
use App\Services\JadwalService;
use App\Services\PresensiService;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function __construct(
        protected JadwalService $jadwalService,
        protected PresensiService $presensiService,
        protected ImageService $imageService,
    ) {}

    public function jadwal(Request $request)
    {
        $putaran = Pengaturan::aktif()?->putaran_aktif ?? 'P1';
        $guru = $request->user()->guru;

        $daftarJadwal = $this->jadwalService->untukGuru($guru->id, $putaran)
            ->map(fn ($j) => [
                'jadwal_id' => $j->id,
                'hari' => $j->hari,
                'jam_mulai' => $j->jam_mulai->format('H:i'),
                'jam_selesai' => $j->jam_selesai->format('H:i'),
                'mapel' => $j->mataPelajaran?->nama,
                'kelas' => $j->kelas?->nama,
                'ruang' => $j->ruang,
            ]);

        return response()->json([
            'nama_guru' => $guru->nama,
            'putaran_aktif' => $putaran,
            'daftar_jadwal' => $daftarJadwal,
        ]);
    }

    public function presensi(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jadwal_id' => 'required|exists:jadwal,id',
            'siswa' => 'required|array',
            'siswa.*.siswa_id' => 'required|exists:siswa,id',
            'siswa.*.status' => 'required|in:H,I,S,A',
            'siswa.*.keterangan' => 'nullable|string',
        ]);

        $guru = $request->user()->guru;
        $hasil = [];

        foreach ($request->siswa as $item) {
            // Jika sudah terkunci (izin diverifikasi BK), lewati agar tidak tertimpa guru
            if ($this->presensiService->isTerkunci($item['siswa_id'], $request->tanggal)) {
                $hasil[] = ['siswa_id' => $item['siswa_id'], 'terkunci' => true, 'skipped' => true];
                continue;
            }

            $presensi = $this->presensiService->simpan([
                'siswa_id' => $item['siswa_id'],
                'jadwal_id' => $request->jadwal_id,
                'guru_id' => $guru->id,
                'tanggal' => $request->tanggal,
                'status' => $item['status'],
                'keterangan' => $item['keterangan'] ?? null,
            ]);

            $hasil[] = ['siswa_id' => $item['siswa_id'], 'status' => $presensi->status, 'terkunci' => $presensi->terkunci];
        }

        return response()->json(['message' => 'Presensi tersimpan.', 'hasil' => $hasil]);
    }

    public function jurnal(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'tanggal' => 'required|date',
            'nama_ruang' => 'nullable|string',
            'materi' => 'nullable|string',
            'progress_kurikulum' => 'required|integer|min:0|max:100',
            'foto_kelas' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $guru = $request->user()->guru;

        $fotoPath = null;
        if ($request->hasFile('foto_kelas')) {
            $fotoPath = $request->file('foto_kelas')->store('jurnal', 'public');
            // kompres otomatis agar <= 300KB
            $this->imageService->kompres(storage_path('app/public/'.$fotoPath), 300);
        }

        $jurnal = JurnalKelas::updateOrCreate(
            [
                'jadwal_id' => $request->jadwal_id,
                'guru_id' => $guru->id,
                'tanggal' => $request->tanggal,
            ],
            [
                'nama_ruang' => $request->nama_ruang,
                'materi' => $request->materi,
                'progress_kurikulum' => $request->progress_kurikulum,
                'foto' => $fotoPath,
            ]
        );

        return response()->json(['message' => 'Jurnal tersimpan.', 'jurnal' => $jurnal], 201);
    }
}
