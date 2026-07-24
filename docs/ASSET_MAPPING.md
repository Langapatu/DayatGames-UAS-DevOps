# Asset Mapping DayatGames

Tanggal pemeriksaan: 24 Juli 2026

Seluruh file pada folder sumber hanya dibaca. Salinan final dikonversi ke WebP tanpa memperbesar gambar kecil dan disimpan pada `public/images/games`. Logo asli disalin/dioptimasi menjadi `public/images/brand/dayatgames-logo.png`; turunan favicon berada di `public/favicon.png`.

| File asli | Format aktual | Resolusi | Rasio | Game dikenali | File final | Kegunaan |
|---|---|---:|---:|---|---|---|
| `7aFAQ28D5avzogf7TFg11ryH.jpg` | WebP | 1024×1024 | 1:1 | Death Stranding Director’s Cut | `death-stranding-directors-cut.webp` | cover/hero fallback |
| `9e09677388a2f815ab02fe08cc918d945b822d4041a55023.jpg` | AVIF/HEIF | 1024×1024 | 1:1 | Forza Horizon 6 | `forza-horizon-6.webp` | cover/hero fallback |
| `Atomic_Heart_cover.jpg` | JPEG | 258×387 | 2:3 | Atomic Heart | `atomic-heart.webp` | cover |
| `EGS_GodofWar_...jpg` | JPEG | 1200×1600 | 3:4 | God of War | `god-of-war.webp` | cover/hero fallback |
| `Ghost_of_Tsushima.jpg` | JPEG | 250×334 | 3:4 | Ghost of Tsushima | `ghost-of-tsushima.webp` | cover |
| `It_Takes_Two_cover_art.jpg` | PNG | 280×356 | 4:5 | It Takes Two | `it-takes-two.webp` | cover |
| `K6mmm89oNII1iI1aqaClO0wh.jpg` | AVIF/HEIF | 1024×1024 | 1:1 | Grand Theft Auto V | `grand-theft-auto-v.webp` | cover/hero fallback |
| `Kingdom_Come_Deliverance_II.jpg` | JPEG | 273×365 | 3:4 | Kingdom Come: Deliverance II | `kingdom-come-deliverance-ii.webp` | cover |
| `Mafia_The_Old_Country_cover_art.jpg` | JPEG | 285×350 | 4:5 | Mafia: The Old Country | `mafia-the-old-country.webp` | cover |
| `RDR2.jpg` | JPEG | 312×312 | 1:1 | Red Dead Redemption 2 | `red-dead-redemption-2.webp` | cover/hero fallback |
| `Road_96_cover.jpg` | JPEG | 258×387 | 2:3 | Road 96 | `road-96.webp` | cover |
| `Sons_of_the_Forest.jpg` | JPEG | 259×388 | 2:3 | Sons of the Forest | `sons-of-the-forest.webp` | cover |
| `co2228_7_.jpg` | WebP | 500×706 | 5:7 | Detroit: Become Human | `detroit-become-human.webp` | cover |
| `d0afe358...jpg` | AVIF/HEIF | 1024×1024 | 1:1 | Ghost of Yōtei | `ghost-of-yotei.webp` | cover/hero fallback |
| `dinner-1hsix.jpg` | JPEG | 1200×1600 | 3:4 | LEGO Batman: Legacy of the Dark Knight | `lego-batman-legacy-of-the-dark-knight.webp` | cover/hero fallback |
| `f6b1e451...jpg` | AVIF/HEIF | 1440×2160 | 2:3 | Hogwarts Legacy | `hogwarts-legacy.webp` | cover |
| `images.jpg` | JPEG | 447×447 | 1:1 | Subnautica 2 | `subnautica-2.webp` | cover/hero fallback |

## Catatan teknis

- Beberapa file memakai ekstensi `.jpg` meskipun isi aktualnya WebP, AVIF/HEIF, atau PNG.
- Semua file final dinormalisasi ke WebP quality 86 agar format dan ekstensi konsisten.
- Gambar persegi dipakai sebagai cover dengan crop CSS; file sumber tidak dipotong.
- Karena tidak tersedia aset landscape terpisah, `hero_image` sementara menunjuk salinan cover yang sama dan ditampilkan dengan `object-fit: cover`.
- Identitas seluruh game di atas dapat dibaca dari judul yang tampak pada gambar atau nama file; tidak ada cover pengganti yang diunduh.

