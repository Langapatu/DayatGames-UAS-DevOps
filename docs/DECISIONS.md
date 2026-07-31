# Keputusan Teknis DayatGames

## ADR-001 — Struktur proyek mandiri

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: proyek DayatGames menggunakan struktur Laravel dan konfigurasi Docker yang mandiri.
- Alasan: pemisahan komponen aplikasi, webserver, basis data, dan dokumentasi memudahkan pengembangan serta pengujian.

## ADR-002 — Pertahankan versi framework baseline

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: menggunakan Laravel 13.8 dan PHP 8.4 yang kompatibel dengan kebutuhan aplikasi.
- Alasan: versi tersebut mendukung implementasi fitur dan lingkungan container yang digunakan.

## ADR-003 — Source bersih tanpa artefak lokal

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: `.env`, `vendor`, `node_modules`, dan artefak lokal tidak disertakan dalam repository.
- Alasan: mencegah kebocoran kredensial dan menjaga proses build tetap dapat direproduksi.

## ADR-004 — Struktur service Compose

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: memakai service `app`, `webserver`, `db`, dan `phpmyadmin`, dengan Nginx meneruskan PHP ke `app:9000`.
- Alasan: memisahkan tanggung jawab tiap container dan memungkinkan komunikasi melalui Docker network.

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

## ADR-009 — Autentikasi session native Laravel

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: auth dibuat dengan controller/request Laravel tanpa memasang Breeze.
- Alasan: baseline Laravel 13 belum menyertakan Breeze dan auth yang dibutuhkan dapat dibuat dengan komponen framework tanpa mengubah major version.

## ADR-010 — Tidak melakukan fetch font saat build

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: plugin remote Bunny font dihapus dari Vite; UI memakai font stack lokal/sistem.
- Alasan: build harus deterministik dan tidak gagal saat akses jaringan frontend dibatasi.

## ADR-011 — Payment simulasi tetap memakai alur transaksi nyata

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: Virtual Account, transfer bank, dan e-wallet hanya berupa simulasi akademik, tetapi order, snapshot item, payment, bukti, verifikasi, library, dan pembersihan cart diproses melalui database transaction.
- Alasan: aplikasi dapat memperagakan konsistensi transaksi dan otorisasi tanpa mengklaim integrasi bank atau payment gateway sungguhan.

## ADR-012 — Cyan mengikuti logo, violet dipertahankan sebagai aksen

- Tanggal: 24 Juli 2026
- Status: diterima
- Keputusan: background dan card tetap mengikuti palet awal, sementara cyan/steel-blue dari logo diperkuat pada brand, focus state, harga, dan link; violet tetap dipakai terbatas pada CTA.
- Alasan: identitas terasa konsisten dengan logo pengguna tanpa menghilangkan kontras dan hierarki warna yang sudah ditetapkan.
