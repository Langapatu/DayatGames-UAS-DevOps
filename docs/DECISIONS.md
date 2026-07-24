# Keputusan Teknis DayatGames

## ADR-001 — Workspace final terpisah

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: project final dibangun di `C:\.Kuliah\TugasMatkul\DevOPS\UAS\DayatGames`.
- Alasan: folder Docker lama harus tetap utuh dan hanya berfungsi sebagai baseline referensi.

## ADR-002 — Pertahankan versi framework baseline

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: mempertahankan Laravel 13.8 dan PHP 8.4 dari project lama selama kompatibel.
- Alasan: prompt melarang perubahan major version hanya untuk menambahkan fitur.

## ADR-003 — Source bersih tanpa artefak lokal lama

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: menyalin source framework dan konfigurasi yang relevan, tetapi tidak menyalin `.env` dan `vendor`.
- Alasan: mencegah kebocoran kredensial, ketergantungan host lama, dan artefak build yang tidak dapat direproduksi.

## ADR-004 — Struktur service Compose

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: memakai service `app`, `webserver`, `db`, dan `phpmyadmin`, dengan Nginx meneruskan PHP ke `app:9000`.
- Alasan: mengikuti arsitektur tutorial dosen dan memisahkan tanggung jawab tiap container.

## ADR-005 — Riwayat transaksi dipertahankan

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: relasi master ke transaksi tidak memakai cascade yang dapat menghapus riwayat; `order_items.game_id` boleh menjadi null, sedangkan snapshot judul/harga tetap disimpan.
- Alasan: perubahan atau penghapusan master game tidak boleh mengubah bukti transaksi sebelumnya.

## ADR-006 — Idempotensi melalui unique constraint dan transaction

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: cart item, wishlist, library, dan review memakai unique composite; checkout/verifikasi payment dibungkus transaksi database.
- Alasan: mencegah duplikasi pada request berulang atau proses verifikasi ulang.

## ADR-007 — Dependency development tersedia di image UAS

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: Dockerfile menjalankan `composer install` termasuk dependency development.
- Alasan: container yang sama digunakan untuk pengembangan dan pembuktian `php artisan test`; skeleton Laravel 13 juga mendaftarkan Pail pada lingkungan lokal.

## ADR-008 — Vendor memakai named volume terpisah

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: `/var/www/vendor` dipasang sebagai named volume agar bind mount source Windows tidak menutupi dependency hasil build.
- Alasan: source tetap dapat diedit dari host sementara dependency Linux tetap konsisten di container.
