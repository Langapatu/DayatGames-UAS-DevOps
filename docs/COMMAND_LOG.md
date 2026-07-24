# Command Log DayatGames

Catatan ini merangkum perintah penting dan hasil aktual. Password atau rahasia tidak dicatat.

## 24 Juli 2026 — Fase 0

| Perintah | Hasil |
|---|---|
| `docker version` | Berhasil; Docker Desktop 4.81.0 dan Engine 29.6.1 aktif. |
| `docker ps -a` | Menemukan empat container Laravel lama; seluruhnya berhenti sejak sekitar sembilan hari sebelumnya. |
| `docker inspect laravel_app laravel_nginx laravel_mysql laravel_phpmyadmin` | Label working directory dan config menunjuk ke `C:\Coding\laravel-docker`; bind mount source menunjuk ke `C:\Coding\laravel-docker\src`. |
| Pemeriksaan `compose.yaml`, `Dockerfile`, Nginx, dan source lama | Baseline Laravel 13.8/PHP 8.4 ditemukan; Nginx root menunjuk ke `/var/www/public`. |
| Pemeriksaan Git folder lama dan folder induk UAS | Keduanya bukan repository Git. |
| Penyalinan source relevan ke folder final | Berhasil; `.env` dan `vendor` tidak disalin. |
| `git init` dan `git switch -c feature/dayatgames-uas` | Repository lokal dan branch kerja berhasil dibuat. |

