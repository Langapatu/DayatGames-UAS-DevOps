# Inspeksi Project Docker Lama

Tanggal inspeksi: 24 Juli 2026

## Lokasi terverifikasi

- Working directory Compose: `C:\Coding\laravel-docker`
- File Compose: `C:\Coding\laravel-docker\compose.yaml`
- Source Laravel: `C:\Coding\laravel-docker\src`
- Konfigurasi Nginx aktif: `C:\Coding\laravel-docker\docker\nginx\default.conf`

Lokasi tersebut divalidasi dari label `com.docker.compose.project.working_dir`, label `com.docker.compose.project.config_files`, dan `Mounts.Source` hasil `docker inspect`.

## Container lama

| Container | Image | Kondisi saat inspeksi | Mount penting |
|---|---|---|---|
| `laravel_app` | `laravel-docker-app` | Exited (0) | `C:\Coding\laravel-docker\src` ke `/var/www` |
| `laravel_nginx` | `nginx:alpine` | Exited (0) | source ke `/var/www`; Nginx config ke `/etc/nginx/conf.d/default.conf` |
| `laravel_mysql` | `mysql:8.0` | Exited (137) | named volume ke `/var/lib/mysql` |
| `laravel_phpmyadmin` | `phpmyadmin:latest` | Exited (137) | tidak ada bind mount source |

Tidak ada container lama yang dihapus, dimulai, atau dimodifikasi selama inspeksi.

## Temuan konfigurasi

- Service Compose lama sudah memakai pola `app`, `webserver`, `db`, dan `phpmyadmin`.
- Port lama sudah menggunakan `8080:80`, `3306:3306`, dan `8081:80`.
- Nginx sudah memakai document root Laravel `/var/www/public`.
- FastCGI diarahkan ke `app:9000`.
- Database lama bernama `laravel`; kredensial ini tidak dipakai untuk database final DayatGames.
- Compose lama belum memiliki healthcheck database dan kondisi `depends_on` berbasis kesehatan.
- Dockerfile lama menjalankan `composer install` pada saat build, tetapi bind mount development dapat menutupi hasil tersebut.

## Keputusan migrasi

Struktur framework, Dockerfile, Compose, dan konfigurasi Nginx disalin sebagai baseline ke workspace final. Konfigurasi database akan diganti menjadi database terisolasi `dayatgames`, Compose akan diperkuat dengan healthcheck, dan source aplikasi akan dikembangkan hanya pada workspace final. `.env` serta `vendor` lama sengaja tidak disalin.

