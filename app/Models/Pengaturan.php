<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['putaran_aktif', 'tahun_ajaran', 'semester'];

    public static function aktif(): ?self
    {
        return self::orderBy('id', 'desc')->first();
    }
}
