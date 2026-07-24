# Catatan Laporan

## Fakta akademik

- Nama: Galang Rispa'i
- NPM: 237006516020
- Program Studi: Sistem Informasi
- Fakultas: Fakultas Teknologi Komunikasi dan Informatika
- Universitas: Universitas Nasional
- Kelas: R.02
- Mata Kuliah: DevOps dan Pengembangan Agile
- Dosen: Lili Dwi Yulianto, S.Kom., M.Kom.

## Judul

IMPLEMENTASI DEVOPS DAN PENGEMBANGAN AGILE PADA APLIKASI PENJUALAN GAME DIGITAL DAYATGAMES BERBASIS LARAVEL DAN DOCKER

## Fakta implementasi terverifikasi

- Project Docker lama ditemukan melalui metadata di `C:\Coding\laravel-docker` dan tidak diubah.
- Project final terpisah pada `C:\.Kuliah\TugasMatkul\DevOPS\UAS\DayatGames`, branch `feature/dayatgames-uas`.
- Docker Compose memiliki app, webserver, db, phpmyadmin; aplikasi HTTP 200 pada 8080, phpMyAdmin HTTP 200 pada 8081, MySQL host 3306, dan db healthy.
- MySQL memakai named volume serta `DB_HOST=db`; migration menghasilkan 15 tabel bisnis dan 20 foreign key.
- Seeder idempotent menghasilkan admin/customer dan 17 game dari aset pengguna; 15 harga Steam IDR terverifikasi pada 24 Juli 2026 dan dua harga berstatus demo.
- Auth/role, CRUD, katalog, wishlist, cart, checkout, payment, admin verify/reject, library, review, dan moderasi selesai.
- Browser MySQL nyata menghasilkan order ID 1, payment verified, library Atomic Heart, dan review published.
- Regresi terakhir: 26 test, 145 assertion; npm build berhasil; npm audit 0 vulnerability.
- Uji restart: genre ID 13 `QA Persistence` tetap ditemukan setelah MySQL kembali healthy.
- 31 screenshot browser/phpMyAdmin nyata tersedia; lima screenshot editor/terminal masih ditandai Manual dan tidak disintesis.
- Git lokal berisi commit bertahap; URL GitHub belum tersedia sampai push dilakukan.

## Kendala nyata

- Dependency Pail membutuhkan composer dev dependency di image UAS.
- Remote font membuat build tidak deterministik dan dihapus.
- Cast harga `NUMERIC` tidak kompatibel dengan MySQL; diganti `DECIMAL(15,2)`.
- Windows `public/storage` reparse point sempat membuat Docker build gagal; dikecualikan dari build context.
- Query pertama setelah restart terjadi sebelum MySQL healthy; verifikasi diulang setelah healthcheck.

## Larangan klaim

- Jangan mencantumkan URL repository sampai push nyata berhasil.
- Jangan menyebut lima screenshot manual sudah tersedia.
- Concept board UI bukan screenshot aplikasi.
- Payment bukan integrasi bank sungguhan.
