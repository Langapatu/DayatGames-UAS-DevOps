# Functional dan Non-Functional Requirements

## Functional Requirements

| ID | Kebutuhan | Acceptance criteria |
|---|---|---|
| FR-01 | Registrasi | Guest dapat membuat akun customer dengan nama, email unik, dan password tervalidasi. |
| FR-02 | Login/logout | Pengguna dapat login dengan kredensial benar, logout, dan diarahkan sesuai role. |
| FR-03 | Katalog | Guest/customer hanya melihat game published dengan pagination. |
| FR-04 | Search/filter | Katalog dapat dicari berdasarkan judul dan difilter genre/rentang harga. |
| FR-05 | Detail game | Detail menampilkan metadata, harga, gallery, review published, dan related games. |
| FR-06 | Wishlist | Customer dapat menambah/menghapus game tanpa duplikat. |
| FR-07 | Cart | Customer dapat menambah/menghapus game published yang belum dimiliki tanpa duplikat. |
| FR-08 | Checkout | Checkout menghitung harga server-side dan membuat order, item, serta payment atomik. |
| FR-09 | Pembayaran | Customer memilih metode demo dan dapat mencatat referensi/bukti sesuai metode. |
| FR-10 | Riwayat order | Customer hanya dapat melihat daftar dan detail order miliknya. |
| FR-11 | Library | Game dari payment verified masuk library tepat satu kali. |
| FR-12 | Review | Pemilik game dapat membuat satu review; admin dapat memoderasinya. |
| FR-13 | CRUD genre | Admin dapat create, read, update, delete genre dengan validasi. |
| FR-14 | CRUD publisher | Admin dapat create, read, update, delete publisher dengan validasi. |
| FR-15 | CRUD developer | Admin dapat create, read, update, delete developer dengan validasi. |
| FR-16 | CRUD game | Admin dapat CRUD game, upload gambar, memilih genre, status, dan featured. |
| FR-17 | Kelola order | Admin dapat mencari, memeriksa, dan memperbarui order sesuai transisi valid. |
| FR-18 | Verifikasi payment | Admin dapat verify/reject; verify idempotent membuat library dan menyelesaikan order. |
| FR-19 | Moderasi review | Admin dapat mengubah review menjadi published atau rejected. |
| FR-20 | Dashboard admin | Admin melihat ringkasan game, customer, order, payment, pendapatan, dan item terbaru. |

## Non-Functional Requirements

| ID | Kebutuhan | Acceptance criteria |
|---|---|---|
| NFR-01 | Docker Compose | Project dijalankan oleh satu `compose.yaml` dengan empat service target. |
| NFR-02 | Laravel + MySQL | Logika aplikasi memakai Laravel dan data bisnis tersimpan di MySQL 8. |
| NFR-03 | Nginx + PHP-FPM | Nginx melayani HTTP dan meneruskan PHP ke PHP-FPM service `app`. |
| NFR-04 | phpMyAdmin | Database dapat diperiksa melalui host port 8081. |
| NFR-05 | Auth/otorisasi | Route dilindungi middleware auth/role dan resource ownership. |
| NFR-06 | Security baseline | Password di-hash, form memakai CSRF, input/upload tervalidasi, rahasia tidak di-Git. |
| NFR-07 | Responsive | Customer dan admin dapat digunakan pada desktop serta mobile modern. |
| NFR-08 | Performa animasi | Animasi memakai transform/opacity, lazy loading, dan tidak menyebabkan overflow. |
| NFR-09 | Persistensi | MySQL memakai named volume dan data bertahan setelah restart. |
| NFR-10 | Git bertahap | Implementasi dicatat dalam commit terpisah per fase/fitur. |
| NFR-11 | Browser modern | Fitur inti berjalan pada Chromium modern dengan progressive enhancement. |
| NFR-12 | Reduced motion | Smooth scroll/parallax/animasi besar dinonaktifkan saat reduced motion aktif. |

## Definisi selesai per requirement

Sebuah requirement hanya berstatus selesai bila kode, route/view, tabel yang relevan, dan test atau bukti manual aktual tersedia. Status dokumen tidak menggantikan pengujian.

