# 🧪 Test Scenarios — Phase 1.5: Persalinan (INC)

> **Module:** Intra Natal Care — Penapisan 18 item + SOAP timeline + 4 Kala + Terapi
> **DDL:** `database/ddl/09_DDL_PHASE1_5_INC.sql`

## Pre-condition
```bash
sudo -u postgres psql -d klinik -f /tmp/09_DDL_PHASE1_5_INC.sql
```
```powershell
"C:\Users\jihad\.config\herd\bin\php.bat" artisan view:clear
"C:\Users\jihad\.config\herd\bin\php.bat" artisan route:clear
```

Verifikasi:
```sql
SELECT 'deliveries' tbl, COUNT(*) FROM tbr_deliveries
UNION ALL SELECT 'soap', COUNT(*) FROM tbr_delivery_soap
UNION ALL SELECT 'perm_inc', COUNT(*) FROM tbm_permissions WHERE module='pelayanan_inc';
-- Expected: 0, 0, 6
SELECT fn_next_doc_number(1, 'PS'); -- PS-01-2026-000001
```

---

## 🎯 Scenario 1: Workflow Mulai Persalinan dari ANC

1. Login admin Bu Tin → ANC kehamilan aktif dengan UK ≥ 36 mg
2. Detail kehamilan → tombol **"🩺 Mulai Persalinan (INC)"** muncul (warna merah)
3. Klik → form INC create
4. **Section A Penapisan**: centang **1-2 item** (mis. "Riwayat SC" + "Anemia berat")
   - **Expected:** counter "2/18", keputusan auto = "RUJUK", banner danger
5. **Section B Masuk PMB**: isi TD 110/70, Nadi 80, Suhu 36.5, RR 18, DJJ 140, His 3, VT 4 cm, Ketuban Utuh
6. Klik **Mulai Persalinan**
7. **Expected:**
   - Redirect ke `/admin/inc/{id}`
   - No. Persalinan: `PS-01-2026-000001`
   - Status: "Inpartu / Kala I"
   - Skor penapisan 2 ditampilkan dengan banner merah
   - SOAP timeline sudah punya 1 row (auto dari data masuk)

## 🎯 Scenario 2: Tambah SOAP saat Kala I

1. Detail persalinan → klik **Tambah SOAP**
2. Isi: observed_at +2 jam, Kala = "Kala I", S = "Mules makin teratur", O = TD 115/70, DJJ 145, His 4×/10' durasi 40 detik, VT 7 cm Hodge II
3. A = "INPARTU Kala I fase aktif progres baik", P = "Observasi 4 jam, ma/mi cukup"
4. Save → SOAP #2 muncul di timeline

## 🎯 Scenario 3: Kala II — Bayi Lahir

1. Edit persalinan → tab **Kala II (Bayi)**
2. Isi: Kala II Mulai (jam sekarang), Bayi Lahir (5 mnt kemudian), JK = L, BB 3200, PB 50, APGAR 1' = 8, APGAR 5' = 9, centang Spontan + Langsung Menangis
3. Pilih Status: **Kala II (Mengejan)** atau biarkan
4. Save

## 🎯 Scenario 4: Kala III — Plasenta

1. Edit → tab **Kala III**
2. Isi: Kala III Mulai (jam bayi lahir), Plasenta Lahir (+5 mnt), centang Spontan + MAK III + TFU Sepusat + UC Kuat + Selaput Lengkap
3. Status → "Kala III (Plasenta)"

## 🎯 Scenario 5: Kala IV — Observasi

1. Edit → tab **Kala IV**
2. Isi: Kala IV Mulai, Kala IV Selesai (+2 jam), Perineum Laserasi = Derajat II, centang Heckting + Lidocain, Perdarahan 200 ml
3. Status → "Kala IV (Observasi)"

## 🎯 Scenario 6: Terapi Pasca Persalinan

1. Edit → tab **Terapi**
2. Centang: Amox, As Mef, Fe, Metergin
3. Vit A Dose 1 = waktu sekarang
4. Untuk Bayi: centang Vit K1 + Salep Mata + HB-0, isi No Batch
5. Save

## 🎯 Scenario 7: Outcome & Selesai

1. Edit → tab **Outcome**
2. Kondisi Ibu: Sehat, Kondisi Bayi: Hidup Sehat
3. Status: **Selesai**
4. Save
5. **Expected:**
   - Kehamilan otomatis update jadi `partus` (status Pregnancy = partus)
   - Tanggal Partus terisi
   - Banner success di show + edit dilock (form terkunci)

## 🎯 Scenario 8: Rujukan

1. Buat persalinan baru dengan penapisan banyak risiko
2. Edit → Outcome → Status: **Rujuk**, isi Rujukan Ke + Alasan
3. Save
4. **Expected:**
   - Pregnancy → status `rujuk`
   - Detail show: banner danger "Dirujuk"
   - Tampilkan alasan rujukan

## 🎯 Scenario 9: Cetak Asuhan Persalinan

1. Detail persalinan → akses URL `/admin/inc/{id}/kartu` (manual atau tambah tombol)
2. **Expected:**
   - Dokumen A4 "ASUHAN PERSALINAN"
   - Section: Identitas / Penapisan 18 item dengan ✓ YA / Pemeriksaan Masuk / SOAP timeline / 4 Kala / Terapi
   - Footer tanda tangan Bidan

## 🎯 Scenario 10: Multi-Tenant + Permission

1. Login admin Amanah → tidak melihat persalinan Bu Tin
2. No. Persalinan Amanah: `PS-02-2026-000001`
3. Dokter dapat: view/create/update/soap/print (tidak delete)
4. Pendaftaran: view/print only

---

## ✅ Definition of Done

- [ ] SQL 09 ter-eksekusi
- [ ] Auto-create SOAP #1 dari data masuk PMB
- [ ] Penapisan skor auto-calc + suggest rujuk
- [ ] Status persalinan transitions: masuk → inpartu → kala_ii → kala_iii → kala_iv → selesai/rujuk
- [ ] Pregnancy auto-update saat selesai/rujuk
- [ ] Hard lock saat selesai/rujuk
- [ ] Cetak kartu work

## 🚀 Next: Phase 1.6 PNC (Nifas) + KN (Neonatus)

Lanjutan natural: setelah persalinan selesai → ibu masuk fase nifas (KF1-KF4) + bayi neonatus (KN1-KN3).
