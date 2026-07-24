# Command Log DayatGames

Catatan ini merangkum perintah penting dan hasil aktual. Password atau rahasia tidak dicatat.

## 24 Juli 2026 — Fase 0

| Perintah | Hasil |
|---|---|
| `docker version` | Berhasil; Docker Desktop 4.81.0 dan Engine 29.6.1 aktif. |
| `docker ps -a` | Menemukan empat container Laravel lama; seluruhnya berhenti sejak sekitar sembilan hari sebelumnya. |
| `docker inspect laravel_app laravel_nginx laravel_mysql laravel_phpmyadmin` | Label working directory dan config menunjuk ke `C:\Coding\laravel-docker`; bind mount source menunjuk ke `C:\Coding\laravel-docker\src`. |
| Pemeriksaan `compose.yaml`, `Dockerfile`, Nginx, dan source lama | Baseline Laravel 13.8/PHP 8.4 ditemukan; Nginx root menunjuk ke `/var/www/public`. |
| Pemeriksaan Git folder lama dan folder induk UAS | Keduanya bukan repository Git. |
| Penyalinan source relevan ke folder final | Berhasil; `.env` dan `vendor` tidak disalin. |
| `git init` dan `git switch -c feature/dayatgames-uas` | Repository lokal dan branch kerja berhasil dibuat. |

## 24 Juli 2026 — Fase 1

| Aktivitas | Hasil |
|---|---|
| Penyusunan project specification | Aktor, scope, aturan bisnis, arsitektur, dan acceptance criteria terdokumentasi. |
| Penyusunan FR/NFR | 20 functional requirements dan 12 non-functional requirements tersedia. |
| Penyusunan backlog/traceability | 24 backlog dipetakan ke FR, target implementasi, tabel, test, dan screenshot. |
| Perancangan database | 15 tabel bisnis, constraint, indeks, ERD Mermaid, serta arsitektur Mermaid dirancang. |

## 24 Juli 2026 — Fase 2

| Perintah | Hasil |
|---|---|
| Pemeriksaan port 3306, 8080, 8081 | Ketiga port bebas sebelum service final dijalankan. |
| `docker compose config` | Exit 0; empat service, network, DB volume, vendor volume, healthcheck, dan port tervalidasi. |
| `docker compose build --pull` | Percobaan pertama tertahan lock metadata Docker; percobaan ulang exit 0 dan menghasilkan image `dayatgames-app`. |
| `docker compose up -d` | Empat container dibuat dan dijalankan; MySQL mencapai status healthy. |
| `composer install` pada vendor volume | Dependency development termasuk PHPUnit/Pail berhasil dilengkapi setelah temuan provider Pail. |
| `php artisan key:generate --force` | Berhasil; key hanya tersimpan pada `.env` lokal yang diabaikan Git. |
| `php artisan migrate --force` | Tiga migration framework baseline berhasil dijalankan pada database baru `dayatgames`. |
| HTTP smoke test | `http://localhost:8080` mengembalikan 200 dengan title DayatGames; `http://localhost:8081` mengembalikan 200 dengan title phpMyAdmin. |
| `docker compose ps` | app, webserver, db, dan phpmyadmin Up; db healthy. |

## 24 Juli 2026 — Fase 3

| Perintah/aktivitas | Hasil |
|---|---|
| PHP lint pada `app` dan `database` | Tidak ada syntax error. |
| `php artisan migrate --force` | Migration users extension, katalog, dan commerce berhasil pada MySQL. |
| `php artisan db:seed --force` | Admin, customer, 16 developer, publisher, 12 genre, dan 17 game dibuat/di-update tanpa duplikasi. |
| Query `INFORMATION_SCHEMA` | Tepat 15 tabel bisnis inti dan 20 foreign key ditemukan. |
| Steam Storefront API `cc=id` | 16 App ID terverifikasi; 15 harga IDR tersedia, satu harga tidak tersedia; Ghost of Yōtei tetap data demo. |
| Normalisasi aset dengan Sharp | 17 gambar user dikonversi ke WebP; logo dan favicon diturunkan tanpa menimpa source. |
| Mermaid CLI + Chrome lokal | `ERD.png` dan `ARCHITECTURE.png` berhasil dirender dari source `.mmd`. |
| `php artisan test --testsuite=Feature` | 3 feature test lulus dengan 24 assertion, termasuk 15 tabel dan relasi katalog. |
| `php artisan test` | Verifikasi penuh fase database: 4 test lulus dengan 25 assertion. |

## 24 Juli 2026 — Fase 4

| Perintah/aktivitas | Hasil |
|---|---|
| Implementasi auth session native Laravel | Registrasi, login dengan throttle, logout, dan session regeneration tersedia. |
| Middleware `role` | Guest dialihkan ke login; customer menerima 403; admin dapat membuka dashboard. |
| Profile dan avatar | Update nama/email/telepon serta upload image maksimal 2 MB tersedia; storage symlink berhasil dibuat. |
| `php artisan test --filter='AuthenticationTest\|RoleAuthorizationTest'` | 7 test lulus dengan 22 assertion. |
| `npm run build` | Percobaan awal gagal karena font plugin mencoba fetch eksternal; remote font dihapus, build ulang berhasil dalam 118 ms. |
| HTTP smoke test | `/login` dan `/register` mengembalikan 200; `/admin` guest mengembalikan 302 ke login. |
