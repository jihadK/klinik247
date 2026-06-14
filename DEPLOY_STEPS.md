# 📋 Step Detail Deploy Klinik247 ke `kamilsrv1`

> **Tujuan**: Install Klinik247 di `/var/www/klinik247` tanpa menyentuh `/var/www/pesisirfreshfish`.
> **Prinsip**: Ubah → Verifikasi → Lanjut. Setiap step diakhiri **cek fishstock masih jalan**.
> **Domain target**: `klinik247.net` → diarahkan ke server `kamilsrv1`.

---

## 🔧 Konfigurasi Awal (set sekali per SSH session)

Setelah SSH login, jalankan ini di terminal supaya semua perintah selanjutnya pakai variabel yang konsisten:

```bash
# Domain Klinik247 (sudah diset oleh user)
export KLINIK_DOMAIN="klinik247.net"

# Domain fishstock (existing, jangan disentuh)
export FISHSTOCK_URL="pesisirfreshfish.web.id"

# PHP-FPM version (auto-detect dari socket aktif)
export PHP_FPM_VER=$(ls /var/run/php/ | grep -oP 'php\K[0-9]+\.[0-9]+' | head -1)
echo "PHP-FPM Version: $PHP_FPM_VER"

# Repo
export KLINIK_REPO="github-klinik247:jihadK/klinik247.git"
```

✅ Verifikasi:
```bash
echo "Klinik:    https://$KLINIK_DOMAIN"
echo "Fishstock: https://pesisirfreshfish.web.id"
echo "PHP-FPM:   php${PHP_FPM_VER}-fpm"
```

> 💡 Kalau pindah SSH session / re-login, jalankan blok di atas lagi. Atau simpan permanen di `~/.bashrc`:
> ```bash
> cat >> ~/.bashrc <<'EOF'
> export KLINIK_DOMAIN="klinik247.net"
> export FISHSTOCK_URL="pesisirfreshfish.web.id"
> export PHP_FPM_VER=$(ls /var/run/php/ 2>/dev/null | grep -oP 'php\K[0-9]+\.[0-9]+' | head -1)
> EOF
> source ~/.bashrc
> ```

---

## 🛡 Aturan Aman (WAJIB)

1. ❌ **JANGAN PERNAH** edit/hapus file di `/var/www/pesisirfreshfish/`
2. ❌ **JANGAN PERNAH** edit `/etc/nginx/sites-available/pesisirfreshfish`
3. ❌ **JANGAN PERNAH** hapus symlink di `/etc/nginx/sites-enabled/pesisirfreshfish`
4. ❌ **JANGAN PERNAH** restart Nginx tanpa `nginx -t` lolos dulu
5. ✅ **SELALU** pakai `reload` (bukan `restart`) untuk Nginx & PHP-FPM
6. ✅ **SELALU** verifikasi fishstock setelah reload Nginx

---

## STEP 0 — Snapshot & Health Check Awal

**Tujuan**: Pastikan fishstock baseline sehat sebelum mulai.

### 0.1 SSH ke server
```bash
ssh root@kamilsrv1
```

### 0.2 Cek fishstock running (BASELINE)
```bash
# Cek vhost aktif
ls -la /etc/nginx/sites-enabled/

# Cek HTTP response fishstock
curl -sI https://pesisirfreshfish.web.id/ | head -3
# atau pakai IP + Host header kalau belum tahu domain:
curl -sI http://localhost/ -H "Host: pesisirfreshfish.web.id" | head -3
```

✅ **Pastikan respon `HTTP/1.1 200 OK` atau `HTTP/2 200`.** Catat URL ini — akan dipakai untuk verifikasi nanti.

### 0.3 Catat versi & socket PHP-FPM yang dipakai fishstock
```bash
# Versi PHP CLI
php -v | head -1

# Socket FPM aktif
ls /var/run/php/

# Cek vhost fishstock pakai socket mana
grep -E 'fastcgi_pass|server_name' /etc/nginx/sites-available/pesisirfreshfish
```

📝 **Simpan output** — `fastcgi_pass unix:/var/run/php/phpX.Y-fpm.sock` ini yang akan disamakan di vhost klinik247.

### 0.4 Backup config Nginx (jaga-jaga)
```bash
mkdir -p /root/backup-nginx
tar czf /root/backup-nginx/nginx-before-klinik247-$(date +%Y%m%d-%H%M).tar.gz \
    /etc/nginx/sites-available /etc/nginx/sites-enabled /etc/nginx/nginx.conf
ls -la /root/backup-nginx/
```

✅ Output ada file `.tar.gz` → backup OK.

---

## STEP 1 — Cek Tools Wajib

```bash
which git composer node npm certbot
php -m | grep -iE 'pgsql|mbstring|xml|bcmath|gd|curl|zip|intl|fileinfo'
```

✅ Semua harus ada. Kalau ada yang kurang, install dulu (lihat DEPLOY.md langkah 0️⃣).

---

## STEP 2 — Setup SSH Deploy Key untuk GitHub

> Dijalankan **sebagai root** di server.

### 2.1 Generate key khusus klinik247
```bash
# Cek dulu apakah sudah ada
ls -la ~/.ssh/klinik247_deploy 2>/dev/null

# Kalau belum ada → generate
ssh-keygen -t ed25519 -C "klinik247-deploy@kamilsrv1" \
    -f ~/.ssh/klinik247_deploy -N ""
```

### 2.2 Tampilkan public key
```bash
cat ~/.ssh/klinik247_deploy.pub
```

### 2.3 Daftarkan di GitHub
- Buka https://github.com/jihadK/klinik247/settings/keys
- Klik **Add deploy key**
- Title: `kamilsrv1-dev`
- Key: paste output `.pub` tadi
- ☑️ Allow write access → **JANGAN dicentang** (read-only saja)
- Save

### 2.4 Tambah SSH alias
```bash
cat >> ~/.ssh/config <<'EOF'

Host github-klinik247
    HostName github.com
    User git
    IdentityFile ~/.ssh/klinik247_deploy
    IdentitiesOnly yes
    StrictHostKeyChecking accept-new
EOF
chmod 600 ~/.ssh/config
```

### 2.5 Test koneksi GitHub
```bash
ssh -T github-klinik247
```
✅ Output harus: `Hi jihadK/klinik247! You've successfully authenticated, but GitHub does not provide shell access.`

❌ Kalau gagal: cek lagi paste public key di GitHub.

### 2.6 ✅ Verifikasi fishstock masih jalan
```bash
curl -sI https://pesisirfreshfish.web.id/ | head -1
```
Harus tetap `200 OK`.

---

## STEP 3 — Clone Repo (Read-only, Tidak Sentuh Fishstock)

```bash
# Masuk parent directory
cd /var/www

# Sebelum clone, pastikan folder klinik247 belum ada
ls -d klinik247 2>/dev/null && echo "⚠️  STOP: folder klinik247 sudah ada!" || echo "OK: aman dibuat"

# Clone
git clone github-klinik247:jihadK/klinik247.git klinik247

# Verifikasi
ls -la /var/www/
```

✅ Harus muncul **dua** folder:
```
drwxr-xr-x pesisirfreshfish    ← jangan diutak-atik
drwxr-xr-x klinik247           ← target kerja
```

### 3.1 ✅ Verifikasi fishstock
```bash
ls /var/www/pesisirfreshfish/ | head -5  # masih lengkap
curl -sI https://pesisirfreshfish.web.id/ | head -1  # 200 OK
```

---

## STEP 4 — Install Dependencies

```bash
cd /var/www/klinik247

# Composer (production mode, skip dev deps)
composer install --no-dev --optimize-autoloader --no-interaction

# Verifikasi vendor terisi
ls vendor/laravel/framework/composer.json && echo "✅ Laravel terinstall"
```

> ⚠️ **Kalau muncul error PHP version mismatch**: server pakai PHP versi lama (misalnya 8.2/8.3) sementara `composer.json` butuh PHP 8.4. Install PHP 8.4 ikuti DEPLOY.md langkah 0️⃣, **tanpa menghapus PHP versi lama** (fishstock masih perlu pakai versi lama).

```bash
# Node — hanya kalau Vite dipakai. Portal pakai Tailwind CDN, jadi opsional.
# Cek dulu apakah resources/views ada referensi @vite()
grep -r "@vite" resources/views/ | head -3

# Kalau ada → build
# npm ci --no-audit --no-fund
# npm run build
```

---

## STEP 5 — Konfigurasi `.env`

### 5.1 Copy template
```bash
cd /var/www/klinik247
cp .env.example .env
```

### 5.2 Edit
```bash
nano .env
```

Isi minimal (yang **harus** diubah):
```dotenv
APP_NAME="Klinik247"
APP_ENV=dev
APP_KEY=                                              # ← akan diisi otomatis
APP_DEBUG=false
APP_URL=https://klinik247.net           # ← ganti dgn domain Anda

APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

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

Simpan: `Ctrl+O` → `Enter` → `Ctrl+X`.

### 5.3 Generate APP_KEY
```bash
php artisan key:generate
```

✅ Output: `INFO  Application key set successfully.`

### 5.4 Test koneksi DB
```bash
php artisan migrate:status | head -5
```
✅ Harus muncul daftar migration. Kalau error `connection refused` → cek firewall DB allow IP `kamilsrv1`.

### 5.5 Cache config + route + view
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link
```
✅ Setiap perintah harus muncul `INFO  ... cached successfully.`

---

## STEP 6 — Set Permissions

```bash
cd /var/www/klinik247

# Owner ke www-data (user PHP-FPM)
chown -R www-data:www-data .

# Writable folder untuk Laravel
chmod -R 775 storage bootstrap/cache

# Verifikasi
ls -ld storage bootstrap/cache
# Harus: drwxrwxr-x www-data www-data
```

---

## STEP 7 — Upload Logo Klinik247

Logo Klinik247 (file PNG) belum ada di repo (kemungkinan). Upload manual dari mesin local.

### 7.1 Cek dulu di server
```bash
ls -la /var/www/klinik247/public/portal/
```

Kalau folder kosong atau `logo-klinik247.png` tidak ada, lanjut upload.

### 7.2 Dari mesin local PowerShell (BUKAN di SSH):
```powershell
scp "D:\FILE\KAMIL\PROJECT\php\testappklnk\public\portal\logo-klinik247.png" `
    root@kamilsrv1:/var/www/klinik247/public/portal/
```

### 7.3 Balik ke server, fix permission:
```bash
chown www-data:www-data /var/www/klinik247/public/portal/logo-klinik247.png
ls -la /var/www/klinik247/public/portal/
```

> 💡 Kalau logo belum diupload, portal otomatis pakai icon `medical_services` Material — tampilan masih rapi.

---

## STEP 8 — Buat Vhost Nginx BARU (Tidak Sentuh Fishstock)

### 8.1 Buat file vhost baru
```bash
nano /etc/nginx/sites-available/klinik247
```

Isi (perhatikan **`server_name`** dan **`fastcgi_pass`** sesuai socket dari STEP 0.3):

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

    # Static cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2?|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location ~ \.php$ {
        # GANTI socket sesuai output STEP 0.3
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

Simpan: `Ctrl+O` → `Enter` → `Ctrl+X`.

### 8.2 ⚠️ TEST KONFIG SEBELUM RELOAD (kritikal!)
```bash
nginx -t
```

✅ Output **HARUS**:
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

❌ **Kalau ada error**: JANGAN reload, perbaiki dulu. Fishstock masih jalan kalau Nginx belum di-reload.

### 8.3 Aktifkan vhost (buat symlink)
```bash
ln -s /etc/nginx/sites-available/klinik247 /etc/nginx/sites-enabled/

# Verifikasi
ls -la /etc/nginx/sites-enabled/
```

✅ Harus muncul **dua symlink**:
```
pesisirfreshfish -> /etc/nginx/sites-available/pesisirfreshfish    ← jangan diutak-atik
klinik247        -> /etc/nginx/sites-available/klinik247           ← baru
```

### 8.4 TEST LAGI setelah aktifasi
```bash
nginx -t
```
✅ Pastikan masih `syntax is ok`.

### 8.5 RELOAD Nginx (bukan restart!)
```bash
systemctl reload nginx

# Cek status
systemctl status nginx --no-pager | head -10
```
✅ Status harus `active (running)`.

### 8.6 ✅ VERIFIKASI FISHSTOCK MASIH JALAN
```bash
curl -sI https://pesisirfreshfish.web.id/ | head -3
```
✅ Harus tetap `200 OK`.

❌ Kalau fishstock down → **rollback cepat**:
```bash
rm /etc/nginx/sites-enabled/klinik247
nginx -t && systemctl reload nginx
```
Lalu cek error log: `tail -50 /var/log/nginx/error.log`

---

## STEP 9 — Test HTTP (Sebelum SSL)

```bash
# Test landing portal via HTTP
curl -sI http://klinik247.net/ | head -3

# Atau via Host header kalau DNS belum siap
curl -sI http://localhost/ -H "Host: klinik247.net" | head -3
```

✅ Harus muncul `HTTP/1.1 302 Found` (redirect ke `/portal`) atau `200`.

❌ Kalau 502/404:
- 502 → cek `fastcgi_pass` socket benar
- 404 → cek `root /var/www/klinik247/public` benar

```bash
# Debug log
tail -30 /var/log/nginx/klinik247_error.log
tail -30 /var/www/klinik247/storage/logs/laravel.log
```

---

## STEP 10 — Pasang SSL Let's Encrypt

**Prasyarat**: DNS sub-domain `klinik247.net` sudah pointing ke IP `kamilsrv1`. Verifikasi:
```bash
dig +short klinik247.net
# Harus return IP server kamilsrv1
```

```bash
certbot --nginx -d klinik247.net \
    --agree-tos --redirect -m admin@klinik247.net -n
```

✅ Output diakhiri: `Successfully deployed certificate for klinik247.net to /etc/nginx/sites-enabled/klinik247`

### 10.1 Test HTTPS
```bash
curl -sI https://klinik247.net/ | head -3
```
✅ `HTTP/2 302` atau `HTTP/2 200`.

### 10.2 ✅ VERIFIKASI FISHSTOCK
```bash
curl -sI https://pesisirfreshfish.web.id/ | head -1
```
✅ Tetap `200 OK`.

---

## STEP 11 — Smoke Test Aplikasi

```bash
cd /var/www/klinik247

# 1. Routes
php artisan route:list --path=portal
php artisan route:list --path=admin/login

# 2. Test portal landing
curl -s https://klinik247.net/portal | grep -o "Portal Pasien" | head -1
# Harus muncul: "Portal Pasien"

# 3. Test admin login
curl -sI https://klinik247.net/admin/login | head -3
# Harus: HTTP/2 200
```

### 11.1 Browser test
- 🌐 `https://klinik247.net/portal` → Portal Pasien Klinik247
- 🔐 `https://klinik247.net/admin/login` → Login petugas
- Login: `superadmin` / `superadmin` → **ganti password segera!**

---

## STEP 12 — Final Health Check (Dual-App)

```bash
echo "=== FISHSTOCK ==="
curl -sI https://pesisirfreshfish.web.id/ | head -1

echo "=== KLINIK247 ==="
curl -sI https://klinik247.net/ | head -1

echo "=== NGINX ==="
systemctl is-active nginx
nginx -t 2>&1 | tail -2

echo "=== PHP-FPM ==="
systemctl is-active php8.4-fpm

echo "=== DISK ==="
df -h /var/www
```

✅ Kedua app `200`, semua service `active`.

---

## 🔄 Workflow Update Code Berikutnya

Untuk update setelah deploy awal:

```bash
cd /var/www/klinik247

# 1. Maintenance mode (opsional, mencegah user akses saat update)
php artisan down

# 2. Pull
git pull origin main

# 3. Update deps (kalau composer.lock berubah)
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Migrate (kalau ada migration baru)
php artisan migrate --force

# 5. Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Fix permissions (kalau git pull ubah owner)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Reload PHP-FPM (clear opcache)
systemctl reload php8.4-fpm   # sesuaikan versi

# 8. Up lagi
php artisan up

# 9. Verifikasi
curl -sI https://klinik247.net/portal | head -1
curl -sI https://pesisirfreshfish.web.id/ | head -1   # FISHSTOCK TETAP OK!
```

### Skrip One-Liner

Simpan sekali di server:
```bash
cat > /usr/local/bin/deploy-klinik247.sh <<'EOF'
#!/bin/bash
set -e
APP=/var/www/klinik247
PHP_FPM=php8.4-fpm   # ganti kalau versi beda
cd $APP
php artisan down || true
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
systemctl reload $PHP_FPM
php artisan up
echo "✅ Deploy klinik247 selesai."
echo "Check: curl -sI https://klinik247.net/portal | head -1"
EOF

chmod +x /usr/local/bin/deploy-klinik247.sh

# Pakai:
deploy-klinik247.sh
```

---

## 🆘 Rollback Cepat (Kalau Ada Masalah)

### Skenario A: Nginx config klinik247 bikin error, fishstock down
```bash
rm /etc/nginx/sites-enabled/klinik247
nginx -t && systemctl reload nginx

# Verifikasi fishstock balik normal
curl -sI https://pesisirfreshfish.web.id/ | head -1
```

### Skenario B: Mau hapus klinik247 total (tanpa sentuh fishstock)
```bash
# 1. Nonaktifkan vhost
rm /etc/nginx/sites-enabled/klinik247

# 2. Reload Nginx
nginx -t && systemctl reload nginx

# 3. (opsional) Hapus folder app
rm -rf /var/www/klinik247

# 4. (opsional) Hapus vhost file
rm /etc/nginx/sites-available/klinik247

# 5. (opsional) Hapus log
rm -f /var/log/nginx/klinik247_*.log

# 6. (opsional) Revoke SSL
certbot delete --cert-name klinik247.net

# Fishstock SAMA SEKALI TIDAK TERSENTUH
ls /var/www/pesisirfreshfish/ | head -3
curl -sI https://pesisirfreshfish.web.id/ | head -1
```

### Skenario C: Restore config Nginx dari backup STEP 0.4
```bash
cd /
tar xzf /root/backup-nginx/nginx-before-klinik247-<TIMESTAMP>.tar.gz
nginx -t && systemctl reload nginx
```

---

## 🐛 Troubleshooting

| Gejala                                            | Cek                                                                                |
|---------------------------------------------------|-------------------------------------------------------------------------------------|
| **`nginx -t` error: duplicate server_name**       | Berarti domain sama dengan fishstock. Ganti `server_name` di vhost klinik247.       |
| **502 Bad Gateway**                               | `fastcgi_pass` salah. Cek `ls /var/run/php/*.sock`, samakan dengan vhost fishstock. |
| **500 blank page**                                | `tail -f /var/www/klinik247/storage/logs/laravel.log`                              |
| **`SQLSTATE[08006]`**                             | DB firewall block IP `kamilsrv1`. Hubungi admin DB allowlist IP server.            |
| **403 Forbidden**                                 | Permission salah. `chown -R www-data:www-data /var/www/klinik247`                  |
| **Setelah `git pull`, browser tetap lama**        | `php artisan view:clear && view:cache && systemctl reload php8.4-fpm`              |
| **Certbot gagal: DNS belum siap**                 | Tunggu propagasi DNS, cek `dig +short klinik247.net`                       |
| **Fishstock tiba-tiba down**                      | Rollback skenario A di atas. Cek `tail -100 /var/log/nginx/error.log`              |

### Log realtime (3 terminal sekaligus):
```bash
# Terminal 1
tail -f /var/www/klinik247/storage/logs/laravel.log

# Terminal 2
tail -f /var/log/nginx/klinik247_error.log

# Terminal 3
tail -f /var/log/nginx/pesisirfreshfish_error.log  # pastikan tetap kosong!
```

---

## ✅ Checklist Akhir

Sebelum hand-off ke user:

- [ ] STEP 0–12 selesai semua
- [ ] HTTPS aktif di `klinik247.net`
- [ ] Fishstock di `pesisirfreshfish.web.id` MASIH NORMAL
- [ ] Login `superadmin` berhasil, password sudah diganti
- [ ] Portal `/portal` bisa diakses publik tanpa login
- [ ] Logo Klinik247 tampil (atau fallback icon kalau belum upload)
- [ ] Skrip `/usr/local/bin/deploy-klinik247.sh` sudah dibuat
- [ ] Backup config Nginx di `/root/backup-nginx/`
- [ ] `APP_DEBUG=false` dan `SESSION_SECURE_COOKIE=true`

---

🎉 **Selesai!** Klinik247 + Fishstock berjalan bersama di `kamilsrv1` tanpa konflik.

**Domain klinik247-dev**: `https://klinik247.net`
**Domain fishstock** (tidak berubah): `https://<domain-fishstock>`
