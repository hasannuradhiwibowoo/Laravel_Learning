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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $pw = 'password123';

        // Pengaturan
        Pengaturan::firstOrCreate(
            ['putaran_aktif' => 'P1', 'tahun_ajaran' => '2025/2026'],
            ['semester' => 'Ganjil']
        );

        // Kelas
        $kX = Kelas::firstOrCreate(['nama' => 'X RPL 1'], ['tingkat' => 'X', 'jurusan' => 'RPL']);
        $kXI = Kelas::firstOrCreate(['nama' => 'XI RPL 1'], ['tingkat' => 'XI', 'jurusan' => 'RPL']);
        $kXII = Kelas::firstOrCreate(['nama' => 'XII RPL 1'], ['tingkat' => 'XII', 'jurusan' => 'RPL']);

        // Mapel
        $mapelWeb = MataPelajaran::firstOrCreate(['kode' => 'PW'], ['nama' => 'Pemrograman Web']);
        $mapelBd = MataPelajaran::firstOrCreate(['kode' => 'BD'], ['nama' => 'Basis Data']);
        $mapelMat = MataPelajaran::firstOrCreate(['kode' => 'MTK'], ['nama' => 'Matematika']);

        // Guru
        $uG1 = User::updateOrCreate(
            ['nisn_nip' => 'G001'],
            ['name' => 'Bpk. Rafi Ahmad', 'password' => $pw, 'role' => 'guru', 'aktif' => true]
        );
        Guru::updateOrCreate(
            ['user_id' => $uG1->id],
            ['nip' => 'G001', 'nama' => 'Bpk. Rafi Ahmad', 'jenis_kelamin' => 'L', 'no_hp' => '081234000001']
        );

        $uG2 = User::updateOrCreate(
            ['nisn_nip' => 'G002'],
            ['name' => 'Ibu Siti', 'password' => $pw, 'role' => 'guru', 'aktif' => true]
        );
        Guru::updateOrCreate(
            ['user_id' => $uG2->id],
            ['nip' => 'G002', 'nama' => 'Ibu Siti', 'jenis_kelamin' => 'P', 'no_hp' => '081234000002']
        );

        // Guru BK
        $uBk = User::updateOrCreate(
            ['nisn_nip' => 'BK001'],
            ['name' => 'Ibu Ani (BK)', 'password' => $pw, 'role' => 'guru_bk', 'aktif' => true]
        );
        Guru::updateOrCreate(
            ['user_id' => $uBk->id],
            ['nip' => 'BK001', 'nama' => 'Ibu Ani', 'jenis_kelamin' => 'P', 'no_hp' => '081234000003']
        );

        // Waka pengelola utama (dummy dari .env, fallback ke credential default)
        User::updateOrCreate(
            ['nisn_nip' => env('WAKA_NISN', '197311152002122005')],
            [
                'name' => env('WAKA_NAMA', 'Tatik Wardayati, S.Pd'),
                'password' => env('WAKA_PASSWORD', '197311152002122005'),
                'role' => 'waka',
                'aktif' => true,
            ]
        );

        // Siswa per kelas
        $namaDepan = ['Budi', 'Sari', 'Andi', 'Rina', 'Dedi', 'Nita', 'Joko', 'Maya'];
        $namaBelakang = ['Santoso', 'Wijaya', 'Pratama', 'Lestari', 'Halim', 'Saputra', 'Permata', 'Gunawan'];
        foreach ([$kX, $kXI, $kXII] as $idx => $kelas) {
            for ($i = 1; $i <= 5; $i++) {
                $n = ($idx * 5) + $i;
                $nisn = 'S'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
                $user = User::updateOrCreate(
                    ['nisn_nip' => $nisn],
                    [
                        'name' => $namaDepan[($n - 1) % count($namaDepan)].' '.$namaBelakang[($n - 1) % count($namaBelakang)],
                        'password' => $pw,
                        'role' => 'siswa',
                        'aktif' => true,
                    ]
                );
                Siswa::updateOrCreate(
                    ['nis' => $nisn],
                    [
                        'user_id' => $user->id,
                        'nama' => $user->name,
                        'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
                        'kelas_id' => $kelas->id,
                        'no_hp' => '0812'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                    ]
                );
            }
        }

        // Jadwal P1 & P2
        $jadwalWebXI = Jadwal::updateOrCreate(
            [
                'kelas_id' => $kXI->id,
                'mata_pelajaran_id' => $mapelWeb->id,
                'guru_id' => $uG1->id,
                'hari' => 'Senin',
                'jam_mulai' => '07:00:00',
                'putaran' => 'P1',
            ],
            ['jam_selesai' => '08:30:00', 'ruang' => 'R1']
        );
        Jadwal::updateOrCreate(
            [
                'kelas_id' => $kXI->id,
                'mata_pelajaran_id' => $mapelWeb->id,
                'guru_id' => $uG1->id,
                'hari' => 'Senin',
                'jam_mulai' => '07:00:00',
                'putaran' => 'P2',
            ],
            ['jam_selesai' => '08:30:00', 'ruang' => 'R2']
        );
        Jadwal::updateOrCreate(
            [
                'kelas_id' => $kX->id,
                'mata_pelajaran_id' => $mapelBd->id,
                'guru_id' => $uG2->id,
                'hari' => 'Selasa',
                'jam_mulai' => '09:00:00',
                'putaran' => 'P1',
            ],
            ['jam_selesai' => '10:30:00', 'ruang' => 'R3']
        );

        $hariIni = now()->locale('id')->dayName;
        Jadwal::updateOrCreate(
            [
                'kelas_id' => $kX->id,
                'mata_pelajaran_id' => $mapelBd->id,
                'guru_id' => $uG2->id,
                'hari' => $hariIni,
                'jam_mulai' => '08:00:00',
                'putaran' => 'P1',
            ],
            ['jam_selesai' => '09:30:00', 'ruang' => 'R4']
        );

        $siswaContoh = Siswa::where('kelas_id', $kXI->id)->first();
        if ($siswaContoh) {
            Izin::updateOrCreate(
                ['siswa_id' => $siswaContoh->id, 'status' => 'menunggu', 'tanggal_mulai' => now()->toDateString()],
                ['tipe' => 'izin', 'tanggal_selesai' => now()->addDays(1)->toDateString(), 'keterangan' => 'Acara keluarga']
            );
        }

        if (JurnalKelas::count() === 0) {
            $siswaXI = Siswa::where('kelas_id', $kXI->id)->get();
            $statuses = ['H', 'H', 'H', 'I', 'S'];
            foreach ($siswaXI as $i => $s) {
                Presensi::create([
                    'siswa_id' => $s->id,
                    'jadwal_id' => $jadwalWebXI->id,
                    'guru_id' => $uG1->id,
                    'tanggal' => now()->subDays(2)->toDateString(),
                    'status' => $statuses[$i % count($statuses)],
                    'terkunci' => false,
                ]);
            }

            if ($siswaXI->isNotEmpty()) {
                Izin::create([
                    'siswa_id' => $siswaXI->first()->id,
                    'tipe' => 'izin',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_selesai' => now()->addDays(1)->toDateString(),
                    'keterangan' => 'Acara keluarga',
                    'status' => 'menunggu',
                ]);
            }

            JurnalKelas::create([
                'jadwal_id' => $jadwalWebXI->id,
                'guru_id' => $uG1->id,
                'tanggal' => now()->subDays(2)->toDateString(),
                'pertemuan_ke' => 5,
                'nama_ruang' => 'R1',
                'materi' => 'Membuat CRUD dengan Laravel',
                'progress_kurikulum' => 60,
            ]);
        }
    }
}
