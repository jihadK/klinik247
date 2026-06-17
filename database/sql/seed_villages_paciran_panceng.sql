-- ===================================================================
-- SEED VILLAGES — Kecamatan Paciran (Lamongan) + Panceng (Gresik)
-- ===================================================================
-- Plain SQL, no procedure. Cocok untuk DBeaver/pgAdmin manual execute.
-- IDEMPOTENT — aman dijalankan berulang (ON CONFLICT handled)
--
-- Kode wilayah berdasarkan Permendagri 137/2017:
--   - Province  : 2 digit  (35      = Jatim)
--   - Regency   : 4 digit  (3524    = Lamongan, 3525 = Gresik)
--   - District  : 6 digit  (352410  = Paciran,  352518 = Panceng)
--   - Village   : 10 digit (district + 4 digit sequential)
--                 prefix '2' = desa, '1' = kelurahan
-- ===================================================================

-- ============================================================
-- 1. Province Jatim
-- ============================================================
INSERT INTO tbm_provinces (code, name, is_active) VALUES
  ('35', 'Jawa Timur', TRUE)
ON CONFLICT (code) DO NOTHING;


-- ============================================================
-- 2. Regency Lamongan + Gresik
-- ============================================================
INSERT INTO tbm_regencies (code, province_code, name, type, is_active) VALUES
  ('3524', '35', 'Lamongan', 'kabupaten', TRUE),
  ('3525', '35', 'Gresik',   'kabupaten', TRUE)
ON CONFLICT (code) DO NOTHING;


-- ============================================================
-- 3. District Paciran (Lamongan) + Panceng (Gresik)
-- ============================================================
INSERT INTO tbm_districts (code, regency_code, name, is_active) VALUES
  ('352410', '3524', 'Paciran', TRUE),
  ('352518', '3525', 'Panceng', TRUE)
ON CONFLICT (code) DO NOTHING;


-- ============================================================
-- 4. Villages Paciran (17 desa, kode pos 62264)
-- ============================================================
INSERT INTO tbm_villages (code, district_code, name, type, postal_code, is_active) VALUES
  ('3524102001', '352410', 'Banjarwati',       'desa', '62264', TRUE),
  ('3524102002', '352410', 'Blimbing',         'desa', '62264', TRUE),
  ('3524102003', '352410', 'Drajat',           'desa', '62264', TRUE),
  ('3524102004', '352410', 'Kandangsemangkon', 'desa', '62264', TRUE),
  ('3524102005', '352410', 'Kemantren',        'desa', '62264', TRUE),
  ('3524102006', '352410', 'Kranji',           'desa', '62264', TRUE),
  ('3524102007', '352410', 'Paciran',          'desa', '62264', TRUE),
  ('3524102008', '352410', 'Paloh',            'desa', '62264', TRUE),
  ('3524102009', '352410', 'Sendangagung',     'desa', '62264', TRUE),
  ('3524102010', '352410', 'Sendangduwur',     'desa', '62264', TRUE),
  ('3524102011', '352410', 'Sidokelar',        'desa', '62264', TRUE),
  ('3524102012', '352410', 'Sidokumpul',       'desa', '62264', TRUE),
  ('3524102013', '352410', 'Sumurgayam',       'desa', '62264', TRUE),
  ('3524102014', '352410', 'Tlogosadang',      'desa', '62264', TRUE),
  ('3524102015', '352410', 'Tunggul',          'desa', '62264', TRUE),
  ('3524102016', '352410', 'Warulor',          'desa', '62264', TRUE),
  ('3524102017', '352410', 'Weru',             'desa', '62264', TRUE)
ON CONFLICT (code) DO UPDATE SET
  name        = EXCLUDED.name,
  postal_code = EXCLUDED.postal_code,
  is_active   = EXCLUDED.is_active;


-- ============================================================
-- 5. Villages Panceng (14 desa, kode pos 61156)
-- ============================================================
INSERT INTO tbm_villages (code, district_code, name, type, postal_code, is_active) VALUES
  ('3525182001', '352518', 'Banyutengah', 'desa', '61156', TRUE),
  ('3525182002', '352518', 'Campurejo',   'desa', '61156', TRUE),
  ('3525182003', '352518', 'Dalegan',     'desa', '61156', TRUE),
  ('3525182004', '352518', 'Doudo',       'desa', '61156', TRUE),
  ('3525182005', '352518', 'Ketanen',     'desa', '61156', TRUE),
  ('3525182006', '352518', 'Pantenan',    'desa', '61156', TRUE),
  ('3525182007', '352518', 'Petung',      'desa', '61156', TRUE),
  ('3525182008', '352518', 'Prupuh',      'desa', '61156', TRUE),
  ('3525182009', '352518', 'Serah',       'desa', '61156', TRUE),
  ('3525182010', '352518', 'Siwalan',     'desa', '61156', TRUE),
  ('3525182011', '352518', 'Sukodono',    'desa', '61156', TRUE),
  ('3525182012', '352518', 'Sumurber',    'desa', '61156', TRUE),
  ('3525182013', '352518', 'Surowiti',    'desa', '61156', TRUE),
  ('3525182014', '352518', 'Wotan',       'desa', '61156', TRUE)
ON CONFLICT (code) DO UPDATE SET
  name        = EXCLUDED.name,
  postal_code = EXCLUDED.postal_code,
  is_active   = EXCLUDED.is_active;


-- ============================================================
-- VERIFIKASI HASIL
-- ============================================================

-- Summary
SELECT
    p.name        AS provinsi,
    r.name        AS kabupaten,
    d.name        AS kecamatan,
    COUNT(v.code) AS jumlah_desa
FROM tbm_districts d
JOIN tbm_regencies r ON r.code = d.regency_code
JOIN tbm_provinces p ON p.code = r.province_code
LEFT JOIN tbm_villages v ON v.district_code = d.code
WHERE d.code IN ('352410', '352518')
GROUP BY p.name, r.name, d.name
ORDER BY r.name, d.name;

-- Detail desa Paciran
SELECT code, name, type, postal_code
FROM tbm_villages
WHERE district_code = '352410'
ORDER BY name;

-- Detail desa Panceng
SELECT code, name, type, postal_code
FROM tbm_villages
WHERE district_code = '352518'
ORDER BY name;
