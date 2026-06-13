# Klinik247 — Sistem Manajemen Klinik

Aplikasi web untuk **Praktek Mandiri Bidan (PMB)** dan klinik kecil — mencakup manajemen pasien, kunjungan, ANC/INC/PNC/KN, KB, imunisasi & tumbuh kembang anak, rekam medis terintegrasi, serta portal pasien self-service.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14%2B-336791?logo=postgresql)](https://postgresql.org)

---

## ✨ Fitur Utama

### 🏥 Admin (Petugas Klinik)
| Modul                   | Status | Keterangan                                                     |
|-------------------------|:------:|----------------------------------------------------------------|
| **Auth & RBAC**         |   ✅   | 12 tabel auth, ~70 permissions granular, 2FA, lockout 5x       |
| **Multi-tenant (Site)** |   ✅   | Global scope `site_id`, multi-warehouse, isolasi data per klinik |
| **Master Data**         |   ✅   | Sites, Roles, Users, Payer Types, Wilayah (Prov/Kab/Kec/Desa)  |
| **Pasien**              |   ✅   | CRUD + cascade wilayah AJAX + auto generate No. RM `SS-YYYY-NNNNNN` |
| **Kunjungan**           |   ✅   | 4 kategori register (Umum/ANC/KB/Anak), no_register otomatis   |
| **KB (Akseptor)**       |   ✅   | Kartu KB, kunjungan ulang, ganti alat kontrasepsi              |
| **ANC (Ibu Hamil)**     |   ✅   | Kartu Hamil, HPHT→HPL auto, kunjungan K1-K6, IMT, GPA          |
| **INC (Persalinan)**    |   ✅   | Data persalinan, SOAP siklus, surat rujukan                    |
| **PNC (Nifas)**         |   ✅   | Kunjungan nifas KF1-KF4                                        |
| **KN (Neonatus)**       |   ✅   | Kunjungan neonatus KN1-KN3, link ke Imunisasi                  |
| **Imunisasi**           |   ✅   | Dose tracking per jenis vaksin                                 |
| **Tumbuh Kembang**      |   ✅   | Antropometri (BB/TB/LK), SDIDTK                                |
| **Rekam Medis Integrated** | ✅ | Single-page view: identitas + semua riwayat                    |
| **Apotik & Kasir**      |  🚧   | Phase 1.8 — perencanaan                                        |

### 🌐 Portal Pasien (Public Self-Service)
- Cari rekam medis pribadi pakai **No. RM atau NIK + Tanggal Lahir** (2-factor)
- Rate limit 5x / 15 menit per IP, session expiry 30 menit
- Timeline kronologis: kunjungan, kehamilan, persalinan, KB, neonatus, imunisasi
- Material 3 design, responsive mobile-first, print-ready

---

## 🛠 Tech Stack

| Layer       | Stack                                               |
|-------------|-----------------------------------------------------|
| Backend     | **Laravel 12** + PHP 8.4                            |
| Database    | **PostgreSQL 14+** (remote)                         |
| Frontend    | Blade + Metronic 8 (admin) + Tailwind CDN (portal)  |
| Icons       | KeenIcons (admin), Material Symbols (portal)        |
| Fonts       | Inter + Plus Jakarta Sans                           |
| JS Libs     | Select2, SweetAlert2, jQuery                        |
| Session     | File driver (default), bisa switch ke Database/Redis |
| Local Dev   | **Herd PHP** (Windows) — wajib, bukan Laragon       |

---

## 📂 Struktur Folder Penting

```
testappklnk/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Controller untuk admin (auth required)
│   │   └── Portal/         # PortalController — public self-service
│   └── Models/             # BaseModel + 30+ model dengan global scope site
├── database/migrations/    # Schema (multi-tenant ready)
├── resources/views/
│   ├── admin/              # Metronic 8 admin views
│   └── portal/             # Material 3 portal pasien
│       ├── layout.blade.php
│       ├── landing.blade.php
│       └── result.blade.php
├── routes/web.php          # Public /portal/* + admin /admin/*
├── public/portal/          # Logo & asset portal
└── .env                    # Konfigurasi environment (jangan di-commit)
```

---

## 🚀 Quick Start (Local Dev)

> Prasyarat: **Herd** terinstall + PHP 8.4 + Composer + Node.js 18+

```powershell
# 1. Clone
git clone https://github.com/<org>/klinik247.git
cd klinik247

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
copy .env.example .env
& "C:\Users\<USER>\.config\herd\bin\php.bat" artisan key:generate

# 4. Edit .env — sesuaikan koneksi PostgreSQL
#    DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Migrasi & seed
& "C:\Users\<USER>\.config\herd\bin\php.bat" artisan migrate --seed

# 6. (Opsional) Build asset
npm run build

# 7. Tambahkan site Herd
herd link testapklnk
# Akses: http://testapklnk.test
```

### URL Penting
- **Portal Pasien**: `http://testapklnk.test/portal`
- **Admin Login**: `http://testapklnk.test/admin/login`
- **Default Superadmin**: `superadmin` / `superadmin` (ganti setelah login pertama!)

---

## 🔐 Convention & Best Practices

### Naming Tabel
- `tbm_*` → Master (tbm_users, tbm_sites)
- `tbr_*` → Reference (tbr_villages, tbr_payer_types)
- `tbh_*` → Header transaksi (tbh_patient_visits, tbh_pregnancies)
- `tbs_*` → Sub/detail transaksi (tbs_anc_visits, tbs_kb_visits)

### Multi-tenant
Semua model utama meng-`extends BaseModel` yang inject global scope `site_id = auth()->user()->site_id`. Untuk akses cross-tenant (mis. portal pasien public), pakai `Model::withoutGlobalScope('site')`.

### Response Format (Dual Mode)
Controller mendukung 2 mode response berdasarkan `$request->expectsJson()`:
- **Web**: redirect + flash session
- **AJAX**: JSON `{ resCode: '00', message, data }` (`00` = success, lainnya = error)

### UI Convention (wajib!)
- **SELECT** → Selalu pakai Select2 dengan `data-control="select2"` + `data-placeholder` + `data-allow-clear`
- **Konfirmasi** → Selalu pakai `Swal.fire(...)` — **dilarang** `confirm()`, `alert()`, `prompt()` native

---

## 📖 Dokumentasi Tambahan

| File                          | Isi                                                  |
|-------------------------------|------------------------------------------------------|
| [`DEPLOY.md`](DEPLOY.md)      | Panduan deploy ke server dev (Ubuntu + Nginx)         |
| [`docs_test_phase_1_5_inc.md`](docs_test_phase_1_5_inc.md) | Test scenario Phase 1.5 INC                          |
| `appplandoc/` (luar repo)     | Plandoc 11 file modul (Auth/Pasien/KB/ANC/INC/dst.)  |

---

## 🧪 Testing

```powershell
# Run all tests
& "C:\Users\<USER>\.config\herd\bin\php.bat" artisan test

# Specific suite
& "C:\Users\<USER>\.config\herd\bin\php.bat" artisan test --filter PatientTest
```

---

## 📌 Status Phase Development

| Phase    | Modul                                | Status |
|----------|--------------------------------------|:------:|
| **0**    | Auth + Master + System               |   ✅   |
| **1.1**  | Pasien (CRUD + cascade wilayah)      |   ✅   |
| **1.2**  | Kunjungan + 4 kategori register      |   ✅   |
| **1.3**  | KB (Akseptor + ganti alat)           |   ✅   |
| **1.4**  | ANC Ibu Hamil                        |   ✅   |
| **1.5**  | INC Persalinan + Surat Rujukan       |   ✅   |
| **1.6**  | PNC Nifas + KN Neonatus              |   ✅   |
| **1.7**  | Imunisasi + Tumbuh Kembang Anak      |   ✅   |
| **1.7+** | Rekam Medis Terintegrasi             |   ✅   |
| **1.7+** | **Customer Portal Self-Service**     |   ✅   |
| **1.8**  | Apotik + Kasir                       |   🚧   |
| **2.0**  | Laporan + Cetak Buku KIA             |   📋   |

---

## 📜 Lisensi & Kontak

Proprietary — © {{ date('Y') }} Klinik247. All rights reserved.

Untuk bantuan: hubungi admin sistem.
