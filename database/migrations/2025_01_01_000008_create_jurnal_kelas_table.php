<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwal')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->date('tanggal');
            $table->integer('pertemuan_ke')->nullable();
            $table->string('nama_ruang')->nullable();
            $table->text('materi')->nullable();
            $table->integer('progress_kurikulum')->default(0)->comment('0-100 dari slider');
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_kelas');
    }
};
