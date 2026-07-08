<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->enum('tipe', ['izin', 'sakit']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('keterangan')->nullable();
            $table->string('file_lampiran')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('catatan_penolakan')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin');
    }
};
