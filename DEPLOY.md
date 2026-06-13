# 🚀 Panduan Deploy — Klinik247 ke Server Dev

Panduan step-by-step deploy aplikasi Klinik247 ke server dev Linux (Ubuntu 22.04 / Debian 12) dengan stack **Nginx + PHP-FPM 8.4 + PostgreSQL remote**.

> **Catatan**: Stack mengikuti pola yang sama dengan aplikasi `fishstock` (testapp) yang sudah berjalan di server.

---

## 📋 Asumsi & Prasyarat

- ✅ Server dev sudah tersedia, akses SSH root/sudo
- ✅ Domain / subdomain sudah pointing ke IP server (mis. `klinik247-dev.domain.com`)
- ✅ GitHub repo sudah siap (private repo: `git@github.com:<org>/klinik247.git`)
- ✅ Database PostgreSQL remote sudah jalan di `103.93.162.70:5432/klinik`
- ✅ Akses SSH key sudah didaftarkan (`~/.ssh/authorized_keys`)

---

## 🗺 Arsitektur Deploy

```
┌─────────────────┐         HTTPS         ┌────────────────────────┐
│  Browser User   │ ───────────────────▶ │  Server Dev Ubuntu     │
└─────────────────┘                       │  ┌──────────────────┐  │
                                          │  │ Nginx :443       │  │
                                          │  └────────┬─────────┘  │
                                          │           │ FastCGI    │
                                          │  ┌────────▼─────────┐  │
                                          │  │ PHP-FPM 8.4      │  │
                                          │  └────────┬─────────┘  │
                                          │           │            │
                                          │  /var/www/klinik247/   │
                                          │  ├─ public/  ◀ webroot │
                                          │  ├─ storage/ (writable)│
                                          │  └─ bootstrap/cache/   │
                                          └───────────┼────────────┘
                                                      │ pgsql ext
                                                      ▼
                                         ┌──────────────────────┐
                                         │ PostgreSQL Remote    │
                                         │ 103.93.162.70:5432   │
                                         │ Database: klinik     │
                                         └──────────────────────┘
```

---

## 1️⃣ Persiapan Server (sekali saja)

SSH ke server:

```bash
ssh root@<IP-SERVER-DEV>
```

### 1.1 Update sistem
```bash
apt update && apt upgrade -y
```

### 1.2 Install PHP 8.4 + ekstensi yang dibutuhkan Laravel + PostgreSQL
```bash
# Tambah repo PHP
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.4 + ekstensi
apt install -y php8.4 php8.4-fpm php8.4-cli \
    php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-bcmath \
    php8.4-curl php8.4-zip php8.4-gd php8.4-intl php8.4-readline \
    php8.4-tokenizer php8.4-fileinfo

# Verifikasi
php -v
php -m | grep -iE 'pgsql|mbstring|xml|bcmath|curl|gd'
```

### 1.3 Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer --version
```

### 1.4 Install Node.js 20 LTS (untuk Vite build asset)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
node -v && npm -v
```

### 1.5 Install Nginx
```bash
apt install -y nginx
systemctl enable --now nginx
```

### 1.6 Install Git + PostgreSQL client (untuk test koneksi)
```bash
apt install -y git postgresql-client
```

### 1.7 Test koneksi ke PostgreSQL remote
```bash
PGPASSWORD='klinik!' psql -h 103.93.162.70 -p 5432 -U klinik_app -d klinik -c "SELECT version();"
```
Kalau output muncul versi PostgreSQL → ✅ koneksi OK. Kalau error timeout → cek firewall server DB.

---

## 2️⃣ Setup SSH Key untuk GitHub Pull

Server perlu bisa pull repo dari GitHub. Generate deploy key:

```bash
# Generate key tanpa passphrase
ssh-keygen -t ed25519 -C "klinik247-deploy" -f ~/.ssh/klinik247_deploy -N ""

# Tampilkan public key
cat ~/.ssh/klinik247_deploy.pub
```

Copy output `.pub` itu → **GitHub repo → Settings → Deploy keys → Add deploy key**. Beri nama `dev-server`, paste, biarkan **read-only** centang.

Konfigurasi SSH alias supaya tahu pakai key mana:

```bash
cat >> ~/.ssh/config <<'EOF'
Host github-klinik247
    HostName github.com
    User git
    IdentityFile ~/.ssh/klinik247_deploy
    StrictHostKeyChecking accept-new
EOF
chmod 600 ~/.ssh/config
```

Test:
```bash
ssh -T github-klinik247
# Output: "Hi <user>! You've successfully authenticated..."
```

---

## 3️⃣ Clone & Setup Project

### 3.1 Buat user www-data sebagai owner (opsional tapi recommended)
```bash
mkdir -p /var/www
cd /var/www
```

### 3.2 Clone repo
```bash
git clone github-klinik247:<org>/klinik247.git
cd klinik247
```

### 3.3 Install dependencies (production mode)
```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
```

> **Catatan**: kalau Vite tidak dipakai (portal pakai Tailwind CDN), `npm run build` boleh di-skip — cek `vite.config.js` dulu.

### 3.4 Setup `.env`
```bash
cp .env.example .env
nano .env
```

Isi minimal:
```dotenv
APP_NAME="Klinik247"
APP_ENV=dev
APP_KEY=
APP_DEBUG=false
APP_URL=https://klinik247-dev.domain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

# === PostgreSQL Remote (sama dengan local) ===
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
SESSION_PATH=/
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

### 3.5 Generate APP_KEY
```bash
php artisan key:generate
```

### 3.6 (PENTING) Cek migrasi — biasanya SKIP di server dev kalau DB sama dgn local
```bash
# Kalau ini server pertama yang konek DB remote → run migrate
# Kalau DB sudah lengkap (kasus pakai DB local dev) → CUKUP migrate:status saja
php artisan migrate:status
```

### 3.7 Optimize & cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
# Storage symlink (untuk akses public/storage)
php artisan storage:link
```

---

## 4️⃣ Set Permissions

```bash
# Owner = www-data (user PHP-FPM)
chown -R www-data:www-data /var/www/klinik247

# Folder yang HARUS writable
chmod -R 775 /var/www/klinik247/storage
chmod -R 775 /var/www/klinik247/bootstrap/cache
```

---

## 5️⃣ Konfigurasi Nginx

```bash
nano /etc/nginx/sites-available/klinik247
```

Isi:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name klinik247-dev.domain.com;

    # Redirect HTTP → HTTPS (setelah SSL aktif)
    # return 301 https://$server_name$request_uri;

    root /var/www/klinik247/public;
    index index.php index.html;

    charset utf-8;
    client_max_body_size 20M;

    # Logging
    access_log /var/log/nginx/klinik247_access.log;
    error_log  /var/log/nginx/klinik247_error.log warn;

    # Security headers (sesuai catatan pentest)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2?|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60;
    }

    # Block akses ke file sensitive
    location ~ /\.(?!well-known).* { deny all; }
    location ~* \.(env|log|md|json|lock|sql)$ { deny all; }
}
```

Aktifkan + test + reload:
```bash
ln -s /etc/nginx/sites-available/klinik247 /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 6️⃣ SSL dengan Let's Encrypt

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d klinik247-dev.domain.com --agree-tos -m admin@domain.com --redirect

# Auto-renew sudah aktif via systemd timer
systemctl status certbot.timer
```

Uncomment baris redirect HTTP→HTTPS di `/etc/nginx/sites-available/klinik247` kalau Certbot belum auto-handle.

---

## 7️⃣ Symbolic Link untuk Logo Portal

Logo Klinik247 perlu disimpan di `public/portal/logo-klinik247.png`. Upload dari local:

```bash
# Di mesin local PowerShell:
scp "D:\FILE\KAMIL\PROJECT\php\testappklnk\public\portal\logo-klinik247.png" \
    root@<IP-SERVER>:/var/www/klinik247/public/portal/
```

---

## 8️⃣ Smoke Test

```bash
# 1. Cek route terdaftar
cd /var/www/klinik247
php artisan route:list --path=portal
php artisan route:list --path=admin

# 2. Test homepage (otomatis redirect ke /portal)
curl -sI https://klinik247-dev.domain.com/ | head -5

# 3. Test portal landing
curl -s https://klinik247-dev.domain.com/portal | grep -i "Portal Pasien"

# 4. Test admin login page
curl -sI https://klinik247-dev.domain.com/admin/login | head -3
```

Buka di browser:
- 🌐 `https://klinik247-dev.domain.com/portal` — Portal Pasien (public)
- 🔐 `https://klinik247-dev.domain.com/admin/login` — Admin Login

---

## 🔄 Workflow Update Berikutnya (Pull Latest)

Setelah deploy awal, untuk update kode berikutnya cukup:

```bash
cd /var/www/klinik247

# 1. Maintenance mode (optional)
php artisan down

# 2. Pull
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build   # kalau ada perubahan asset

# 4. Migrasi DB (kalau ada migration baru)
php artisan migrate --force

# 5. Clear & rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Restart PHP-FPM (clear opcache)
systemctl reload php8.4-fpm

# 8. Up lagi
php artisan up
```

### 🤖 Auto-Deploy Script (Opsional)

Simpan di `/usr/local/bin/deploy-klinik247.sh`:

```bash
#!/bin/bash
set -e
cd /var/www/klinik247
echo "🔄 Pulling latest..."
sudo -u www-data git pull origin main
echo "📦 Installing dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
echo "🏗  Building assets..."
sudo -u www-data npm ci && sudo -u www-data npm run build
echo "🗄  Migrating..."
sudo -u www-data php artisan migrate --force
echo "♻️  Caching..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
echo "🔁 Reloading PHP-FPM..."
systemctl reload php8.4-fpm
echo "✅ Done."
```

```bash
chmod +x /usr/local/bin/deploy-klinik247.sh
# Pakai: sudo deploy-klinik247.sh
```

---

## 🐛 Troubleshooting

| Gejala                                          | Solusi                                                    |
|-------------------------------------------------|-----------------------------------------------------------|
| `500 Internal Server Error` (blank page)        | Cek `storage/logs/laravel-*.log`. Biasanya permission `storage/` belum 775. |
| `SQLSTATE[08006] connection refused`            | Firewall server DB belum buka port 5432 untuk IP server dev. |
| `Class "PDO" not found`                         | Ekstensi `php8.4-pgsql` belum terinstall — `apt install php8.4-pgsql && systemctl reload php8.4-fpm` |
| `Permission denied` saat `php artisan ...`      | `chown -R www-data:www-data .` lalu jalankan dengan `sudo -u www-data php artisan ...` |
| Logo portal tidak muncul                        | File `public/portal/logo-klinik247.png` belum di-upload. Fallback ke icon `medical_services` otomatis. |
| CSS portal kelihatan rusak                      | Portal pakai Tailwind CDN — pastikan server bisa akses internet outbound. |
| `419 Page Expired` saat login                   | `SESSION_DRIVER=file` butuh folder `storage/framework/sessions` writable. |
| Setelah update kode, browser tetap pakai cache lama | `php artisan view:clear && view:cache`, tambahkan `?v=2` di link asset, dan `systemctl reload php8.4-fpm` |

### Cek log realtime
```bash
tail -f /var/www/klinik247/storage/logs/laravel.log
tail -f /var/log/nginx/klinik247_error.log
journalctl -u php8.4-fpm -f
```

---

## 🔒 Catatan Keamanan Production

Sebelum ekspos ke publik:

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production` (atau `dev` untuk staging)
- [ ] SSL aktif + HSTS header
- [ ] Ganti password default `superadmin` setelah login pertama
- [ ] `SESSION_SECURE_COOKIE=true` (cuma HTTPS)
- [ ] `SESSION_HTTP_ONLY=true` (block JS akses cookie)
- [ ] Rate limit Nginx tambahan untuk `/admin/login` dan `/portal/search`:
  ```nginx
  # Di http context /etc/nginx/nginx.conf
  limit_req_zone $binary_remote_addr zone=login:10m rate=10r/m;
  # Di location /admin/login dan /portal/search
  limit_req zone=login burst=5 nodelay;
  ```
- [ ] Backup DB schedule (cron `pg_dump`) walaupun DB remote
- [ ] Monitoring uptime (UptimeRobot / Better Stack)

---

## 📞 Kontak

- **Pemilik klinik**: Bu Tin — Pondok Bersalin Lamongan
- **Dev/maintenance**: hubungi admin sistem
- **Repo**: `github.com/<org>/klinik247`

---

🎉 **Selamat! Klinik247 siap digunakan di server dev.**
