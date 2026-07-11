<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\JurnalKelas;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Services\ImageService;
use App\Services\JadwalService;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruController extends Controller
{
    public function __construct(
        protected JadwalService $jadwalService,
        protected PresensiService $presensiService,
        protected ImageService $imageService,
    ) {}

    public function jadwal(Request $request)
    {
        $putaran = $request->input('putaran') ?? (Pengaturan::aktif()?->putaran_aktif ?? 'P1');
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

    public function siswaKelas(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'tanggal' => 'nullable|date',
        ]);

        $tanggal = $request->input('tanggal', now()->toDateString());
        $jadwal = Jadwal::findOrFail($request->jadwal_id);

        $daftar = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama')
            ->get()
            ->map(function ($s) use ($jadwal, $tanggal) {
                $presensi = Presensi::where('siswa_id', $s->id)
                    ->where('jadwal_id', $jadwal->id)
                    ->where('tanggal', $tanggal)
                    ->first();

                $status = 'H';
                $keterangan = null;
                $terkunci = false;

                if ($presensi) {
                    $status = $presensi->status;
                    $keterangan = $presensi->keterangan;
                    $terkunci = (bool) $presensi->terkunci;
                } else {
                    $izin = Izin::where('siswa_id', $s->id)
                        ->where('status', 'disetujui')
                        ->where('tanggal_mulai', '<=', $tanggal)
                        ->where('tanggal_selesai', '>=', $tanggal)
                        ->first();
                    if ($izin) {
                        $status = $izin->tipe === 'sakit' ? 'S' : 'I';
                        $keterangan = 'Izin disetujui BK';
                        $terkunci = true;
                    }
                }

                return [
                    'siswa_id' => $s->id,
                    'nis' => $s->nis,
                    'nama' => $s->nama,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'terkunci' => $terkunci,
                ];
            });

        return response()->json([
            'jadwal_id' => $jadwal->id,
            'mapel' => $jadwal->mataPelajaran?->nama,
            'kelas' => $jadwal->kelas?->nama,
            'ruang' => $jadwal->ruang,
            'tanggal' => $tanggal,
            'siswa' => $daftar,
        ]);
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
                'foto' => $fotoPath ? Storage::disk('public')->url($fotoPath) : null,
            ]
        );

        return response()->json(['message' => 'Jurnal tersimpan.', 'jurnal' => $jurnal], 201);
    }

    public function lihatJurnal(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id',
            'tanggal' => 'nullable|date',
        ]);

        $tanggal = $request->input('tanggal', now()->toDateString());
        $jurnal = JurnalKelas::where('jadwal_id', $request->jadwal_id)
            ->where('guru_id', $request->user()->guru->id)
            ->where('tanggal', $tanggal)
            ->first();

        return response()->json(['jurnal' => $jurnal]);
    }
}
