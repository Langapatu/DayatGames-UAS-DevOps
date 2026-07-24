# Product Backlog DayatGames

| ID | User story | Prioritas | Acceptance criteria ringkas | FR | Status | Bukti implementasi | Test |
|---|---|---|---|---|---|---|---|
| PB-01 | Sebagai guest, saya ingin registrasi agar dapat bertransaksi. | Must | Email unik; role selalu customer; password hash. | FR-01 | Done | `RegisteredUserController`, `RegisterRequest` | AuthenticationTest |
| PB-02 | Sebagai pengguna, saya ingin login/logout secara aman. | Must | Session dibuat/dihapus; redirect sesuai role. | FR-02 | Done | `AuthenticatedSessionController`, `LoginRequest` | AuthenticationTest |
| PB-03 | Sebagai guest, saya ingin melihat katalog published. | Must | Draft tidak tampil; pagination tersedia. | FR-03 | Done | `HomeController`, `CatalogController`, storefront views | CustomerMarketplaceTest |
| PB-04 | Sebagai pengunjung, saya ingin search/filter katalog. | Must | Judul, genre, dan harga dapat difilter; query dipertahankan. | FR-04 | Done | Filter search/genre/harga/sort pada `CatalogController` | CustomerMarketplaceTest |
| PB-05 | Sebagai pengunjung, saya ingin melihat detail game. | Must | Metadata, harga, gallery, review, related tampil. | FR-05 | Done | `storefront.catalog.show` | CustomerMarketplaceTest |
| PB-06 | Sebagai customer, saya ingin menyimpan wishlist. | Should | Tambah/hapus; tidak duplikat. | FR-06 | Done | `Customer\WishlistController`, wishlist view | CustomerMarketplaceTest |
| PB-07 | Sebagai customer, saya ingin mengelola cart digital. | Must | Add/remove; hanya published; bukan milik sendiri; tidak duplikat. | FR-07 | Done | `Customer\CartController`, cart view | CustomerMarketplaceTest |
| PB-08 | Sebagai customer, saya ingin checkout secara konsisten. | Must | Harga server-side; transaction; order/items/payment dibuat. | FR-08 | Done | `Customer\CheckoutController`, `CheckoutRequest` | TransactionWorkflowTest |
| PB-09 | Sebagai customer, saya ingin memilih pembayaran demo. | Must | VA/transfer/e-wallet tersimpan; bukti tervalidasi. | FR-09 | Done | Checkout payment method, VA simulasi, proof upload | TransactionWorkflowTest |
| PB-10 | Sebagai customer, saya ingin melihat order sendiri. | Must | Index/detail hanya milik user aktif. | FR-10 | Done | `Customer\OrderController`, order views | TransactionWorkflowTest |
| PB-11 | Sebagai customer, saya ingin game terverifikasi masuk library. | Must | Tepat satu library per user/game. | FR-11 | Done | `Admin\PaymentController::verify`, library view | TransactionWorkflowTest |
| PB-12 | Sebagai pemilik game, saya ingin membuat review. | Should | Hanya pemilik; satu review; rating 1–5. | FR-12 | Done | `Customer\ReviewController`, `ReviewRequest` | TransactionWorkflowTest |
| PB-13 | Sebagai admin, saya ingin CRUD genre. | Must | Search, pagination, validasi, flash, proteksi role. | FR-13 | Done | `Admin\GenreController`, `GenreRequest`, views admin | AdminCatalogCrudTest |
| PB-14 | Sebagai admin, saya ingin CRUD publisher. | Must | Search, pagination, validasi, flash, proteksi role. | FR-14 | Done | `Admin\PublisherController`, `PublisherRequest`, views admin | AdminCatalogCrudTest |
| PB-15 | Sebagai admin, saya ingin CRUD developer. | Should | Search, pagination, validasi, flash, proteksi role. | FR-15 | Done | `Admin\DeveloperController`, `DeveloperRequest`, views admin | AdminCatalogCrudTest |
| PB-16 | Sebagai admin, saya ingin CRUD game. | Must | Relasi, harga/diskon, upload, genre, status, featured tervalidasi. | FR-16 | Done | `Admin\GameController`, `GameRequest`, views admin | AdminCatalogCrudTest |
| PB-17 | Sebagai admin, saya ingin memeriksa order. | Must | Search/filter/status dan detail snapshot tersedia. | FR-17 | Done | `Admin\OrderController`, order admin views | TransactionWorkflowTest |
| PB-18 | Sebagai admin, saya ingin verify/reject payment. | Must | Transaction; idempotent; library/cart/order sinkron. | FR-18 | Done | `Admin\PaymentController`, payment admin views | TransactionWorkflowTest |
| PB-19 | Sebagai admin, saya ingin moderasi review. | Should | Pending dapat menjadi published/rejected. | FR-19 | Done | `Admin\ReviewController`, moderation view | TransactionWorkflowTest |
| PB-20 | Sebagai admin, saya ingin melihat dashboard. | Should | Statistik berasal dari query database nyata. | FR-20 | Done | `Admin\DashboardController`, `admin.dashboard` | RoleAuthorizationTest |
| PB-21 | Sebagai penguji, saya ingin stack yang dapat direproduksi. | Must | config/build/up/ps/migrate/seed berhasil. | NFR-01–04 | Done | Compose, Dockerfile, Nginx, `.env.example`, command log | Docker command log |
| PB-22 | Sebagai pengguna keyboard, saya ingin UI aksesibel. | Should | Focus visible, label/form benar, carousel keyboard, reduced motion. | NFR-07, NFR-12 | Done | Skip link, focus state, responsive menu, Swiper A11y, reduced-motion CSS/JS | Browser QA |
| PB-23 | Sebagai maintainer, saya ingin data bertahan setelah restart. | Must | Record uji ditemukan setelah `docker compose restart`. | NFR-09 | Done | Named volume; genre ID 13 `QA Persistence` | Persistence check |
| PB-24 | Sebagai dosen, saya ingin bukti dan laporan yang sesuai implementasi. | Must | Screenshot nyata, dokumentasi, DOCX/PDF tervalidasi. | NFR-10 | Done | 36 screenshot nyata; DOCX target-layout 53 halaman; PDF 48 halaman; indeks statis dan render QA PDF | Evidence checklist, validasi DOCX, dan render PDF |

Prioritas menggunakan Must/Should/Could. Seluruh item berstatus Done berdasarkan bukti implementasi dan pengujian aktual.
