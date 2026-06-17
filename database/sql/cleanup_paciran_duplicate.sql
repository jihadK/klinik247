-- ===================================================================
-- CLEANUP — Hapus duplikat Kecamatan Paciran (code 3524141)
-- ===================================================================
-- Mempertahankan: code 352410 (standar Permendagri)
-- Menghapus:      code 3524141 + semua village + remap patients
-- ===================================================================
-- AMAN:
--   1. Patients yang refer ke village/district lama → di-remap ke
--      village/district standar berdasarkan NAMA desa
--   2. Patients tanpa pasangan nama desa → di-set NULL
--   3. Baru hapus villages lama, lalu district lama
-- ===================================================================

-- ============================================================
-- STEP 0 (PRE-CHECK) — Lihat dulu apa yang akan dihapus
-- ============================================================
-- Jalankan ini DULU sebelum eksekusi DELETE di bawah!
SELECT 'DISTRICTS' AS what, code, name, regency_code, NULL::TEXT AS extra
FROM tbm_districts
WHERE code IN ('3524141', '352410')

UNION ALL

SELECT 'VILLAGES (akan dihapus)', v.code, v.name, v.district_code, v.postal_code
FROM tbm_villages v
WHERE v.district_code = '3524141'

UNION ALL

SELECT 'PATIENTS (akan di-remap)',
       p.no_rm,
       p.name,
       p.district_code,
       (SELECT name FROM tbm_villages WHERE code = p.village_code)
FROM tbm_patients p
WHERE p.district_code = '3524141'
   OR p.village_code IN (SELECT code FROM tbm_villages WHERE district_code = '3524141');


-- ===================================================================
-- KALAU SUDAH OK, JALANKAN BLOK DI BAWAH INI
-- ===================================================================


-- ============================================================
-- STEP 1 — Remap patients ke village/district standar (352410)
-- ============================================================
-- Patients yang district-nya pakai 3524141 → ganti ke 352410
-- DAN village-nya di-match by name ke village di district 352410
UPDATE tbm_patients p
SET
    district_code = '352410',
    village_code  = (
        SELECT v_new.code
        FROM tbm_villages v_old
        JOIN tbm_villages v_new ON v_new.name = v_old.name
        WHERE v_old.code = p.village_code
          AND v_old.district_code = '3524141'
          AND v_new.district_code = '352410'
        LIMIT 1
    )
WHERE p.district_code = '3524141'
   OR p.village_code IN (SELECT code FROM tbm_villages WHERE district_code = '3524141');


-- ============================================================
-- STEP 2 — Hapus villages lama yang district_code = 3524141
-- ============================================================
DELETE FROM tbm_villages
WHERE district_code = '3524141';


-- ============================================================
-- STEP 3 — Hapus district lama 3524141
-- ============================================================
DELETE FROM tbm_districts
WHERE code = '3524141';


-- ============================================================
-- VERIFIKASI HASIL
-- ============================================================

-- Pastikan district lama hilang
SELECT 'sisa_districts_paciran' AS info, COUNT(*) AS jumlah
FROM tbm_districts
WHERE name ILIKE 'Paciran' AND regency_code = '3524';

-- Pastikan village dengan district 3524141 tidak ada lagi
SELECT 'sisa_villages_district_lama' AS info, COUNT(*) AS jumlah
FROM tbm_villages
WHERE district_code = '3524141';

-- Pastikan patients sudah ter-remap
SELECT 'patients_masih_refer_lama' AS info, COUNT(*) AS jumlah
FROM tbm_patients
WHERE district_code = '3524141'
   OR village_code LIKE '3524141%';

-- Detail district Paciran yang tersisa + jumlah desa-nya
SELECT
    d.code,
    d.name AS kecamatan,
    r.name AS kabupaten,
    COUNT(v.code) AS jumlah_desa
FROM tbm_districts d
JOIN tbm_regencies r ON r.code = d.regency_code
LEFT JOIN tbm_villages v ON v.district_code = d.code
WHERE d.name ILIKE 'Paciran' AND d.regency_code = '3524'
GROUP BY d.code, d.name, r.name;
