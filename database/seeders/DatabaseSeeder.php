<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\JurnalKelas;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengaturan;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan
        Pengaturan::create(['putaran_aktif' => 'P1', 'tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil']);

        // Kelas
        $kX = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);
        $kXI = Kelas::create(['nama' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL']);
        $kXII = Kelas::create(['nama' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan' => 'RPL']);

        // Mapel
        $mapelWeb = MataPelajaran::create(['nama' => 'Pemrograman Web', 'kode' => 'PW']);
        $mapelBd = MataPelajaran::create(['nama' => 'Basis Data', 'kode' => 'BD']);
        $mapelMat = MataPelajaran::create(['nama' => 'Matematika', 'kode' => 'MTK']);

        $pw = Hash::make('password123');

        // Guru
        $uG1 = User::create(['name' => 'Bpk. Rafi Ahmad', 'nisn_nip' => 'G001', 'password' => $pw, 'role' => 'guru', 'aktif' => true]);
        $g1 = Guru::create(['user_id' => $uG1->id, 'nip' => 'G001', 'nama' => 'Bpk. Rafi Ahmad', 'jenis_kelamin' => 'L', 'no_hp' => '081234000001']);

        $uG2 = User::create(['name' => 'Ibu Siti', 'nisn_nip' => 'G002', 'password' => $pw, 'role' => 'guru', 'aktif' => true]);
        $g2 = Guru::create(['user_id' => $uG2->id, 'nip' => 'G002', 'nama' => 'Ibu Siti', 'jenis_kelamin' => 'P', 'no_hp' => '081234000002']);

        // Guru BK
        $uBk = User::create(['name' => 'Ibu Ani (BK)', 'nisn_nip' => 'BK001', 'password' => $pw, 'role' => 'guru_bk', 'aktif' => true]);
        Guru::create(['user_id' => $uBk->id, 'nip' => 'BK001', 'nama' => 'Ibu Ani', 'jenis_kelamin' => 'P', 'no_hp' => '081234000003']);

        // Waka
        User::create(['name' => 'Bpk. Waka', 'nisn_nip' => 'W001', 'password' => $pw, 'role' => 'waka', 'aktif' => true]);

        // Siswa per kelas
        $siswaList = [];
        $namaDepan = ['Budi', 'Sari', 'Andi', 'Rina', 'Dedi', 'Nita', 'Joko', 'Maya'];
        foreach ([$kX, $kXI, $kXII] as $idx => $kelas) {
            for ($i = 1; $i <= 5; $i++) {
                $n = ($idx * 5) + $i;
                $nisn = 'S'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
                $u = User::create([
                    'name' => $namaDepan[($n - 1) % count($namaDepan)].' '.['Santoso','Wijaya','Pratama','Lestari','Halim','Saputra','Permata','Gunawan'][($n - 1) % 8],
                    'nisn_nip' => $nisn,
                    'password' => $pw,
                    'role' => 'siswa',
                    'aktif' => true,
                ]);
                $siswa = Siswa::create([
                    'user_id' => $u->id,
                    'nis' => $nisn,
                    'nama' => $u->name,
                    'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
                    'kelas_id' => $kelas->id,
                    'no_hp' => '0812'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                ]);
                $siswaList[] = $siswa;
            }
        }

        // Jadwal P1 & P2
        $jadwalWebXI = Jadwal::create([
            'kelas_id' => $kXI->id, 'mata_pelajaran_id' => $mapelWeb->id, 'guru_id' => $g1->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:30:00', 'ruang' => 'R1', 'putaran' => 'P1',
        ]);
        Jadwal::create([
            'kelas_id' => $kXI->id, 'mata_pelajaran_id' => $mapelWeb->id, 'guru_id' => $g1->id,
            'hari' => 'Senin', 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:30:00', 'ruang' => 'R2', 'putaran' => 'P2',
        ]);
        Jadwal::create([
            'kelas_id' => $kX->id, 'mata_pelajaran_id' => $mapelBd->id, 'guru_id' => $g2->id,
            'hari' => 'Selasa', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00', 'ruang' => 'R3', 'putaran' => 'P1',
        ]);

        // Presensi untuk jadwal Web XI (siswa kelas XI)
        $siswaXI = Siswa::where('kelas_id', $kXI->id)->get();
        $statuses = ['H', 'H', 'H', 'I', 'S'];
        foreach ($siswaXI as $i => $s) {
            Presensi::create([
                'siswa_id' => $s->id,
                'jadwal_id' => $jadwalWebXI->id,
                'guru_id' => $g1->id,
                'tanggal' => now()->subDays(2)->toDateString(),
                'status' => $statuses[$i % count($statuses)],
                'terkunci' => false,
            ]);
        }

        // Izin menunggu untuk satu siswa
        Izin::create([
            'siswa_id' => $siswaXI->first()->id,
            'tipe' => 'izin',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(1)->toDateString(),
            'keterangan' => 'Acara keluarga',
            'status' => 'menunggu',
        ]);

        // Jurnal contoh
        JurnalKelas::create([
            'jadwal_id' => $jadwalWebXI->id,
            'guru_id' => $g1->id,
            'tanggal' => now()->subDays(2)->toDateString(),
            'pertemuan_ke' => 5,
            'nama_ruang' => 'R1',
            'materi' => 'Membuat CRUD dengan Laravel',
            'progress_kurikulum' => 60,
        ]);
    }
}
