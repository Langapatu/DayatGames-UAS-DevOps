# UI Design DayatGames

## Arah visual

Interface menggunakan latar navy-hitam, panel slate, aksen cyan yang mengikuti logo, dan violet sebagai warna CTA sekunder. Struktur tetap original: hero featured, carousel keyboard/touch, grid katalog, detail dua kolom, serta halaman admin yang lebih padat.

Concept board tersedia pada `docs/design/dayatgames-ui-concept.png`. Gambar tersebut dibuat sebagai referensi desain, bukan screenshot atau bukti aplikasi berjalan.

Prompt ringkas concept board: high-fidelity desktop homepage DayatGames dengan logo sebagai referensi yang dipertahankan, hero sinematik, featured rail, game discount grid, navy/cyan palette, kontras aksesibel, dan struktur yang dapat diimplementasikan dengan Blade/Tailwind tanpa WebGL.

## Motion dan progressive enhancement

- GSAP + ScrollTrigger: hero reveal, parallax ringan, section reveal, dan stagger card.
- Lenis: smooth wheel hanya ketika pengguna tidak mengaktifkan reduced motion.
- Swiper: carousel featured dengan tombol, keyboard, touch, dan informasi A11y.
- CSS: card lift memakai `transform`, cover zoom, focus ring, sticky navbar blur, serta flash dismissal.
- Tanpa JavaScript, link, form, CRUD, dan konten tetap tersedia; carousel tetap berupa susunan konten HTML.

## Aksesibilitas dan responsivitas

- Skip link menuju konten utama.
- Label dan semantic heading dipertahankan.
- Focus state cyan dengan kontras tinggi.
- Menu mobile memakai `aria-expanded`.
- `prefers-reduced-motion: reduce` menonaktifkan smooth scroll, parallax, dan animasi besar.
- Pemeriksaan browser pada 390×844 memastikan menu dapat dibuka, tidak ada horizontal overflow, dan carousel dapat digerakkan.

## Catatan aset

Logo berasal dari aset pengguna dan tidak didesain ulang. Cover/hero memakai hasil optimasi aset `ImageGames`; concept board memakai thumbnail generik hanya sebagai referensi visual.
