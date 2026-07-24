# Product Backlog DayatGames

| ID | User story | Prioritas | Acceptance criteria ringkas | FR | Status | Bukti implementasi | Test |
|---|---|---|---|---|---|---|---|
| PB-01 | Sebagai guest, saya ingin registrasi agar dapat bertransaksi. | Must | Email unik; role selalu customer; password hash. | FR-01 | Done | `RegisteredUserController`, `RegisterRequest` | AuthenticationTest |
| PB-02 | Sebagai pengguna, saya ingin login/logout secara aman. | Must | Session dibuat/dihapus; redirect sesuai role. | FR-02 | Done | `AuthenticatedSessionController`, `LoginRequest` | AuthenticationTest |
| PB-03 | Sebagai guest, saya ingin melihat katalog published. | Must | Draft tidak tampil; pagination tersedia. | FR-03 | Planned | Belum ada | CatalogTest |
| PB-04 | Sebagai pengunjung, saya ingin search/filter katalog. | Must | Judul, genre, dan harga dapat difilter; query dipertahankan. | FR-04 | Planned | Belum ada | CatalogFilterTest |
| PB-05 | Sebagai pengunjung, saya ingin melihat detail game. | Must | Metadata, harga, gallery, review, related tampil. | FR-05 | Planned | Belum ada | GameDetailTest |
| PB-06 | Sebagai customer, saya ingin menyimpan wishlist. | Should | Tambah/hapus; tidak duplikat. | FR-06 | Planned | Belum ada | WishlistTest |
| PB-07 | Sebagai customer, saya ingin mengelola cart digital. | Must | Add/remove; hanya published; bukan milik sendiri; tidak duplikat. | FR-07 | Planned | Belum ada | CartTest |
| PB-08 | Sebagai customer, saya ingin checkout secara konsisten. | Must | Harga server-side; transaction; order/items/payment dibuat. | FR-08 | Planned | Belum ada | CheckoutTest |
| PB-09 | Sebagai customer, saya ingin memilih pembayaran demo. | Must | VA/transfer/e-wallet tersimpan; bukti tervalidasi. | FR-09 | Planned | Belum ada | PaymentSubmissionTest |
| PB-10 | Sebagai customer, saya ingin melihat order sendiri. | Must | Index/detail hanya milik user aktif. | FR-10 | Planned | Belum ada | OrderAuthorizationTest |
| PB-11 | Sebagai customer, saya ingin game terverifikasi masuk library. | Must | Tepat satu library per user/game. | FR-11 | Planned | Belum ada | PaymentVerificationTest |
| PB-12 | Sebagai pemilik game, saya ingin membuat review. | Should | Hanya pemilik; satu review; rating 1–5. | FR-12 | Planned | Belum ada | ReviewTest |
| PB-13 | Sebagai admin, saya ingin CRUD genre. | Must | Search, pagination, validasi, flash, proteksi role. | FR-13 | Done | `Admin\GenreController`, `GenreRequest`, views admin | AdminCatalogCrudTest |
| PB-14 | Sebagai admin, saya ingin CRUD publisher. | Must | Search, pagination, validasi, flash, proteksi role. | FR-14 | Done | `Admin\PublisherController`, `PublisherRequest`, views admin | AdminCatalogCrudTest |
| PB-15 | Sebagai admin, saya ingin CRUD developer. | Should | Search, pagination, validasi, flash, proteksi role. | FR-15 | Done | `Admin\DeveloperController`, `DeveloperRequest`, views admin | AdminCatalogCrudTest |
| PB-16 | Sebagai admin, saya ingin CRUD game. | Must | Relasi, harga/diskon, upload, genre, status, featured tervalidasi. | FR-16 | Done | `Admin\GameController`, `GameRequest`, views admin | AdminCatalogCrudTest |
| PB-17 | Sebagai admin, saya ingin memeriksa order. | Must | Search/filter/status dan detail snapshot tersedia. | FR-17 | Planned | Belum ada | AdminOrderTest |
| PB-18 | Sebagai admin, saya ingin verify/reject payment. | Must | Transaction; idempotent; library/cart/order sinkron. | FR-18 | Planned | Belum ada | PaymentVerificationTest |
| PB-19 | Sebagai admin, saya ingin moderasi review. | Should | Pending dapat menjadi published/rejected. | FR-19 | Planned | Belum ada | AdminReviewTest |
| PB-20 | Sebagai admin, saya ingin melihat dashboard. | Should | Statistik berasal dari query database nyata. | FR-20 | Done | `Admin\DashboardController`, `admin.dashboard` | RoleAuthorizationTest |
| PB-21 | Sebagai penguji, saya ingin stack yang dapat direproduksi. | Must | config/build/up/ps/migrate/seed berhasil. | NFR-01–04 | Planned | Belum ada | Docker command log |
| PB-22 | Sebagai pengguna keyboard, saya ingin UI aksesibel. | Should | Focus visible, label/form benar, carousel keyboard, reduced motion. | NFR-07, NFR-12 | Planned | Belum ada | Browser QA |
| PB-23 | Sebagai maintainer, saya ingin data bertahan setelah restart. | Must | Record uji ditemukan setelah `docker compose restart`. | NFR-09 | Planned | Belum ada | Persistence check |
| PB-24 | Sebagai dosen, saya ingin bukti dan laporan yang sesuai implementasi. | Must | Screenshot nyata, dokumentasi, DOCX/PDF tervalidasi. | NFR-10 | Planned | Belum ada | Evidence checklist |

Prioritas menggunakan Must/Should/Could. Kolom status akan diubah menjadi In Progress atau Done hanya setelah bukti aktual tersedia.
