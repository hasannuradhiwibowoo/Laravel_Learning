# Demo tes semua role SCP - jalanin: .\test_demo.ps1 (server harus nyala dulu)
$base = "http://127.0.0.1:8000/api"
$h = @{ Accept = "application/json"; "Content-Type" = "application/json" }

function T($n, $p) {
    (Invoke-RestMethod -Uri "$base/login" -Method Post `
        -Body (@{ nisn_nip = $n; password = $p } | ConvertTo-Json) `
        -ContentType "application/json" -Headers $h).token
}
function G($u, $t) {
    Invoke-RestMethod -Uri $u -Headers @{ Authorization = "Bearer $t"; Accept = "application/json" }
}

Write-Host "`n=== 1. SISWA (S0001) ===" -ForegroundColor Cyan
$t = G "$base/siswa/dashboard" (T "S0001" "password123")
"  nama=$($t.nama_siswa) | kelas=$($t.kelas) | persen=$($t.persen_kehadiran)%"

Write-Host "`n=== 2. GURU (G001) ===" -ForegroundColor Cyan
$tg = G "$base/guru/jadwal" (T "G001" "password123")
"  putaran=$($tg.putaran_aktif) | jumlah jadwal=$($tg.daftar_jadwal.Count)"

Write-Host "`n=== 3. GURU BK (BK001) ===" -ForegroundColor Cyan
$tb = G "$base/bk/izin-menunggu" (T "BK001" "password123")
"  izin menunggu=$($tb.jumlah_menunggu_verifikasi)"

Write-Host "`n=== 4. WAKA (W001) ===" -ForegroundColor Cyan
$tw = G "$base/waka/monitoring" (T "W001" "password123")
"  putaran=$($tw.putaran_aktif) | kelas belum presensi=$($tw.kelas_belum_presensi.Count)"

Write-Host "`nSELESAI. Server tetap jalan di http://127.0.0.1:8000`n" -ForegroundColor Green
