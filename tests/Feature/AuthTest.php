<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'nisn_nip' => fake()->unique()->numerify('############'),
            'password' => 'password',
            'role' => 'siswa',
            'aktif' => true,
        ], $attrs));
    }

    public function test_login_sukses_mengembalikan_token(): void
    {
        $this->makeUser(['role' => 'waka', 'nisn_nip' => '123456789012']);

        $this->postJson('/api/login', [
            'nisn_nip' => '123456789012',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['token', 'role', 'user']);
    }

    public function test_login_gagal_password_salah(): void
    {
        $this->makeUser(['nisn_nip' => '123456789012']);

        $this->postJson('/api/login', [
            'nisn_nip' => '123456789012',
            'password' => 'salah',
        ])->assertStatus(422);
    }

    public function test_akses_api_tanpa_token_401(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_siswa_tidak_boleh_akses_route_waka_403(): void
    {
        $siswa = $this->makeUser(['role' => 'siswa', 'nisn_nip' => 'siswa001']);
        $token = $siswa->createToken('auth-token', ['siswa'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/waka/users')
            ->assertStatus(403);
    }

    public function test_waka_dapat_menambah_user_siswa(): void
    {
        $waka = $this->makeUser(['role' => 'waka', 'nisn_nip' => 'waka001']);
        $token = $waka->createToken('auth-token', ['waka'])->plainTextToken;
        $kelas = Kelas::create(['nama' => 'X RPL 1', 'tingkat' => 'X', 'jurusan' => 'RPL']);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/waka/users', [
                'role' => 'siswa',
                'nama' => 'Budi Santoso',
                'nisn_nip' => 'siswa999',
                'kelas_id' => $kelas->id,
            ])
            ->assertCreated()
            ->assertJsonPath('user.role', 'siswa');

        $this->assertDatabaseHas('siswa', ['nis' => 'siswa999', 'nama' => 'Budi Santoso']);
    }
}
