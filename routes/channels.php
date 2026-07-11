<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Notifikasi untuk Guru BK / Waka
Broadcast::channel('bk.notifications', function ($user) {
    return in_array($user->role, ['guru_bk', 'waka']);
});

// Update status izin untuk siswa terkait
Broadcast::channel('siswa.{id}', function ($user, $id) {
    return $user->siswa && (int) $user->siswa->id === (int) $id;
});
