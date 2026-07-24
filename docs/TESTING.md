# Pengujian DayatGames

Pembaruan: 24 Juli 2026, Asia/Jakarta.

## Otomatisasi

Perintah final:

```powershell
docker compose exec -T app php artisan test
npm run build
npm audit --audit-level=high
```

Hasil terakhir sebelum dokumentasi laporan:

- PHPUnit: 26 test lulus, 148 assertion.
- Vite: 44 module ditransformasi, build berhasil.
- npm audit: 0 vulnerability.
- Blade: `php artisan view:cache` berhasil.

## Matriks hasil aktual

| Fitur | Langkah | Hasil diharapkan | Hasil aktual | Status | Bukti |
|---|---|---|---|---|---|
| Katalog published | Buat game published dan draft; buka katalog | Hanya published tampil | Published tampil, draft tidak; detail draft 404 | Lulus | `CustomerMarketplaceTest`, 17-katalog-customer.png |
| Search/filter | Filter judul, genre, harga | Hasil sesuai dan query dipertahankan | Kombinasi filter lulus pada SQLite dan endpoint MySQL HTTP 200 | Lulus | `CustomerMarketplaceTest`, 19-search-filter.png |
| Auth/role | Akses admin sebagai guest/customer/admin | Redirect, 403, dan 200 sesuai role | Sesuai | Lulus | `AuthenticationTest`, `RoleAuthorizationTest` |
| CRUD genre/publisher/developer/game | Create, update, delete sebagai admin | Data/relasi berubah dan validasi bekerja | 4 test, 29 assertion | Lulus | `AdminCatalogCrudTest`, screenshot 09–16 |
| Wishlist | Add dua kali lalu remove | Satu record lalu terhapus | Sesuai unique constraint | Lulus | `CustomerMarketplaceTest`, 20-wishlist.png |
| Cart | Add dua kali, remove, coba game dimiliki | Tidak duplikat; owned ditolak | Sesuai | Lulus | `CustomerMarketplaceTest`, 21-cart.png |
| Checkout kosong | POST tanpa cart | Ditolak | Validation error `cart` | Lulus | `TransactionWorkflowTest` |
| Checkout dan harga | Kirim `price=1` dengan game Rp75.000 | Server memakai harga database dan snapshot | Total/subtotal Rp75.000; unit Rp100.000; diskon Rp25.000 | Lulus | `TransactionWorkflowTest`, 22-checkout.png |
| Otorisasi order | Customer lain membuka order | Tidak boleh membaca | HTTP 404 | Lulus | `TransactionWorkflowTest` |
| Verify payment | Admin verify dua kali | Library satu; order complete; cart item order hilang | Idempotent; library count tetap satu | Lulus | `TransactionWorkflowTest`, 25-payment-verified.png |
| Reject payment | Admin reject pending | Payment failed, order cancelled, library kosong | Sesuai | Lulus | `TransactionWorkflowTest` |
| Review | Non-owner dan owner mencoba review | Non-owner 403; owner satu review | Sesuai; moderasi published lulus | Lulus | `TransactionWorkflowTest`, screenshot 27 |
| Browser flow MySQL | Login customer, cart, checkout VA, admin verify, library, review, moderation | Alur end-to-end tersimpan pada DB nyata | Order ID 1 dan seluruh transisi berhasil tanpa console error | Lulus | screenshot 21–27, 30b |
| Persistensi volume | Buat genre ID 13; restart seluruh Compose; baca ulang | Record tetap tersedia | `QA Persistence` ditemukan setelah MySQL healthy | Lulus | 32-data-after-restart.png |
| Service HTTP | Buka app, katalog, login, phpMyAdmin setelah restart | HTTP 200 | Keempat endpoint HTTP 200 | Lulus | `COMMAND_LOG.md` |

## Struktur suite

- `AuthenticationTest`: registrasi, login, password salah, logout.
- `RoleAuthorizationTest`: akses dashboard berdasarkan role.
- `DatabaseSchemaTest`: 15 tabel inti, kolom, model, dan relasi.
- `AdminCatalogCrudTest`: CRUD master/game serta validasi diskon.
- `CustomerMarketplaceTest`: published catalog, filter, wishlist, badge cart, cart, owned game.
- `TransactionWorkflowTest`: checkout, snapshot harga, authorization, payment, library, review.
