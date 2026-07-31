# DayatGames

DayatGames adalah aplikasi *marketplace* game digital berbasis Laravel yang dikembangkan sebagai proyek Ujian Akhir Semester mata kuliah DevOps dan Pengembangan Agile. Sistem mengintegrasikan katalog game, transaksi, pembayaran simulasi, kepemilikan game, ulasan pelanggan, dan administrasi data dalam satu aplikasi berbasis basis data relasional.

## Identitas Proyek

| Keterangan | Informasi |
|---|---|
| Mata kuliah | DevOps dan Pengembangan Agile (R.02) |
| Dosen pengampu | Lili Dwi Yulianto, S.Kom., M.Kom. |
| Mahasiswa | Galang Rispai |
| NPM | 237006516020 |
| Tema aplikasi | *Marketplace* game digital |

## Fitur Sistem

### Pengunjung

- Melihat beranda, katalog, detail, galeri pratinjau, dan ulasan game.
- Melakukan pencarian, penyaringan, pengurutan, dan navigasi halaman katalog.
- Membuat akun dan masuk ke dalam sistem.

### Pelanggan

- Mengelola profil dan avatar.
- Menyimpan game ke *wishlist* dan keranjang.
- Melakukan *checkout* dengan kode voucher.
- Memilih metode pembayaran simulasi berupa *Virtual Account*, transfer bank, atau dompet digital.
- Melacak status pesanan dan memperoleh game pada *library* setelah pembayaran terdeteksi.
- Memberikan ulasan terhadap game yang dimiliki.

### Administrator

- Melihat ringkasan data melalui *dashboard*.
- Mengelola game, genre, voucher, pelanggan, pesanan, pembayaran, dan ulasan.
- Menambahkan data pengembang dan penerbit melalui formulir game.
- Memantau riwayat transaksi beserta gambar game dan status pembayaran.

## Teknologi

| Komponen | Teknologi |
|---|---|
| Kerangka aplikasi | Laravel 13 dan Blade |
| Bahasa pemrograman | PHP 8.4 dan JavaScript |
| Basis data | MySQL 8.0 |
| Webserver | Nginx dan PHP-FPM |
| Administrasi basis data | phpMyAdmin |
| Kontainerisasi | Docker Compose |
| Antarmuka | Tailwind CSS, Vite, GSAP, Lenis, dan Swiper |

## Arsitektur Kontainer

DayatGames dijalankan melalui empat layanan Docker Compose:

| Layanan | Fungsi | Akses |
|---|---|---|
| `app` | Menjalankan Laravel melalui PHP-FPM | Port internal 9000 |
| `webserver` | Melayani aplikasi melalui Nginx | `http://localhost:8080` |
| `db` | Menyimpan data aplikasi pada MySQL | `localhost:3306` |
| `phpmyadmin` | Menampilkan dan mengelola basis data | `http://localhost:8081` |

Komunikasi antarkontainer menggunakan jaringan Docker dengan nama layanan sebagai *hostname*. Aplikasi terhubung ke MySQL melalui `DB_HOST=db`.

## Basis Data

Implementasi DayatGames menggunakan 16 tabel bisnis inti dengan 21 *foreign key*. Tabel tersebut mencakup `users`, `developers`, `publishers`, `genres`, `games`, `game_genre`, `game_images`, `carts`, `cart_items`, `wishlists`, `vouchers`, `orders`, `order_items`, `payments`, `libraries`, dan `reviews`.

Relasi basis data, atribut tabel, tipe data, dan keterkaitan antar-entitas disajikan pada [ERD DayatGames](docs/ERD_DevOps_Final.png).

## Pengujian

Hasil verifikasi akhir menunjukkan:

- 70 pengujian berhasil dengan 627 asersi.
- Seluruh service Docker berstatus aktif dan MySQL berstatus sehat.
- Proses CRUD game, genre, voucher, transaksi, pembayaran, dan ulasan berfungsi sesuai kebutuhan.
- Proses pembayaran simulasi bersifat idempoten dan tidak menggandakan pesanan maupun kepemilikan game.
- Antarmuka telah diuji pada tampilan desktop dan perangkat bergerak.
- Penyimpanan data tetap tersedia setelah container dijalankan ulang.

Rincian skenario, hasil aktual, dan status pengujian tercantum pada laporan akhir.

## Struktur Repositori

| Direktori/Berkas | Isi |
|---|---|
| `app/` | Pengendali, model, *middleware*, permintaan, dan layanan aplikasi |
| `database/` | Migrasi, *factory*, dan *seeder* |
| `resources/` | Blade, CSS, dan JavaScript |
| `public/` | Aset publik dan hasil kompilasi antarmuka |
| `docker/nginx/` | Konfigurasi Nginx |
| `tests/` | Pengujian unit dan fitur |
| `docs/` | Spesifikasi, diagram, bukti, dan dokumentasi pengujian |
| `compose.yaml` | Definisi layanan Docker Compose |
| `Dockerfile` | Konfigurasi citra aplikasi PHP-FPM |

## Akun Pengujian

| Peran | Surel | Kata sandi |
|---|---|---|
| Administrator | `admin@dayatgames.test` | `password` |
| Pelanggan | `customer@dayatgames.test` | `password` |

## Laporan Akhir

- [Laporan akhir format DOCX](docs/report/Laporan_Akhir_DevOps_Agile_DayatGames_Galang_Rispai.docx)
- [Laporan akhir format PDF](docs/report/Laporan_Akhir_DevOps_Agile_DayatGames_Galang_Rispai.pdf)
- [Diagram Hubungan Entitas](docs/ERD_DevOps_Final.png)
- [Diagram arsitektur kontainer](docs/ARCHITECTURE_DevOps_Final.png)
