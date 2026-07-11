<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\MonitoringService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
                    'jadwal_id' => $j->id,
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

    public function listUser(Request $request)
    {
        $users = User::with(['siswa.kelas', 'guru'])
            ->where('role', '!=', 'waka')
            ->orderBy('role')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'nama' => $u->name,
                'nisn_nip' => $u->nisn_nip,
                'role' => $u->role,
                'aktif' => (bool) $u->aktif,
                'kelas' => $u->siswa?->kelas?->nama,
            ]);

        return response()->json(['users' => $users]);
    }

    public function listKelas()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama')
            ->get(['id', 'nama', 'tingkat', 'jurusan']);

        return response()->json(['kelas' => $kelas]);
    }

    public function tambahUser(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|in:siswa,guru,guru_bk',
            'nama' => 'required|string|max:255',
            'nisn_nip' => 'required|string|unique:users,nisn_nip',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp' => 'nullable|string',
            'kelas_id' => 'required_if:role,siswa|exists:kelas,id',
            'password' => 'nullable|string|min:4',
        ]);

        $password = $data['password'] ?? $data['nisn_nip'];

        $user = User::create([
            'name' => $data['nama'],
            'nisn_nip' => $data['nisn_nip'],
            'password' => Hash::make($password),
            'role' => $data['role'],
            'aktif' => true,
        ]);

        if ($data['role'] === 'siswa') {
            $user->siswa()->create([
                'nis' => $data['nisn_nip'],
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'kelas_id' => $data['kelas_id'],
                'no_hp' => $data['no_hp'] ?? null,
            ]);
        } else {
            $user->guru()->create([
                'nip' => $data['nisn_nip'],
                'nama' => $data['nama'],
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'user' => $user->load(['siswa.kelas', 'guru']),
        ], 201);
    }
}
