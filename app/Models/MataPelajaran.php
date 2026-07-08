<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $fillable = ['nama', 'kode'];

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
