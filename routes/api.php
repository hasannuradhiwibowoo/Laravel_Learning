<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuruBkController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\WakaController;
use Illuminate\Support\Facades\Route;

// ---------- AUTH (publik) ----------
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ---------- Terautentikasi (Bearer Token) ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Siswa
    Route::middleware('role:siswa')->group(function () {
        Route::get('/siswa/dashboard', [SiswaController::class, 'dashboard']);
        Route::post('/siswa/izin', [SiswaController::class, 'ajukanIzin']);
        Route::get('/siswa/izin', [SiswaController::class, 'riwayatIzin']);
        Route::get('/siswa/statistik', [SiswaController::class, 'statistik']);
    });

    // Guru
    Route::middleware('role:guru')->group(function () {
        Route::get('/guru/jadwal', [GuruController::class, 'jadwal']);
        Route::post('/guru/presensi', [GuruController::class, 'presensi']);
        Route::post('/guru/jurnal', [GuruController::class, 'jurnal']);
    });

    // Guru BK
    Route::middleware('role:guru_bk')->group(function () {
        Route::get('/bk/izin-menunggu', [GuruBkController::class, 'listMenunggu']);
        Route::post('/bk/verifikasi', [GuruBkController::class, 'verifikasi']);
        Route::get('/bk/perlu-perhatian', [GuruBkController::class, 'perluPerhatian']);
        Route::get('/bk/tingkat-kehadiran', [GuruBkController::class, 'tingkatKehadiranSekolah']);
    });

    // Waka / Admin
    Route::middleware('role:waka')->group(function () {
        Route::post('/waka/putaran', [WakaController::class, 'setPutaran']);
        Route::get('/waka/monitoring', [WakaController::class, 'monitoring']);
        Route::post('/waka/trigger-wa', [WakaController::class, 'triggerWa']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });
});
