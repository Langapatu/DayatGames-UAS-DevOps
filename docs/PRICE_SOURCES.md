# Sumber Harga Game

Pemeriksaan dilakukan pada 24 Juli 2026 melalui endpoint resmi Steam Storefront dengan parameter negara `cc=id` dan bahasa `l=indonesian`. Nilai integer API dibagi 100 sesuai unit minor IDR. Harga dapat berubah setelah waktu pemeriksaan.

| Game | Steam App ID | Harga normal | Harga saat diperiksa | Diskon | Status | Sumber/catatan |
|---|---:|---:|---:|---:|---|---|
| Death Stranding Director’s Cut | 1850570 | Rp539.999 | Rp539.999 | 0% | Terverifikasi | https://store.steampowered.com/app/1850570 |
| Forza Horizon 6 | 2483190 | Rp899.000 | Rp899.000 | 0% | Terverifikasi | https://store.steampowered.com/app/2483190 |
| Atomic Heart | 668580 | Rp549.000 | Rp137.250 | 75% | Terverifikasi | https://store.steampowered.com/app/668580 |
| God of War | 1593500 | Rp729.000 | Rp729.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1593500 |
| Ghost of Tsushima Director’s Cut | 2215430 | Rp879.000 | Rp879.000 | 0% | Terverifikasi | https://store.steampowered.com/app/2215430 |
| It Takes Two | 1426210 | Rp479.000 | Rp479.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1426210 |
| Grand Theft Auto V Enhanced | 3240220 | Rp439.000 | Rp219.500 | 50% | Terverifikasi | https://store.steampowered.com/app/3240220 |
| Kingdom Come: Deliverance II | 1771300 | Rp641.000 | Rp641.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1771300 |
| Mafia: The Old Country | 1941540 | Rp570.000 | Rp570.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1941540 |
| Red Dead Redemption 2 | 1174180 | Rp879.000 | Rp879.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1174180 |
| Road 96 | 1466640 | Rp214.000 | Rp214.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1466640 |
| Sons of the Forest | 1326470 | Rp245.999 | Rp245.999 | 0% | Terverifikasi | https://store.steampowered.com/app/1326470 |
| Detroit: Become Human | 1222140 | Rp399.000 | Rp399.000 | 0% | Terverifikasi | https://store.steampowered.com/app/1222140 |
| Ghost of Yōtei | — | Rp879.000 | Rp879.000 | 0% | Demo | Tidak ditemukan halaman Steam PC yang dapat diverifikasi pada pemeriksaan; tidak diklaim sebagai harga resmi. |
| LEGO Batman: Legacy of the Dark Knight | 2215200 | Rp649.000 | Rp649.000 | 0% | Demo | Metadata Steam terverifikasi, tetapi API wilayah Indonesia tidak mengembalikan `price_overview`; nilai di aplikasi tetap demo. |
| Hogwarts Legacy | 990080 | Rp799.000 | Rp799.000 | 0% | Terverifikasi | https://store.steampowered.com/app/990080 |
| Subnautica 2 | 1962700 | Rp350.999 | Rp350.999 | 0% | Terverifikasi | https://store.steampowered.com/app/1962700 |

## Implementasi

- Sebanyak 15 record memakai `price_is_demo=false`, `price_checked_at`, `price_source_url`, dan Steam App ID.
- Ghost of Yōtei serta LEGO Batman memakai `price_is_demo=true`.
- Tidak ada konversi USD ke IDR buatan.
- Harga diskon disimpan terpisah dari harga normal agar tampilan dan riwayat checkout dapat membedakannya.

## Disclaimer

DayatGames merupakan aplikasi akademik untuk keperluan pembelajaran. Nama game, merek, dan aset terkait merupakan milik pemegang hak masing-masing. Harga yang ditampilkan merupakan data demonstrasi dan dapat berbeda dari harga toko resmi.

