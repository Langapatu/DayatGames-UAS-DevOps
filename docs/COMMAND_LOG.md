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

## 24 Juli 2026 — Fase 5

| Perintah/aktivitas | Hasil |
|---|---|
| Implementasi CRUD katalog admin | Genre, publisher, developer, dan game memiliki index/search/pagination, create, update, serta delete terproteksi. |
| Form Request katalog | Slug unik, URL, relasi, status, upload image, rentang harga, dan konsistensi diskon divalidasi server-side. |
| `php artisan route:list --path=admin` | 26 route admin terdaftar, termasuk empat resource katalog. |
| PHP lint controller/request/model/test | Seluruh file yang diperiksa bebas syntax error. |
| `php artisan test --filter=AdminCatalogCrudTest` | 4 test lulus dengan 29 assertion. |
| `php artisan test` | Regresi penuh: 15 test lulus dengan 76 assertion. |
| `npm run build` | Vite production build berhasil dalam 126 ms. |

## 24 Juli 2026 — Fase 6

| Perintah/aktivitas | Hasil |
|---|---|
| Implementasi storefront | Home, katalog published, detail, game terkait, search, filter genre/harga, serta sort tersedia. |
| Implementasi fitur customer | Wishlist dan cart mendukung add/remove, idempotensi duplikasi, dan larangan cart untuk game yang sudah dimiliki. |
| `php artisan test --filter=CustomerMarketplaceTest` | 5 test lulus dengan 32 assertion. |
| Filter harga MySQL nyata | Query awal menemukan perbedaan sintaks cast SQLite/MySQL; diperbaiki dengan `DECIMAL(15,2)` dan endpoint kembali HTTP 200. |
| HTTP smoke test | `/`, `/games`, dan detail `ghost-of-tsushima` masing-masing mengembalikan HTTP 200. |
| `npm run build` | Vite production build berhasil dalam 137 ms setelah view storefront ditambahkan. |
| `php artisan test` | Regresi penuh setelah test home memakai `RefreshDatabase`: 20 test lulus dengan 108 assertion. |

## 24 Juli 2026 — Fase 7

| Perintah/aktivitas | Hasil |
|---|---|
| Implementasi checkout | Harga dihitung ulang dari game database; order, item snapshot, dan payment dibuat dalam transaction. |
| Implementasi payment simulasi | Virtual Account unik berlabel simulasi, transfer bank, e-wallet, referensi, dan proof upload tervalidasi tersedia. |
| Implementasi administrasi transaksi | Customer, order, payment verify/reject, dan review moderation tersedia di area admin. |
| Implementasi verifikasi | Payment/order/library/cart disinkronkan dalam transaction; `updateOrCreate` membuat verifikasi ulang idempotent. |
| `php artisan view:cache` | Seluruh Blade template berhasil dikompilasi. |
| `php artisan test --filter=TransactionWorkflowTest` | 6 test lulus dengan 37 assertion. |
| `php artisan test` | Regresi penuh: 26 test lulus dengan 145 assertion. |
| `npm run build` | Vite production build berhasil dalam 173 ms. |

## 24 Juli 2026 — Fase 8

| Perintah/aktivitas | Hasil |
|---|---|
| Concept board berbasis logo | `docs/design/dayatgames-ui-concept.png` dibuat sebagai arah desain, bukan bukti aplikasi. |
| `npm install gsap lenis swiper` | Dependency motion terpasang; package sementara Mermaid yang tidak disimpan ikut dibersihkan npm. |
| Browser QA desktop | Home tampil tanpa horizontal overflow; Swiper terinisialisasi; GSAP target ditemukan; console tidak memiliki error/warning. |
| Browser QA mobile 390×844 | Menu `aria-expanded` berubah benar, navigasi tampil, hero/card responsif, dan tidak ada horizontal overflow. |
| Carousel interaction | Tombol kemudian ArrowRight memindahkan active slide sampai label `3 / 5`. |
| `npm audit --audit-level=high` | Tidak ditemukan vulnerability. |
| `npm run build` | 44 module ditransformasi; build berhasil dalam 188 ms. |
| `php artisan test` | Regresi penuh tetap lulus: 26 test, 145 assertion. |

## 24 Juli 2026 — Fase 9

| Perintah/aktivitas | Hasil |
|---|---|
| `docker compose config --quiet` | Valid. |
| `docker compose build` | Percobaan awal gagal karena Windows reparse point `public/storage`; setelah ditambah ke `.dockerignore`, build berhasil. |
| `docker compose up -d`, migrate, seed | Image terbaru diterapkan; tidak ada migration tertunda; seluruh seeder idempotent berhasil. |
| `php artisan storage:link` | Melaporkan link sudah ada; link existing dipertahankan. |
| Browser flow MySQL nyata | Customer cart/checkout VA membuat order ID 1; admin verify; Atomic Heart masuk library; review dibuat dan dipublikasikan. |
| `docker compose restart` | Seluruh service restart. Query pertama terlalu cepat saat DB `health: starting`; setelah healthy, seluruh service Up dan record terbaca. |
| Persistence check | Genre ID 13 dengan nama `QA Persistence` tetap tersedia. |
| HTTP setelah restart | `/`, `/games`, `/login`, dan phpMyAdmin mengembalikan HTTP 200. |
| Count MySQL | 2 users, 17 games, 13 genres sebelum browser transaksi; kemudian 1 order/library/review nyata ditambahkan. |
| phpMyAdmin | Login melalui `dayatgames_user`; database `dayatgames`, tabel, FK games, serta record games/orders/genres terlihat. |
| Browser console | Tidak ada error atau warning selama flow end-to-end. |
| QA CRUD browser | Game QA dibuat, diperbarui, dikonfirmasi hapus, lalu terhapus; screenshot 10–14 tersimpan. |
| Verifikasi final | 26 test/145 assertion, Vite 44 module, npm audit 0 vulnerability, dan 60 route aplikasi. |
| Count transaksi MySQL | 17 games, 1 order, 1 payment verified, 1 library, 1 review published, 1 cart item, dan 1 wishlist. |
| Secret/ignore check | `.env`, `vendor`, dan `node_modules` ignored; pola API token/private key tidak ditemukan pada tracked source. |
| `gh auth status` | Tidak dapat dijalankan karena GitHub CLI tidak terpasang; tidak ada URL repository yang diklaim. |

## 24 Juli 2026 - Fase 11

| Perintah/aktivitas | Hasil |
|---|---|
| Pembuatan DOCX | Laporan akademik A4 dibuat dengan Times New Roman 12 pt, spasi 1,5, margin akademik, heading, caption, tabel, gambar, dan lampiran. |
| Update field Microsoft Word | Daftar isi, 31 PAGEREF gambar, 9 PAGEREF tabel, dan nomor halaman diperbarui; DOCX final 52 halaman tanpa placeholder. |
| Render DOCX | Microsoft Word menghasilkan PDF QA sementara 52 halaman; seluruh halaman diraster dengan Poppler dan diperiksa. |
| Pembuatan PDF final | HTML A4 dari struktur DOCX dicetak lokal melalui Chrome headless dengan pemetaan halaman dua-pass. |
| Validasi PDF | 47 halaman A4; 48 heading, 31 gambar, dan 9 tabel seluruhnya terpetakan; page map stabil pada render kedua. |
| Pemeriksaan visual | Seluruh 52 halaman DOCX dan 47 halaman PDF diperiksa; tidak ada clipping, overlap, halaman kosong, placeholder, atau footer ganda. |

## 24 Juli 2026 - Fase 12

| Perintah/aktivitas | Hasil |
|---|---|
| Redesign auth dan navbar | Login/registrasi menjadi split-panel modern; navbar customer diringkas dengan active state dan menu mobile. |
| Redesign admin CRUD | Sembilan menu admin dipindahkan ke sidebar; topbar, filter, tabel, form, thumbnail, dan aksi memiliki hierarchy konsisten. |
| Koreksi gambar game | Card, detail, cart, checkout, order, library, dan thumbnail admin memakai frame `object-fit: contain`; browser mengukur seluruh card 303x188 di dalam frame 303x189. |
| Browser QA desktop | Login, registrasi, dashboard, games index, form create, katalog, dan detail tampil tanpa error/warning console. |
| Browser QA mobile 390x844 | Navbar customer dan drawer admin berfungsi; `scrollWidth` kembali 375 px dan tidak ada overflow horizontal. |
| Screenshot refresh | 26 screenshot UI diambil ulang; empat bukti tambahan mencakup registrasi serta auth, katalog, dan admin mobile. |
| `npm run build` | 44 modul ditransformasi; build Vite berhasil. |
| `php artisan test` | 26 test dan 145 assertion lulus. |
| Iterasi UI final | Cover 4:5, tinggi kartu seragam, profil modern, badge cart dinamis, pagination gelap, dan layout admin responsif diverifikasi pada desktop serta mobile. |
| `php artisan test` | Regresi final: 26 test dan 148 assertion lulus. |
| Refresh laporan final | 36 screenshot nyata, 36 gambar laporan, dan 9 tabel; DOCX divalidasi struktural dan PDF 48 halaman A4 diperiksa tanpa clipping atau overlap. |
| Regenerasi laporan | DOCX menjadi 53 halaman; PDF A4 menjadi 48 halaman dengan 36 gambar dan 9 tabel. |
| Visual QA final | Seluruh 48 halaman PDF diraster menggunakan Poppler dan diperiksa tanpa clipping, overlap, atau gambar rusak. |
| Catatan render DOCX | LibreOffice tidak tersedia dan otomasi ekspor Word build terakhir tidak selesai; indeks DOCX dimaterialisasi statis, placeholder ditiadakan, dan validasi struktur dijalankan. |
