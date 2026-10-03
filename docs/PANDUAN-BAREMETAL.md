# Panduan Instalasi & Deployment Baremetal / Dedicated Host

Panduan ini menjelaskan langkah demi langkah instalasi dan deployment aplikasi **VIWB** pada server fisik (*baremetal*), *Dedicated Server*, maupun *Virtual Private Server* (VPS) berbasis Linux (disarankan **Ubuntu 22.04 / 24.04 LTS** atau **Debian 12 Bookworm**) tanpa menggunakan Docker.

---

## 1. Spesifikasi Minimum Server

| Komponen | Spesifikasi Minimum | Rekomendasi Production |
|---|---|---|
| **CPU** | 1 Core (vCPU) | 2 Core atau lebih |
| **RAM** | 1 GB (dengan swap) | 2 GB - 4 GB |
| **Storage** | 10 GB SSD | 20 GB+ NVMe SSD |
| **Sistem Operasi** | Ubuntu 22.04 / 24.04 LTS atau Debian 12 | Ubuntu 24.04 LTS |

---

## 2. Persiapan Sistem & Instalasi Paket Prasyarat

Masuk ke server sebagai pengguna `root` atau pengguna dengan hak akses `sudo`:

### 2.1 Update Repositori Sistem
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip zip rsync certbot python3-certbot-nginx supervisor
```

### 2.2 Install PHP 8.3 & Ekstensi yang Dibutuhkan
Aplikasi VIWB membutuhkan **PHP 8.3** dan modul-modul berikut:

```bash
# Tambahkan repositori PPA Ondřej Surý (untuk Ubuntu)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 FPM & modul pendukung
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath \
    php8.3-curl php8.3-gd php8.3-intl php8.3-zip php8.3-opcache
```

Pastikan instalasi PHP berhasil:
```bash
php -v
# Menampilkan: PHP 8.3.x ...
```

### 2.3 Install Composer 2
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 2.4 Install Node.js 22 LTS & NPM
Dibutuhkan untuk melakukan kompilasi aset frontend (Vite + Vue 3 + Tailwind CSS v4):

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

### 2.5 Install & Konfigurasi Web Server (Nginx)
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 2.6 Install Database Server (MariaDB / MySQL)
```bash
sudo apt install -y mariadb-server mariadb-client
sudo systemctl enable mariadb
sudo systemctl start mariadb

# Amankan instalasi database
sudo mysql_secure_installation
```

Buat database dan pengguna khusus untuk aplikasi:
```bash
sudo mariadb -u root -p
```
Jalankan query SQL berikut di dalam prompt MariaDB/MySQL:
```sql
CREATE DATABASE `web-viwb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'user'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON `web-viwb`.* TO 'user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Deployment Source Code Aplikasi

### 3.1 Clone / Pindahkan Direktori Project
Tempatkan project pada direktori standar `/var/www/viwb`:

```bash
sudo mkdir -p /var/www/viwb
# Clone repository resmi VIWB
sudo git clone https://github.com/projectsonlyindra/web-viwb.git /var/www/viwb

# Pindah ke direktori proyek Laravel
cd /var/www/viwb
```

### 3.2 Salin & Konfigurasi Environment (`.env`)
```bash
cp .env.example .env
nano .env
```

Sesuaikan parameter environment penting berikut:
```dotenv
APP_NAME=VIWB
APP_ENV=production
APP_DEBUG=false
APP_URL=https://viwb.domainanda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web-viwb
DB_USERNAME=user
DB_PASSWORD=GANTI_DENGAN_PASSWORD_KUAT

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=file

# WhatsApp WAHA Gateway
WAHA_DUMMY_MODE=false
WAHA_BASE_URL=http://127.0.0.1:3000
WAHA_SESSION=default
```

---

## 4. Instalasi Dependensi & Build Aset

Jalankan perintah berikut di dalam `/var/www/viwb`:

### 4.1 Install Composer Dependencies (Production Mode)
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### 4.2 Generate Application Key
```bash
php artisan key:generate --force
```

### 4.3 Install Node Modules & Build Aset Frontend
```bash
npm ci
npm run build
```

### 4.4 Jalankan Migrasi & Database Seeder
```bash
# Jalankan migrasi tabel
php artisan migrate --force

# Seed akun admin awal & konfigurasi default
php artisan db:seed --force
```

Akun default yang terbentuk:
- **Super Admin:** `superadmin@viwb.test` / password: `password`
- **Ketua RT:** `ketuart@viwb.test` / password: `password`
- **Bendahara:** `bendahara@viwb.test` / password: `password`
- **Warga Test:** `warga@viwb.test` / password: `password`

*(Wajib ubah password akun default setelah login pertama kali di menu Profile).*

---

## 5. Pengaturan Hak Akses & Storage Link

Nginx dan PHP-FPM berjalan menggunakan user `www-data`. Berikan hak akses kepemilikan yang tepat:

```bash
cd /var/www/viwb

# Buat symbolic link storage public untuk bukti transfer & foto
php artisan storage:link

# Atur kepemilikan user web server
sudo chown -R www-data:www-data /var/www/viwb

# Berikan izin tulis khusus untuk direktori storage & bootstrap cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 6. Konfigurasi Nginx Web Server

Buat file konfigurasi Virtual Host Nginx baru:
```bash
sudo nano /etc/nginx/sites-available/viwb.conf
```

Tempelkan konfigurasi berikut (sesuaikan `server_name` dan path socket PHP-FPM):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name viwb.domainanda.com;

    root /var/www/viwb/public;
    index index.php index.html;

    charset utf-8;

    # Batas ukuran upload bukti bayar
    client_max_body_size 10M;

    # Logging
    access_log /var/log/nginx/viwb_access.log;
    error_log /var/log/nginx/viwb_error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Eksekusi PHP melalui PHP-FPM socket
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Blokir akses ke file tersembunyi (.env, .git, dll)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Caching aset statis (CSS, JS, gambar)
    location ~* \.(css|js|jpg|jpeg|png|gif|svg|ico|webp|woff|woff2|ttf)$ {
        expires 14d;
        access_log off;
        add_header Cache-Control "public, no-transform";
    }
}
```

Aktifkan konfigurasi dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/viwb.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 7. Konfigurasi Background Scheduler & Queue Worker

Aplikasi VIWB membutuhkan pemrosesan background rutin:
1. **Cron Scheduler:** Menjalankan `broadcast:process` setiap menit untuk antrean WhatsApp.
2. **Supervisor Daemon:** Memastikan antrean atau scheduler selalu aktif di background.

### Opsi A: Menggunakan Linux Cron (Sederhana & Direkomendasikan)
Buka crontab user `www-data`:
```bash
sudo crontab -u www-data -e
```

Tambahkan baris berikut di baris paling bawah:
```cron
* * * * * cd /var/www/viwb && php artisan schedule:run >> /dev/null 2>&1
```

### Opsi B: Menggunakan Supervisor Daemon
Jika Anda ingin worker scheduler selalu berjalan secara *real-time* (identik dengan arsitektur container):

Buat file `/etc/supervisor/conf.d/viwb-scheduler.conf`:
```ini
[program:viwb-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/viwb/artisan schedule:work
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/viwb-scheduler.log
```

Muat dan jalankan supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start viwb-scheduler:*
```

---

## 8. Optimasi Performa Production

Sebelum membuka akses ke publik, jalankan serangkaian cache Laravel untuk performa optimal:

```bash
cd /var/www/viwb

# Cache konfigurasi, rute, dan view blade
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

*(Catatan: Jika ada perubahan pada file `.env` atau rute di kemudian hari, jalankan `php artisan optimize:clear` lalu jalankan kembali perintah di atas).*

---

## 9. Pengamanan SSL / HTTPS (Let's Encrypt)

Gunakan Certbot untuk mengonfigurasi sertifikat SSL otomatis dan redirect HTTPS:

```bash
sudo certbot --nginx -d viwb.domainanda.com
```

Certbot akan otomatis memperbarui konfigurasi Nginx dan mengatur auto-renewal berkala.

---

## 10. Verifikasi & Pengujian Instalasi

Jalankan test suite langsung di server untuk memastikan seluruh dependensi dan driver database berfungsi dengan sempurna:

```bash
cd /var/www/viwb
php artisan test
```

Jika seluruh **62 tests** berstatus `PASS`, instalasi di baremetal/dedicated host telah sukses 100% dan aplikasi siap digunakan.
