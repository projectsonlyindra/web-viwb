# Panduan Deployment & Konfigurasi HTTPS (TLS)

Dokumen ini menyediakan panduan konfigurasi HTTPS untuk sistem **VIWB Web** di lingkungan production.

---

## 1. Arsitektur Reverse Proxy & Terminasi TLS

Container `web-viwb` dirancang dalam pola *all-in-one* yang mendengarkan HTTP port 80 secara internal. Pada lingkungan production publik, seluruh lalu lintas internet wajib diamankan dengan protokol HTTPS (port 443) menggunakan reverse proxy eksternal yang menangani terminasi TLS (*SSL Termination*).

Laravel dan Nginx di dalam container telah dilengkapi dengan:
- `$middleware->trustProxies(at: '*')` pada `bootstrap/app.php` untuk mengenali header `X-Forwarded-Proto: https` secara otomatis.
- Pass-through header `HTTP_X_FORWARDED_PROTO` dan `HTTP_X_FORWARDED_FOR` di konfigurasi Nginx FastCGI.

---

## 2. Opsi A: Deployment dengan Caddy (Rekomendasi - Otomatis Let's Encrypt)

Caddy adalah web server modern yang mengelola penerbitan dan pembaruan sertifikat SSL Let's Encrypt secara otomatis tanpa instalasi certbot.

### Langkah-langkah:
1. Hubungkan domain publik Anda (misal `iuran.paguyuban-rt.id`) ke IP publik server melalui DNS A Record.
2. Buat file `Caddyfile` di server:

```caddy
iuran.paguyuban-rt.id {
    reverse_proxy localhost:80 {
        header_up Host {host}
        header_up X-Real-IP {remote_host}
        header_up X-Forwarded-For {remote_host}
        header_up X-Forwarded-Proto https
    }

    # Header keamanan tambahan
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
    }
}
```

3. Jalankan Caddy via Docker:
```bash
docker run -d --name caddy-proxy \
  --restart unless-stopped \
  --net=host \
  -v $(pwd)/Caddyfile:/etc/caddy/Caddyfile \
  -v caddy_data:/data \
  caddy:2-alpine
```

---

## 3. Opsi B: Nginx Host Reverse Proxy + Certbot

Jika server Anda telah menjalankan Nginx di host OS:

### Konfigurasi Nginx Server Block (`/etc/nginx/sites-available/viwb`):

```nginx
server {
    listen 80;
    server_name iuran.paguyuban-rt.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name iuran.paguyuban-rt.id;

    # Sertifikat SSL dari Certbot
    ssl_certificate /etc/letsencrypt/live/iuran.paguyuban-rt.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/iuran.paguyuban-rt.id/privkey.pem;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # HSTS & Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    client_max_body_size 10M;

    location / {
        proxy_pass http://127.0.0.1:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_redirect off;
    }
}
```

### Penerbitan Sertifikat:
```bash
sudo certbot --nginx -d iuran.paguyuban-rt.id
```

---

## 4. Opsi C: Cloudflare Tunnel (Tanpa Buka Port Masuk)

Jika server berada di balik NAT / IP dinamis / internet rumahan (tanpa IP publik statis):
1. Install `cloudflared` di server.
2. Buat tunnel ke `http://localhost:80`.
3. Aktifkan mode SSL **Full (Strict)** di dashboard Cloudflare.

---

## 5. Checklist Verifikasi Pasca Deployment Production

- [ ] `APP_ENV=production` dan `APP_DEBUG=false` di file `.env`.
- [ ] `APP_URL=https://domain-anda.id` menggunakan skema `https://`.
- [ ] Jalankan `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
- [ ] Akses web via browser: verifikasi ikon gembok hijau/aman, tidak ada peringatan *mixed content*.
- [ ] Cookie session memiliki atribut `SameSite=Lax` dan terenkripsi (`SESSION_ENCRYPT=true`).
