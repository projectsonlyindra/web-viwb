# VIWB - Sistem Manajemen Administrasi Perumahan

VIWB adalah aplikasi berbasis web untuk tata kelola administrasi perumahan: data warga, tagihan iuran berkala (keamanan, kebersihan, HIPPAM, paguyuban), denda keterlambatan otomatis, verifikasi pembayaran, pembukuan kas/pengeluaran dengan sistem persetujuan bertingkat, dan pengumuman warga melalui WhatsApp.

---

## Ringkasan Stack

- **Backend:** Laravel 13 (PHP 8.3)
- **Frontend:** Inertia.js + Vue 3 (Composition API) + Tailwind CSS + shadcn-vue
- **Database:** MariaDB / MySQL
- **Infrastruktur:** Docker & Docker Compose / Baremetal
- **Repository:** `https://github.com/projectsonlyindra/web-viwb`

---

## Cara Menjalankan

### Opsi 1: Menjalankan dengan Docker

1. Clone repository:
   ```bash
   git clone https://github.com/projectsonlyindra/web-viwb.git
   cd web-viwb
   ```
2. Salin template environment, lalu isi password database dengan nilai yang kuat
   (`MYSQL_ROOT_PASSWORD`, `MYSQL_PASSWORD`, dan `DB_PASSWORD` yang sama dengan `MYSQL_PASSWORD`).
   Untuk production, set juga `APP_ENV=production` dan `APP_DEBUG=false`:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Default database: nama `web-viwb`, user `user`.
3. Jalankan container menggunakan Docker Compose:
   ```bash
   docker compose up -d
   ```
4. Akses aplikasi melalui browser:
   ```
   http://localhost
   ```

### Opsi 2: Menjalankan di Baremetal / Dedicated Host

Panduan lengkap instalasi tanpa Docker (Nginx, PHP 8.3-FPM, MariaDB, Node.js 22):
- [Panduan Instalasi Baremetal](docs/PANDUAN-BAREMETAL.md)

---

## Pengujian (Testing)

Jalankan seluruh rangkaian automated tests dari checkout development
(container production memakai `composer install --no-dev`, sehingga PHPUnit tidak terpasang di dalamnya):
```bash
composer install
php artisan test
```

---

## Dokumentasi

Detail panduan penggunaan, deployment, dan arsitektur sistem dapat dibaca pada folder `docs/`:
- [Panduan Penggunaan](docs/PANDUAN-PENGGUNAAN.md)
- [Panduan Deployment & SSL Produksi](docs/PANDUAN-DEPLOYMENT-DAN-SSL.md)
- [Panduan Instalasi Baremetal / Dedicated Host](docs/PANDUAN-BAREMETAL.md)
- [Arsitektur & Spesifikasi Sistem](docs/VIWB-ARCHITECTURE.md)
