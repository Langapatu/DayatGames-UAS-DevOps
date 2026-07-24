# Spesifikasi Project DayatGames

## 1. Identitas

- Produk: DayatGames
- Slogan: Temukan. Beli. Mainkan.
- Jenis: marketplace game digital untuk simulasi akademik
- Pengguna: guest, customer, dan admin
- Platform: aplikasi web responsif

## 2. Tujuan

DayatGames menyediakan katalog game digital, wishlist, keranjang, checkout, pembayaran simulasi, library kepemilikan, review, serta panel admin. Implementasi digunakan untuk membuktikan praktik Agile, Laravel, basis data relasional, Docker Compose, pengujian, dan version control pada project UAS.

## 3. Batasan

- Aplikasi bersifat fungsional dan bukan landing page.
- Pembayaran merupakan simulasi akademik yang diproses dan disimpan di database, bukan integrasi bank nyata.
- Harga dapat berupa data yang diverifikasi dari sumber resmi atau data demo yang selalu diberi penanda.
- Game yang dijual merupakan satu lisensi digital per customer; tidak ada quantity lebih dari satu.
- Tidak ada pengiriman fisik.
- Source final berjalan pada Docker Compose lokal melalui `http://localhost:8080`.
- phpMyAdmin tersedia pada `http://localhost:8081`.
- Project tidak mengklaim afiliasi dengan Steam atau pemegang merek game.

## 4. Aktor dan hak akses

### Guest

Guest dapat membuka home, katalog published, detail game, search/filter, registrasi, dan login.

### Customer

Customer dapat mengelola profil, wishlist, cart, checkout, pembayaran, riwayat order miliknya, library, serta review game yang sudah dimiliki.

### Admin

Admin dapat membuka dashboard, CRUD games/genres/publishers/developers, melihat customer, mengelola order, memverifikasi atau menolak payment, memoderasi review, dan menentukan featured game.

## 5. Aturan bisnis utama

1. Hanya game berstatus `published` yang terlihat oleh guest dan customer.
2. Harga checkout selalu dihitung ulang dari database oleh server.
3. Satu game hanya dapat muncul sekali dalam cart customer.
4. Customer tidak dapat membeli game yang sudah ada di library.
5. Checkout keranjang kosong ditolak.
6. Pembuatan order, order item, dan payment menggunakan transaksi database.
7. Verifikasi payment menggunakan transaksi database dan bersifat idempotent.
8. Verifikasi berhasil membuat library tanpa duplikat dan membersihkan cart terkait.
9. Customer hanya dapat melihat order miliknya.
10. Review hanya dapat dibuat oleh customer yang memiliki game tersebut.
11. Riwayat order menyimpan snapshot judul dan harga agar tidak berubah saat game diedit.
12. Penghapusan master data tidak boleh menghapus riwayat transaksi penting.

## 6. Arsitektur target

Browser mengakses Nginx melalui host port 8080. Nginx menyajikan file statis dari `public` dan meneruskan request PHP ke service `app` pada port 9000 di Docker network. Laravel terhubung ke MySQL melalui hostname service `db` dan port container 3306. Browser dapat mengakses phpMyAdmin melalui host port 8081; phpMyAdmin juga terhubung ke `db` di network yang sama.

## 7. Kriteria penerimaan produk

- Empat service Compose dapat dijalankan.
- Database `dayatgames` memiliki 15 tabel bisnis inti beserta foreign key yang sesuai ERD.
- Auth dan role membatasi route admin/customer dengan benar.
- CRUD games, genres, publishers, dan developers dapat digunakan.
- Katalog, search/filter, wishlist, cart, checkout, payment, library, dan review berfungsi.
- Test alur kritis lulus atau kegagalan tersisa didokumentasikan jujur.
- Frontend responsif, keyboard-friendly, dan menghormati `prefers-reduced-motion`.
- Build asset Vite berhasil.
- Data MySQL tetap ada setelah restart container.
- Dokumentasi teknis, bukti nyata, Git log, DOCX, dan PDF tersedia.

## 8. Disclaimer

DayatGames merupakan aplikasi akademik untuk keperluan pembelajaran. Nama game, merek, dan aset terkait merupakan milik pemegang hak masing-masing. Harga yang ditampilkan merupakan data demonstrasi dan dapat berbeda dari harga toko resmi.

