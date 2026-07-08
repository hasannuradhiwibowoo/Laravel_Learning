<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('jadwal_id')->constrained('jadwal')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['H', 'I', 'S', 'A'])->default('H'); // Hadir, Izin, Sakit, Alpa
            $table->boolean('terkunci')->default(false)->comment('true kalau izin sudah diverifikasi BK');
            $table->text('keterangan')->nullable();
            $table->unique(['siswa_id', 'jadwal_id', 'tanggal']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
