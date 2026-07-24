# DayatGames

DayatGames adalah marketplace game digital berbasis Laravel untuk project UAS DevOps dan Pengembangan Agile. Aplikasi menyediakan katalog, wishlist, cart, checkout, pembayaran simulasi, library, review, serta panel admin.

## Stack

- Laravel 13 dan Blade
- PHP 8.4 FPM
- MySQL 8.0
- Nginx
- phpMyAdmin
- Docker Compose
- Vite, Tailwind CSS, dan JavaScript

## Requirement

- Docker Desktop dengan Docker Compose v2
- Port host 8080, 8081, dan 3306 tersedia
- Git

Node.js 22+ pada host diperlukan untuk mengembangkan/build frontend dan motion dependency.

## Instalasi

```powershell
Copy-Item .env.example .env
docker compose config
docker compose build
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
npm install
npm run build
```

## URL

- Aplikasi: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MySQL host: `127.0.0.1:3306`
- MySQL dari container: `db:3306`

`DB_HOST=db` digunakan karena Docker menyediakan DNS internal berdasarkan nama service. Mapping `8080:80` berarti port 8080 pada Windows diteruskan ke port 80 Nginx. Prinsip yang sama berlaku untuk `3306:3306` dan `8081:80`.

## Akun demo

Setelah seeder final tersedia:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@dayatgames.test` | `password` |
| Customer | `customer@dayatgames.test` | `password` |

Akun dan password tersebut hanya untuk lingkungan lokal/demo.

## Perintah harian

```powershell
docker compose up -d
docker compose ps
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan test
docker compose logs --tail=100 app webserver db
docker compose stop
```

`docker compose down -v` tidak digunakan karena menghapus volume database.

## Struktur ringkas

- `app/` — controller, model, middleware, dan Form Request
- `database/` — migration, factory, dan seeder
- `resources/` — Blade, CSS, dan JavaScript
- `public/` — entry point dan aset publik
- `docker/nginx/` — konfigurasi Nginx
- `docs/` — spesifikasi, bukti, diagram, testing, dan laporan

## Testing dan build

```powershell
docker compose exec app php artisan test
npm run build
npm audit --audit-level=high
```

## Troubleshooting singkat

- Jika MySQL gagal bind ke port 3306, periksa service MySQL/XAMPP yang sedang menggunakan port itu. Jangan menghentikannya tanpa memastikan dampak.
- Jika aplikasi belum memiliki key, jalankan `docker compose exec app php artisan key:generate`.
- Jika permission cache bermasalah, periksa akses tulis `storage` dan `bootstrap/cache`.
- Jika tampilan tanpa CSS, jalankan build Vite dan periksa `public/build/manifest.json`.

Dokumentasi lebih lengkap tersedia di folder `docs`.

## Fitur

- Guest: home, katalog published, search/filter/sort, detail dan review published.
- Customer: profil/avatar, wishlist, cart digital, checkout, tiga payment simulasi, order, library, dan review pemilik.
- Admin: dashboard statistik, CRUD game/genre/publisher/developer, customer, order, verify/reject payment, dan moderasi review.
- UI: responsive navigation, GSAP/ScrollTrigger, Lenis, Swiper keyboard/touch, focus state, dan reduced motion.

## Hasil verifikasi terakhir

- 15 tabel bisnis inti dan 20 foreign key.
- 17 game seeded dari aset pengguna.
- 26 test lulus dengan 145 assertion.
- Vite build lulus; npm audit 0 vulnerability.
- Service `app`, `webserver`, `db`, dan `phpmyadmin` Up; MySQL healthy.
- Record `QA Persistence` ID 13 tetap ada setelah seluruh Compose direstart.

Detail aktual tersedia di [docs/TESTING.md](docs/TESTING.md), [docs/COMMAND_LOG.md](docs/COMMAND_LOG.md), dan [docs/SCREENSHOT_CHECKLIST.md](docs/SCREENSHOT_CHECKLIST.md).

## Laporan akhir

- DOCX: `docs/report/Laporan_Akhir_DayatGames_Galang_Rispai.docx` (52 halaman pada render Microsoft Word).
- PDF: `docs/report/Laporan_Akhir_DayatGames_Galang_Rispai.pdf` (47 halaman A4).
- Keduanya memuat daftar isi, daftar gambar, daftar tabel, BAB I-VII, daftar pustaka, lampiran konfigurasi, diagram, dan bukti aplikasi.
- Seluruh halaman DOCX dan PDF telah diraster dan diperiksa; tidak ditemukan clipping, overlap, halaman kosong, atau placeholder field.

## Commit lokal

Branch kerja: `feature/dayatgames-uas`. Lihat riwayat dengan:

```powershell
git log --oneline --decorate
```

Repository GitHub belum diklaim tersedia sampai autentikasi dan push benar-benar dilakukan.

## Disclaimer

DayatGames merupakan aplikasi akademik untuk keperluan pembelajaran. Nama game, merek, dan aset terkait merupakan milik pemegang hak masing-masing. Harga yang ditampilkan merupakan data demonstrasi dan dapat berbeda dari harga toko resmi.
