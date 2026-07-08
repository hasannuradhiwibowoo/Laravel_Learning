<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = ['user_id', 'nip', 'nama', 'jenis_kelamin', 'no_hp'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(JurnalKelas::class);
    }
}
