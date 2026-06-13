-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.0 — Foundation Seed
-- ORDER   : Jalankan SETELAH 03_DDL_PHASE1_FOUNDATION.sql
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. PAYER TYPES (BPJS / UMUM / ASURANSI)
-- =====================================================================
INSERT INTO tbm_payer_types (code, name, description, sort_order) VALUES
    ('PAY-BPJS',     'BPJS Kesehatan', 'Pembiayaan BPJS Kesehatan',                   1),
    ('PAY-UMUM',     'Umum/Mandiri',   'Pembayaran tunai oleh pasien (mandiri)',     2),
    ('PAY-ASURANSI', 'Asuransi Swasta','Pembiayaan asuransi swasta lainnya',          3)
ON CONFLICT (code) DO NOTHING;

-- =====================================================================
-- 2. KONTRASEPSI METHODS (KONDOM/PIL/SUNTIK/IUD/IMPLAN)
-- =====================================================================
INSERT INTO tbm_kontrasepsi_methods (code, name, method_type, description, sort_order) VALUES
    ('KTR-KONDOM', 'Kondom',  'jangka_pendek',  'Kontrasepsi barrier, sekali pakai',                    1),
    ('KTR-PIL',    'Pil KB',  'jangka_pendek',  'Kontrasepsi oral hormonal, per cycle',                 2),
    ('KTR-SUNTIK', 'Suntik',  'jangka_pendek',  'Kontrasepsi injeksi (1 bulan / 3 bulan)',              3),
    ('KTR-IUD',    'IUD/Spiral','jangka_panjang','Intra Uterine Device, jangka panjang (3-10 tahun)',  4),
    ('KTR-IMPLAN', 'Implan',  'jangka_panjang', 'Implan subdermal hormonal (3-5 tahun)',                5)
ON CONFLICT (code) DO NOTHING;

-- =====================================================================
-- 3. IMMUNIZATION TYPES (BCG/HB-DTP/POLIO/HEPATITIS-B/CAMPAK)
-- =====================================================================
INSERT INTO tbm_immunization_types (code, name, target_group, max_dose, description, sort_order) VALUES
    ('IMU-BCG',     'BCG',         'bayi', 1, 'Bacillus Calmette-Guerin (TBC) — 1 dose, biasanya saat lahir',         1),
    ('IMU-HBDTP',   'HB-DTP',      'bayi', 4, 'Hepatitis B + Difteri/Pertusis/Tetanus — 4 dose (I-IV)',               2),
    ('IMU-POLIO',   'Polio',       'bayi', 4, 'Poliomyelitis — 4 dose (I-IV)',                                         3),
    ('IMU-HEPB',    'Hepatitis B', 'bayi', 1, 'Hepatitis B — 1 dose (saat lahir / HB-0)',                              4),
    ('IMU-CAMPAK',  'Campak',      'bayi', 1, 'Campak (MMR) — 1 dose, biasanya umur 9 bulan',                          5)
ON CONFLICT (code) DO NOTHING;

-- =====================================================================
-- 4. EDUCATION LEVELS
-- =====================================================================
INSERT INTO tbm_education_levels (code, name, sort_order) VALUES
    ('EDU-TIDAKSEKOLAH', 'Tidak Sekolah',    1),
    ('EDU-SD',           'SD/Sederajat',     2),
    ('EDU-SMP',          'SMP/Sederajat',    3),
    ('EDU-SMA',          'SMA/SMU/Sederajat',4),
    ('EDU-D1',           'D1',               5),
    ('EDU-D2',           'D2',               6),
    ('EDU-D3',           'D3',               7),
    ('EDU-D4',           'D4',               8),
    ('EDU-S1',           'S1/Sarjana',       9),
    ('EDU-S2',           'S2/Magister',     10),
    ('EDU-S3',           'S3/Doktor',       11)
ON CONFLICT (code) DO NOTHING;

-- =====================================================================
-- 5. RELIGIONS
-- =====================================================================
INSERT INTO tbm_religions (code, name, sort_order) VALUES
    ('AGM-ISLAM',    'Islam',     1),
    ('AGM-KRISTEN',  'Kristen',   2),
    ('AGM-KATOLIK',  'Katolik',   3),
    ('AGM-HINDU',    'Hindu',     4),
    ('AGM-BUDDHA',   'Buddha',    5),
    ('AGM-KONGHUCU', 'Konghucu',  6),
    ('AGM-LAINNYA',  'Lainnya',   7)
ON CONFLICT (code) DO NOTHING;

-- =====================================================================
-- 6. WILAYAH KEMENDAGRI — JATIM (subset minimal: Lamongan + sekitarnya)
-- =====================================================================
-- NOTE: Untuk demo Phase 1, seed Jatim cukup 1 provinsi + 2 kab + 5 kec + 10 desa
--       Production: import full dataset Kemendagri/BPS via script terpisah

-- 6.1 PROVINCE
INSERT INTO tbm_provinces (code, name) VALUES
    ('35', 'Jawa Timur')
ON CONFLICT (code) DO NOTHING;

-- 6.2 REGENCIES (Kab/Kota di Jatim — subset, focus Lamongan + Gresik untuk demo 2 site)
INSERT INTO tbm_regencies (code, province_code, name, type) VALUES
    ('3524', '35', 'Kab. Lamongan', 'kabupaten'),
    ('3525', '35', 'Kab. Gresik',   'kabupaten'),
    ('3578', '35', 'Kota Surabaya', 'kota')
ON CONFLICT (code) DO NOTHING;

-- 6.3 DISTRICTS (Kecamatan)
INSERT INTO tbm_districts (code, regency_code, name) VALUES
    -- Lamongan
    ('3524141', '3524', 'Paciran'),
    ('3524150', '3524', 'Brondong'),
    ('3524160', '3524', 'Solokuro'),
    ('3524010', '3524', 'Sukorame'),
    ('3524020', '3524', 'Bluluk'),
    ('3524130', '3524', 'Lamongan'),
    -- Gresik
    ('3525010', '3525', 'Wringinanom'),
    ('3525020', '3525', 'Driyorejo'),
    ('3525190', '3525', 'Manyar')
ON CONFLICT (code) DO NOTHING;

-- 6.4 VILLAGES (Desa/Kelurahan — focus Paciran karena ini lokasi klinik Bu Tin)
INSERT INTO tbm_villages (code, district_code, name, type, postal_code) VALUES
    -- Paciran (3524141) — Lokasi Pondok Bersalin Bu Tin
    ('3524141001', '3524141', 'Tunggul',          'desa', '62264'),
    ('3524141002', '3524141', 'Tlogosadang',      'desa', '62264'),
    ('3524141003', '3524141', 'Paciran',          'desa', '62264'),
    ('3524141004', '3524141', 'Sumur Gayam',      'desa', '62264'),
    ('3524141005', '3524141', 'Kemantren',        'desa', '62264'),
    ('3524141006', '3524141', 'Sidokelar',        'desa', '62264'),
    ('3524141007', '3524141', 'Sidokumpul',       'desa', '62264'),
    ('3524141008', '3524141', 'Drajat',           'desa', '62264'),
    ('3524141009', '3524141', 'Banjarwati',       'desa', '62264'),
    ('3524141010', '3524141', 'Kandangsemangkon', 'desa', '62264'),
    -- Brondong
    ('3524150001', '3524150', 'Brondong',         'desa', '62263'),
    ('3524150002', '3524150', 'Sedayulawas',      'desa', '62263')
ON CONFLICT (code) DO NOTHING;

-- =====================================================================
-- 7. DOCUMENT SEQUENCES SEED
--    Per site × per doc_type × per kategori (jika applicable)
-- =====================================================================
-- Format pattern reference:
--   {SITE2}-{YYYY}-{NNNNNN}   → 01-2026-000001 (no_rm, kartu, persalinan)
--   {KAT}-{SITE2}-{YYYY}-{NNNNNN} → I-01-2026-000123 (register kunjungan)
--   {KAT}-{SITE2}-{YYYYMMDD}-{NNNN} → RX-01-20260610-0123 (resep harian)

-- Helper: kita seed untuk site_id 1 (Bu Tin) dan 2 (Amanah)
-- Doc types: RM, MH, KB, BB, PS, NF (per tahun) + REG (per kategori) + RX, INV, STK (per hari)

-- 7.1 No. RM Pasien (per tahun, no category)
INSERT INTO tbs_document_sequences (site_id, doc_type, category, prefix, current_number, reset_period, format_pattern) VALUES
    (1, 'RM', NULL, '', 0, 'yearly', '{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'RM', NULL, '', 0, 'yearly', '{SITE2}-{YYYY}-{NNNNNN}');

-- 7.2 No. Register Kunjungan (per tahun, per kategori A/I/K/R)
INSERT INTO tbs_document_sequences (site_id, doc_type, category, prefix, current_number, reset_period, format_pattern) VALUES
    (1, 'REG', 'A', 'A', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (1, 'REG', 'I', 'I', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (1, 'REG', 'K', 'K', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (1, 'REG', 'R', 'R', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'REG', 'A', 'A', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'REG', 'I', 'I', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'REG', 'K', 'K', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'REG', 'R', 'R', 0, 'yearly', '{KAT}-{SITE2}-{YYYY}-{NNNNNN}');

-- 7.3 No. Kartu (per tahun, no category)
INSERT INTO tbs_document_sequences (site_id, doc_type, category, prefix, current_number, reset_period, format_pattern) VALUES
    (1, 'MH', NULL, 'MH', 0, 'yearly', 'MH-{SITE2}-{YYYY}-{NNNNNN}'),  -- Kartu Ibu Hamil
    (1, 'KB', NULL, 'KB', 0, 'yearly', 'KB-{SITE2}-{YYYY}-{NNNNNN}'),  -- Kartu KB Akseptor
    (1, 'BB', NULL, 'BB', 0, 'yearly', 'BB-{SITE2}-{YYYY}-{NNNNNN}'),  -- Kartu Bayi/Anak
    (1, 'PS', NULL, 'PS', 0, 'yearly', 'PS-{SITE2}-{YYYY}-{NNNNNN}'),  -- Persalinan
    (1, 'NF', NULL, 'NF', 0, 'yearly', 'NF-{SITE2}-{YYYY}-{NNNNNN}'),  -- Nifas
    (2, 'MH', NULL, 'MH', 0, 'yearly', 'MH-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'KB', NULL, 'KB', 0, 'yearly', 'KB-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'BB', NULL, 'BB', 0, 'yearly', 'BB-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'PS', NULL, 'PS', 0, 'yearly', 'PS-{SITE2}-{YYYY}-{NNNNNN}'),
    (2, 'NF', NULL, 'NF', 0, 'yearly', 'NF-{SITE2}-{YYYY}-{NNNNNN}');

-- 7.4 Transaksi Harian (Resep / Invoice / Stok — reset daily)
INSERT INTO tbs_document_sequences (site_id, doc_type, category, prefix, current_number, reset_period, format_pattern) VALUES
    (1, 'RX',  NULL, 'RX',  0, 'daily', 'RX-{SITE2}-{YYYYMMDD}-{NNNN}'),
    (1, 'INV', NULL, 'INV', 0, 'daily', 'INV-{SITE2}-{YYYYMMDD}-{NNNN}'),
    (1, 'STK', NULL, 'STK', 0, 'daily', 'STK-{SITE2}-{YYYYMMDD}-{NNNN}'),
    (2, 'RX',  NULL, 'RX',  0, 'daily', 'RX-{SITE2}-{YYYYMMDD}-{NNNN}'),
    (2, 'INV', NULL, 'INV', 0, 'daily', 'INV-{SITE2}-{YYYYMMDD}-{NNNN}'),
    (2, 'STK', NULL, 'STK', 0, 'daily', 'STK-{SITE2}-{YYYYMMDD}-{NNNN}');

-- 7.5 Employee ID (cumulative, never reset)
INSERT INTO tbs_document_sequences (site_id, doc_type, category, prefix, current_number, reset_period, format_pattern) VALUES
    (1, 'EMP', NULL, 'EMP', 0, 'never', 'EMP-{SITE2}-{NNNN}'),
    (2, 'EMP', NULL, 'EMP', 0, 'never', 'EMP-{SITE2}-{NNNN}');

-- =====================================================================
-- 8. PERMISSIONS BARU (master baru perlu permission)
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    -- Master baru
    ('payer_types.view',          'Lihat Jenis Pembiayaan',     'master', 'Akses master payer types'),
    ('payer_types.manage',        'Kelola Jenis Pembiayaan',    'master', 'CRUD payer types (super admin)'),
    ('kontrasepsi.view',          'Lihat Alat Kontrasepsi',     'master', 'Akses master kontrasepsi'),
    ('kontrasepsi.manage',        'Kelola Alat Kontrasepsi',    'master', 'CRUD kontrasepsi (super admin)'),
    ('immunization_types.view',   'Lihat Jenis Imunisasi',      'master', 'Akses master imunisasi'),
    ('immunization_types.manage', 'Kelola Jenis Imunisasi',     'master', 'CRUD imunisasi (super admin)'),
    ('wilayah.view',              'Lihat Wilayah',              'master', 'Akses wilayah (province/regency/district/village)'),
    ('wilayah.manage',            'Kelola Wilayah',             'master', 'Import/edit wilayah (super admin)')
ON CONFLICT (name) DO NOTHING;

-- Grant master baru ke super_admin
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r
CROSS JOIN tbm_permissions p
WHERE r.name = 'super_admin'
  AND p.name IN (
      'payer_types.view', 'payer_types.manage',
      'kontrasepsi.view', 'kontrasepsi.manage',
      'immunization_types.view', 'immunization_types.manage',
      'wilayah.view', 'wilayah.manage'
  )
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- Grant view-only ke admin biasa
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r
CROSS JOIN tbm_permissions p
WHERE r.name = 'admin'
  AND p.name IN (
      'payer_types.view',
      'kontrasepsi.view',
      'immunization_types.view',
      'wilayah.view'
  )
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- =====================================================================
-- DONE Phase 1.0 Seed
-- Verifikasi: SELECT count(*) dari masing-masing tabel
-- =====================================================================

-- Verifikasi cepat (uncomment kalau mau lihat):
-- SELECT 'payer_types' AS tbl, COUNT(*) FROM tbm_payer_types
-- UNION ALL SELECT 'kontrasepsi', COUNT(*) FROM tbm_kontrasepsi_methods
-- UNION ALL SELECT 'immunization', COUNT(*) FROM tbm_immunization_types
-- UNION ALL SELECT 'education', COUNT(*) FROM tbm_education_levels
-- UNION ALL SELECT 'religion', COUNT(*) FROM tbm_religions
-- UNION ALL SELECT 'province', COUNT(*) FROM tbm_provinces
-- UNION ALL SELECT 'regency', COUNT(*) FROM tbm_regencies
-- UNION ALL SELECT 'district', COUNT(*) FROM tbm_districts
-- UNION ALL SELECT 'village', COUNT(*) FROM tbm_villages
-- UNION ALL SELECT 'sequences', COUNT(*) FROM tbs_document_sequences;

-- Test generator (uncomment kalau mau test):
-- SELECT fn_next_doc_number(1, 'RM')           AS no_rm;          -- 01-2026-000001
-- SELECT fn_next_doc_number(1, 'REG', 'I')     AS reg_ibu;        -- I-01-2026-000001
-- SELECT fn_next_doc_number(1, 'MH')           AS kartu_hamil;    -- MH-01-2026-000001
-- SELECT fn_next_doc_number(1, 'RX')           AS no_resep;       -- RX-01-20260610-0001
