<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->string('tujuan'); // nomor hp tujuan
            $table->string('nama_tujuan')->nullable();
            $table->text('pesan');
            $table->enum('status', ['antri', 'terkirim', 'gagal'])->default('antri');
            $table->text('respon_api')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_whatsapp');
    }
};
