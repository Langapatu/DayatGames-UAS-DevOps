# Skema Database DayatGames

## Prinsip

- Seluruh nominal menggunakan `decimal(15,2)`.
- Foreign key master menggunakan `restrict` atau `nullOnDelete` sesuai kebutuhan sejarah data.
- Penghapusan order dan item transaksi tidak diekspos sebagai operasi harian.
- Pivot dan tabel kepemilikan memakai unique composite untuk mencegah duplikasi.
- `order_items` menyimpan snapshot judul dan harga.
- Validasi rentang yang tidak portabel sebagai constraint database juga ditegakkan pada Form Request dan service.

## Tabel bisnis inti

### users

| Field | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar | required |
| email | varchar | unique |
| password | varchar | hash |
| role | enum(admin, customer) | default customer |
| phone | varchar nullable |  |
| avatar | varchar nullable |  |
| timestamps | timestamp nullable |  |

### developers dan publishers

Kedua tabel memiliki `id`, `name`, `slug` unik, `website` nullable, `description` nullable, dan timestamps. Data yang masih direferensikan game tidak dapat dihapus.

### genres

Memiliki `id`, `name`, `slug` unik, `description` nullable, dan timestamps. Relasi dengan game dikelola melalui `game_genre`.

### games

| Field | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| developer_id | bigint unsigned | FK developers, restrict |
| publisher_id | bigint unsigned | FK publishers, restrict |
| steam_app_id | bigint unsigned nullable | index |
| title | varchar | required |
| slug | varchar | unique |
| short_description | text | required |
| description | longtext | required |
| original_price | decimal(15,2) | min 0 |
| discount_price | decimal(15,2) nullable | min 0, max original |
| discount_percent | tinyint unsigned | 0–100, default 0 |
| price_checked_at | timestamp nullable |  |
| price_source_url | varchar nullable | URL |
| price_is_demo | boolean | default true |
| release_date | date nullable |  |
| platform | varchar | required |
| operating_system | varchar nullable |  |
| cover_image | varchar nullable |  |
| hero_image | varchar nullable |  |
| status | enum(draft, published) | default draft |
| is_featured | boolean | default false |
| timestamps | timestamp nullable |  |

### game_genre

Pivot dengan `game_id` dan `genre_id` sebagai primary key gabungan. Kedua foreign key cascade ketika game atau genre dihapus karena pivot tidak memiliki nilai historis mandiri.

### game_images

Memiliki `id`, `game_id`, `image_path`, `image_type`, `sort_order`, dan timestamps. Gallery cascade saat game dihapus.

### carts dan cart_items

Satu user memiliki satu cart (`carts.user_id` unik). `cart_items` menyimpan `cart_id`, `game_id`, dan snapshot harga sementara. Kombinasi cart/game unik. Cart dan item boleh cascade karena bukan riwayat transaksi.

### wishlists

Memiliki `user_id`, `game_id`, timestamps, serta unique composite. Wishlist boleh cascade saat user/game dihapus.

### orders

Memiliki `user_id`, `order_code` unik, `total_amount`, status `pending|paid|completed|cancelled`, `ordered_at`, dan timestamps. User yang memiliki order tidak boleh dihapus secara fisik.

### order_items

Memiliki `order_id`, `game_id` nullable, `game_title`, `unit_price`, `discount_amount`, `subtotal`, dan timestamps. `game_id` menjadi null jika master game dihapus, sehingga snapshot transaksi tetap utuh.

### payments

Satu order memiliki maksimal satu payment. Field penting mencakup metode, reference, virtual account demo, bukti, amount, status `pending|verified|failed`, waktu pembayaran/verifikasi, dan `verified_by` nullable. Penghapus admin verifier membuat `verified_by` null tanpa menghapus audit payment.

### libraries

Memiliki `user_id`, `game_id`, `order_id`, `purchased_at`, timestamps, dan unique composite user/game. Foreign key order dibatasi agar asal kepemilikan tidak hilang.

### reviews

Memiliki `user_id`, `game_id`, `rating` 1–5, comment nullable, status `pending|published|rejected`, timestamps, dan unique composite user/game.

## Indeks yang direncanakan

- games: status, featured, release date, developer, publisher, steam app ID.
- orders: user/status/ordered_at dan order_code unik.
- payments: status/verified_at dan order_id unik.
- reviews: game/status dan user/game unik.
- library, wishlist, cart item: composite unik untuk idempotensi.

## Validasi pascamigrasi

Skema akan diverifikasi melalui migration status, query `INFORMATION_SCHEMA.TABLES`, `INFORMATION_SCHEMA.KEY_COLUMN_USAGE`, dan tampilan database yang sama pada phpMyAdmin.

