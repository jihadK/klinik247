-- ===================================================================
-- SEED tbm_immunization_types — Lengkapi jenis imunisasi Kemenkes RI
-- ===================================================================
-- Idempotent: pakai ON CONFLICT (code) DO UPDATE
-- Existing rows (BCG/HB-DTP/Polio/HEPB/CAMPAK) di-preserve & update label
-- Tambahan: PCV, Rotavirus, JE, Booster (DPT/MR), HPV, Influenza
-- ===================================================================

INSERT INTO tbm_immunization_types
    (code, name, target_group, max_dose, description, sort_order, is_active)
VALUES
  -- Imunisasi dasar (existing — preserve)
  ('IMU-HEPB',    'Hepatitis B (HB-0)',           'newborn',   1, 'Hepatitis B dosis 0 — diberi < 24 jam setelah lahir',  10, TRUE),
  ('IMU-BCG',     'BCG',                          'baby',      1, 'BCG — usia 1 bulan, mencegah TBC',                     20, TRUE),
  ('IMU-POLIO',   'Polio (OPV/IPV)',              'baby',      4, 'Polio tetes/suntik — usia 1, 2, 3, 4 bulan',           30, TRUE),
  ('IMU-HBDTP',   'DPT-HB-Hib (Pentavalen)',      'baby',      3, 'DPT + HB + Hib — usia 2, 3, 4 bulan',                  40, TRUE),
  ('IMU-CAMPAK',  'Campak / MR',                  'baby',      2, 'Campak Rubella — usia 9 bln + 18 bln (booster)',       50, TRUE),

  -- Imunisasi tambahan (rekomendasi IDAI / Kemenkes)
  ('IMU-PCV',     'PCV (Pneumokokus)',            'baby',      3, 'Pneumococcal Conjugate Vaccine — usia 2, 3, 12 bulan', 60, TRUE),
  ('IMU-ROTA',    'Rotavirus',                    'baby',      3, 'Vaksin rotavirus — usia 2, 3, 4 bulan',                70, TRUE),
  ('IMU-JE',      'JE (Japanese Encephalitis)',   'baby',      1, 'JE — usia 9 bulan (daerah endemik)',                   80, TRUE),

  -- Booster (untuk anak usia lebih besar)
  ('IMU-DPTBST',  'DPT Booster',                  'toddler',   2, 'Booster DPT — usia 18 bulan + 5-7 tahun',              90, TRUE),
  ('IMU-MRBST',   'MR Booster',                   'toddler',   2, 'Booster Campak/Rubella — usia 18 bln + kelas 1 SD',   100, TRUE),

  -- Imunisasi anak sekolah
  ('IMU-HPV',     'HPV',                          'school',    2, 'HPV — anak perempuan kelas 5-6 SD',                   110, TRUE),
  ('IMU-INFLU',   'Influenza',                    'baby',      1, 'Influenza — tahunan, mulai usia 6 bulan',             120, TRUE)
ON CONFLICT (code) DO UPDATE SET
    name         = EXCLUDED.name,
    target_group = EXCLUDED.target_group,
    max_dose     = EXCLUDED.max_dose,
    description  = EXCLUDED.description,
    sort_order   = EXCLUDED.sort_order,
    is_active    = EXCLUDED.is_active,
    updated_date = CURRENT_TIMESTAMP;


-- ============================================================
-- VERIFIKASI
-- ============================================================
SELECT code, name, target_group, max_dose, sort_order, is_active
FROM tbm_immunization_types
ORDER BY sort_order;
