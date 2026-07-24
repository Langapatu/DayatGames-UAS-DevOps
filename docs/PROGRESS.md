# Progress DayatGames

Pembaruan terakhir: 24 Juli 2026 (Asia/Jakarta)

## Status fase

| Fase | Status | Ringkasan |
|---|---|---|
| 0. Pemeriksaan dan backup | Selesai | Docker Desktop aktif; container lama ditemukan; project lama berada di `C:\Coding\laravel-docker`; workspace final dibuat terpisah dan Git diinisialisasi. |
| 1. Spesifikasi | Selesai | Project spec, 20 FR, 12 NFR, 24 backlog, traceability, skema 15 tabel, ERD, dan arsitektur awal tersedia. |
| 2. Docker dan Laravel baseline | Selesai | Compose valid; image build berhasil; app/webserver/db/phpMyAdmin Up; DB healthy; Laravel dan phpMyAdmin merespons HTTP 200. |
| 3. Database | Selesai | 15 tabel bisnis, 20 FK, model/relasi, seeder idempotent, 17 game, mapping aset, 15 harga Steam terverifikasi, ERD/arsitektur PNG, dan test skema tersedia. |
| 4. Auth dan role | Selesai | Registrasi customer, login/logout, rate limit, profile, upload avatar, role middleware, dashboard admin, dan 7 test auth/role tersedia. |
| 5. CRUD admin | Selesai | CRUD genre, publisher, developer, dan game dilengkapi search, pagination, slug route binding, validasi Form Request, relasi genre, upload image, serta 4 test dengan 29 assertion. |
| 6. Marketplace customer | Selesai | Home, katalog published, search/filter/sort, detail, related games, wishlist, dan cart tersedia; 5 test marketplace lulus dengan 32 assertion. |
| 7. Checkout dan payment | Selesai | Checkout server-side, snapshot order, tiga metode payment simulasi, proof upload, admin verify/reject idempotent, library, review, moderasi, users/orders admin, dan 6 test transaksi tersedia. |
| 8. UI dan motion | Selesai | Concept board, responsive menu, GSAP/ScrollTrigger, Lenis, Swiper keyboard/touch, card motion, focus/skip link, fallback JS, dan reduced motion tersedia serta diuji di desktop/mobile. |
| 9. QA | Selesai | Compose/build/migrate/seed, 26 test/145 assertion, build/audit, HTTP, browser MySQL end-to-end, phpMyAdmin, restart, dan persistensi ID 13 terverifikasi. |
| 10. Evidence dan Git | Selesai | 31 screenshot browser/phpMyAdmin nyata, 5 item manual transparan, test matrix, troubleshooting, traceability final, secret scan, dan commit bertahap tersedia; GitHub CLI tidak terpasang. |
| 11. Laporan | Selesai | DOCX 52 halaman dan PDF 47 halaman tersedia; daftar isi/gambar/tabel, 31 gambar, 9 tabel, sitasi primer, lampiran, serta pemeriksaan visual seluruh halaman lulus. |

## Kondisi awal terverifikasi

- Docker Desktop 4.81.0 aktif dengan Docker Engine 29.6.1.
- Container lama `laravel_app`, `laravel_nginx`, `laravel_mysql`, dan `laravel_phpmyadmin` ditemukan dalam keadaan berhenti.
- Label Compose dan bind mount menunjuk ke `C:\Coding\laravel-docker`.
- Source lama menggunakan Laravel 13.8, PHP 8.4 FPM, MySQL 8.0, Nginx Alpine, dan phpMyAdmin.
- Folder lama bukan repository Git dan tidak diubah.
- Folder final dibuat di `C:\.Kuliah\TugasMatkul\DevOPS\UAS\DayatGames`.
- Branch kerja: `feature/dayatgames-uas`.
- `.env` dan `vendor` lama tidak disalin.

## Blocker aktif

Tidak ada blocker kritis. Kendala dependency development dan tabel session pada baseline telah diselesaikan.
