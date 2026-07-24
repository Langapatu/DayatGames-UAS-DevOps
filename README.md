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

Node.js host bersifat opsional karena build frontend dapat dijalankan melalui container sementara bila diperlukan.

## Instalasi

```powershell
Copy-Item .env.example .env
docker compose config
docker compose build
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
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

- `app/` — controller, model, middleware, request, policy, dan service
- `database/` — migration, factory, dan seeder
- `resources/` — Blade, CSS, dan JavaScript
- `public/` — entry point dan aset publik
- `docker/nginx/` — konfigurasi Nginx
- `docs/` — spesifikasi, bukti, diagram, testing, dan laporan

## Testing dan build

```powershell
docker compose exec app php artisan test
npm install
npm run build
```

## Troubleshooting singkat

- Jika MySQL gagal bind ke port 3306, periksa service MySQL/XAMPP yang sedang menggunakan port itu. Jangan menghentikannya tanpa memastikan dampak.
- Jika aplikasi belum memiliki key, jalankan `docker compose exec app php artisan key:generate`.
- Jika permission cache bermasalah, periksa akses tulis `storage` dan `bootstrap/cache`.
- Jika tampilan tanpa CSS, jalankan build Vite dan periksa `public/build/manifest.json`.

Dokumentasi lebih lengkap tersedia di folder `docs`.

## Disclaimer

DayatGames merupakan aplikasi akademik untuk keperluan pembelajaran. Nama game, merek, dan aset terkait merupakan milik pemegang hak masing-masing. Harga yang ditampilkan merupakan data demonstrasi dan dapat berbeda dari harga toko resmi.

