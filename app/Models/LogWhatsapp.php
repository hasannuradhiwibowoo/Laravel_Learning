<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogWhatsapp extends Model
{
    protected $table = 'log_whatsapp';

    protected $fillable = [
        'tujuan', 'nama_tujuan', 'pesan', 'status', 'respon_api', 'related_type', 'related_id',
    ];
}
