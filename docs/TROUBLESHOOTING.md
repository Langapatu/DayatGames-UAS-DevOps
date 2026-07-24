# Troubleshooting DayatGames

## Port 3306, 8080, atau 8081 dipakai

Periksa proses/container pemilik port terlebih dahulu. Target tugas mempertahankan `3306:3306`, `8080:80`, dan `8081:80`; jangan menghentikan MySQL/XAMPP tanpa memahami dampaknya.

## Laravel tidak terhubung ke MySQL

Di dalam Compose, gunakan `DB_HOST=db`, bukan `127.0.0.1`. Nama service `db` diselesaikan oleh DNS network Docker. Pastikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` konsisten dengan Compose.

## MySQL connection refused tepat setelah restart

Status container `Up` belum selalu berarti MySQL siap. Tunggu `dayatgames_db` menjadi `(healthy)` sebelum menjalankan Artisan. Pada QA, query pertama setelah restart memang terlalu cepat dan berhasil setelah healthcheck selesai.

## `public/storage` link already exists

Ini bukan kegagalan aplikasi jika link sudah benar. `php artisan storage:link` hanya perlu dijalankan sekali. Build context mengecualikan `public/storage` karena Windows reparse point dapat memicu `invalid file request public/storage`.

## Docker build gagal `invalid file request public/storage`

Pastikan `.dockerignore` memuat:

```text
public/storage
```

Lalu jalankan ulang `docker compose build`.

## Vite gagal mengambil font eksternal

Project tidak bergantung pada font remote. Konfigurasi Bunny font dihapus agar build deterministik; UI memakai font stack sistem.

## Tampilan tanpa CSS/JavaScript

Jalankan `npm install` dan `npm run build`, kemudian pastikan `public/build/manifest.json` ada. Reload browser setelah build.

## Test `no such table: games`

Test yang mengakses home dinamis wajib memakai `RefreshDatabase`. PHPUnit menggunakan SQLite in-memory yang terisolasi, sedangkan aplikasi berjalan pada MySQL.

## Filter harga berbeda antara SQLite dan MySQL

Parameter harga di-cast sebagai `DECIMAL(15,2)` dan ekspresi harga dipaksa numerik. Jangan menggantinya dengan `CAST(... AS NUMERIC)` karena sintaks tersebut ditolak MySQL 8 pada percobaan aktual.

## Permission storage/cache

Pastikan `storage` dan `bootstrap/cache` dapat ditulis user PHP-FPM. Dockerfile sudah menjalankan `chown` untuk kedua lokasi.

## Menjaga data

Jangan gunakan `docker compose down -v` kecuali memang ingin menghapus named volume database. Gunakan `docker compose stop`, `restart`, atau `down` tanpa `-v`.
