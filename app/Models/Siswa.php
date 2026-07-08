<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = ['user_id', 'nis', 'nama', 'jenis_kelamin', 'kelas_id', 'no_hp'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function izins(): HasMany
    {
        return $this->hasMany(Izin::class);
    }
}
