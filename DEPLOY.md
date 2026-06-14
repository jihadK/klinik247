# 🚀 Panduan Deploy — Klinik247 ke `kamilsrv1`

Deploy Klinik247 **side-by-side** dengan fishstock yang sudah jalan di `/var/www/pesisirfreshfish`. Stack sudah tersedia (Nginx 1.18 Ubuntu + PHP-FPM + Composer + Git), jadi langkah deploy fokus pada **clone repo baru, vhost Nginx baru, dan symlink**.

> **Asumsi**: Anda sudah punya SSH access ke `root@kamilsrv1`. Database tetap PostgreSQL remote `103.93.162.70:5432/klinik`.

---

## 🗂 Layout Server (Setelah Deploy)

```
/var/www/
├── pesisirfreshfish/         # ✅ Fishstock (existing, jangan disentuh)
└── klinik247/                # 🆕 Klinik247 (target deploy)
    ├── public/               # Webroot Nginx → /klinik247/public
    ├── storage/
    └── .env

/etc/nginx/sites-available/
├── pesisirfreshfish          # ✅ Existing
└── klinik247                 # 🆕 Baru
```

---

## 0️⃣ Cek Stack Server (sekali, opsional)

SSH dulu:
```bash
ssh root@kamilsrv1
```

Cek versi PHP yang dipakai fishstock:
```bash
php -v
php -m | grep -iE 'pgsql|mbstring|xml|bcmath|gd|curl|zip'

# Cek socket PHP-FPM yang aktif
ls /var/run/php/
```

> **Catatan PHP**: Klinik247 dikembangkan dengan **PHP 8.4** (Herd). Kalau server pakai 8.2/8.3, biasanya masih kompatibel — tinggal pastikan `composer install` lolos. Kalau error, install PHP 8.4 pakai `ppa:ondrej/php`:
>
> ```bash
> add-apt-repository ppa:ondrej/php -y && apt update
> apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-pgsql \
>     php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-curl \
>     php8.4-zip php8.4-gd php8.4-intl php8.4-readline php8.4-fileinfo
> systemctl enable --now php8.4-fpm
> ```

Test koneksi ke DB remote (sekali aja):
```bash
PGPASSWORD='klinik!' psql -h 103.93.162.70 -p 5432 -U klinik_app -d klinik -c "SELECT version();"
```

---

## 1️⃣ Setup SSH Deploy Key (sekali)

Kalau belum ada deploy key untuk repo Klinik247:

```bash
# Generate key khusus untuk klinik247
ssh-keygen -t ed25519 -C "klinik247-deploy@kamilsrv1" \
    -f ~/.ssh/klinik247_deploy -N ""

cat ~/.ssh/klinik247_deploy.pub
```

Copy output → **GitHub repo Klinik247 → Settings → Deploy keys → Add deploy key** (nama: `kamilsrv1-dev`, biarkan **read-only**).

Konfigurasi alias SSH:
```bash
cat >> ~/.ssh/config <<'EOF'

Host github-klinik247
    HostName github.com
    User git
    IdentityFile ~/.ssh/klinik247_deploy
    StrictHostKeyChecking accept-new
EOF
chmod 600 ~/.ssh/config

# Test
ssh -T github-klinik247
```

---

## 2️⃣ Clone Repo

```bash
cd /var/www
git clone github-klinik247:jihadK/klinik247.git klinik247
cd klinik247
```

> Repo: https://github.com/jihadK/klinik247
> Pakai SSH alias (`github-klinik247`) yang sudah dikonfigurasi di langkah 1️⃣ supaya pull pakai deploy key. Hindari clone HTTPS biar tidak perlu masuk-keluar password tiap kali pull.

---

## 3️⃣ Install Dependencies & Setup `.env`

```bash
cd /var/www/klinik247

# Composer
composer install --no-dev --optimize-autoloader --no-interaction

# Node (cuma kalau perlu build asset Vite; portal pakai Tailwind CDN, jadi opsional)
# npm ci && npm run build

# Env
cp .env.example .env
nano .env
```

Isi `.env` minimal:
```dotenv
APP_NAME="Klinik247"
APP_ENV=dev
APP_KEY=
APP_DEBUG=false
APP_URL=https://klinik247.net

APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

# === PostgreSQL Remote ===
DB_CONNECTION=pgsql
DB_HOST=103.93.162.70
DB_PORT=5432
DB_DATABASE=klinik
DB_USERNAME=klinik_app
DB_PASSWORD=klinik!
DB_SCHEMA=public

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Generate key + cache + storage link:
```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link

# Cek migration (skip kalau DB sudah lengkap dari local dev)
php artisan migrate:status
```

---

## 4️⃣ Set Permissions

```bash
cd /var/www/klinik247
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache
```

---

## 5️⃣ Upload Logo Portal (dari local)

Logo Klinik247 disimpan di `public/portal/logo-klinik247.png`. Upload dari mesin local PowerShell:

```powershell
scp "D:\FILE\KAMIL\PROJECT\php\testappklnk\public\portal\logo-klinik247.png" `
    root@kamilsrv1:/var/www/klinik247/public/portal/
```

Lalu di server:
```bash
chown www-data:www-data /var/www/klinik247/public/portal/logo-klinik247.png
```

> Kalau file ini belum diupload, portal otomatis fallback ke icon `medical_services` Material — masih tampil rapi, tidak broken.

---

## 6️⃣ Nginx Vhost Baru (Side-by-Side dengan Fishstock)

```bash
nano /etc/nginx/sites-available/klinik247
```

Isi (sesuaikan `server_name` dan **`fastcgi_pass` mengikuti fishstock**):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name klinik247.net;

    root /var/www/klinik247/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 20M;

    access_log /var/log/nginx/klinik247_access.log;
    error_log  /var/log/nginx/klinik247_error.log warn;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Cache static
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2?|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ \.php$ {
        # PENTING: samakan dengan socket di /etc/nginx/sites-available/pesisirfreshfish
        # cek: grep fastcgi_pass /etc/nginx/sites-available/pesisirfreshfish
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60;
    }

    # Block file sensitif
    location ~ /\.(?!well-known).* { deny all; }
    location ~* \.(env|log|md|json|lock|sql)$ { deny all; }
}
```

> 💡 **Tip**: Kalau fishstock pakai `php8.3-fpm.sock` atau lainnya, GANTI baris `fastcgi_pass` di atas supaya sama persis. Cek dengan:
> ```bash
> grep -h fastcgi_pass /etc/nginx/sites-available/pesisirfreshfish
> ```

Aktifkan + test + reload:
```bash
ln -s /etc/nginx/sites-available/klinik247 /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 7️⃣ SSL dengan Let's Encrypt

Certbot biasanya sudah terinstall (dipakai fishstock). Tinggal:

```bash
certbot --nginx -d klinik247.net --agree-tos --redirect -n
```

Kalau belum:
```bash
apt install -y certbot python3-certbot-nginx
```

Verifikasi auto-renew:
```bash
systemctl status certbot.timer
certbot renew --dry-run
```

---

## 8️⃣ Smoke Test

```bash
cd /var/www/klinik247

# Routes
php artisan route:list --path=portal
php artisan route:list --path=admin/login

# HTTP check
curl -sI https://klinik247.net/ | head -3
curl -s  https://klinik247.net/portal | grep -o "Portal Pasien" | head -1
```

Buka browser:
- 🌐 `https://klinik247.net/portal` — Portal Pasien (public)
- 🔐 `https://klinik247.net/admin/login` — Login petugas

---

## 🔄 Workflow Update Berikutnya

Sama gaya seperti fishstock — masuk ke folder lalu pull:

```bash
cd /var/www/klinik247

# Maintenance mode (opsional)
php artisan down

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction
# kalau ada perubahan asset Vite: npm ci && npm run build

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Reload PHP-FPM (clear opcache) — sesuaikan versi
systemctl reload php8.4-fpm

php artisan up
```

### Skrip Otomatis

Simpan sekali, pakai berkali-kali:

```bash
cat > /usr/local/bin/deploy-klinik247.sh <<'EOF'
#!/bin/bash
set -e
APP=/var/www/klinik247
PHP_FPM=php8.4-fpm   # sesuaikan kalau versi beda
echo "🔄 Pull latest..."
cd $APP && git pull origin main
echo "📦 composer..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "🗄  migrate..."
php artisan migrate --force
echo "♻️  cache..."
php artisan config:cache && php artisan route:cache && php artisan view:cache
echo "🔐 perms..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "🔁 reload php-fpm..."
systemctl reload $PHP_FPM
echo "✅ Done."
EOF
chmod +x /usr/local/bin/deploy-klinik247.sh

# Pakai:
sudo deploy-klinik247.sh
```

---

## 🐛 Troubleshooting

| Gejala                                            | Solusi                                                                              |
|---------------------------------------------------|-------------------------------------------------------------------------------------|
| **502 Bad Gateway**                               | `fastcgi_pass` salah socket. Cek `ls /var/run/php/*.sock` lalu update vhost.        |
| **500 blank page**                                | `tail -f storage/logs/laravel.log` — biasanya permission `storage/` belum 775       |
| `SQLSTATE[08006] connection refused`              | Firewall server DB belum izinkan IP `kamilsrv1`. Hubungi admin DB.                  |
| `Class "PDO" not found`                           | Ekstensi `phpX.Y-pgsql` belum ada. `apt install phpX.Y-pgsql && systemctl reload phpX.Y-fpm` |
| Logo portal tidak muncul                          | File belum diupload di `public/portal/logo-klinik247.png` — fallback icon tetap aktif |
| CSS portal rusak                                  | Portal pakai Tailwind CDN, butuh internet outbound. Cek `curl https://cdn.tailwindcss.com -I` |
| `419 Page Expired` saat login                     | `chmod -R 775 storage/framework/sessions`                                            |
| Setelah `git pull`, browser tetap UI lama         | `php artisan view:clear && view:cache` + `systemctl reload phpX.Y-fpm`              |
| Konflik dengan vhost fishstock                    | Pastikan `server_name` berbeda. Cek `nginx -T \| grep server_name`                   |

### Log realtime
```bash
tail -f /var/www/klinik247/storage/logs/laravel.log
tail -f /var/log/nginx/klinik247_error.log
journalctl -u php8.4-fpm -f
```

---

## ✅ Checklist Production-Readiness

Sebelum dipakai user real / ekspos publik:

- [ ] `APP_DEBUG=false` di `.env`
- [ ] SSL aktif (HTTPS redirect)
- [ ] **Ganti password `superadmin`** setelah login pertama
- [ ] `SESSION_SECURE_COOKIE=true` + `SESSION_HTTP_ONLY=true`
- [ ] Rate limit Nginx tambahan (di `/etc/nginx/nginx.conf` block `http`):
  ```nginx
  limit_req_zone $binary_remote_addr zone=auth:10m rate=10r/m;
  ```
  Lalu di vhost `klinik247`:
  ```nginx
  location = /admin/login    { limit_req zone=auth burst=5 nodelay; try_files $uri /index.php?$query_string; }
  location = /portal/search  { limit_req zone=auth burst=3 nodelay; try_files $uri /index.php?$query_string; }
  ```
- [ ] Backup DB schedule (cron `pg_dump` di server DB)
- [ ] Monitoring uptime (UptimeRobot / Better Stack)
- [ ] Tambahkan `.env` ke `.gitignore` (default Laravel sudah ✓ — verify dengan `git status`)

---

## 📞 Kontak

- Server: `root@kamilsrv1`
- App fishstock (existing): `/var/www/pesisirfreshfish`
- App klinik247 (baru): `/var/www/klinik247`
- DB remote: `103.93.162.70:5432/klinik`
- Repo: https://github.com/jihadK/klinik247

🎉 **Selamat! Klinik247 siap jalan side-by-side dengan fishstock di `kamilsrv1`.**
