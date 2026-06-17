-- ===================================================================
-- SYNC PERMISSIONS — semua module Phase 0-1.7
-- ===================================================================
-- Idempotent: ON CONFLICT update display_name/module
-- Auto-grant ke role super_admin / superadmin / admin (kalau ada)
-- ===================================================================

BEGIN;

-- ============================================================
-- 1. Insert/Update Permissions
-- ============================================================
INSERT INTO tbm_permissions (name, display_name, module, description, is_active) VALUES
  -- Master
  ('sites.view',        'Lihat Klinik',          'master',  NULL, TRUE),
  ('sites.update',      'Ubah Klinik',           'master',  NULL, TRUE),
  ('users.view',        'Lihat Pengguna',        'master',  NULL, TRUE),
  ('users.create',      'Tambah Pengguna',       'master',  NULL, TRUE),
  ('users.update',      'Ubah Pengguna',         'master',  NULL, TRUE),
  ('users.delete',      'Hapus Pengguna',        'master',  NULL, TRUE),
  ('roles.view',        'Lihat Peran',           'master',  NULL, TRUE),
  ('roles.create',      'Tambah Peran',          'master',  NULL, TRUE),
  ('roles.update',      'Ubah Peran',            'master',  NULL, TRUE),
  ('roles.delete',      'Hapus Peran',           'master',  NULL, TRUE),
  ('doctors.view',      'Lihat Dokter',          'master',  NULL, TRUE),
  ('doctors.create',    'Tambah Dokter',         'master',  NULL, TRUE),
  ('doctors.update',    'Ubah Dokter',           'master',  NULL, TRUE),
  ('doctors.delete',    'Hapus Dokter',          'master',  NULL, TRUE),
  ('services.view',     'Lihat Layanan',         'master',  NULL, TRUE),
  ('services.create',   'Tambah Layanan',        'master',  NULL, TRUE),
  ('services.update',   'Ubah Layanan',          'master',  NULL, TRUE),
  ('services.delete',   'Hapus Layanan',         'master',  NULL, TRUE),
  ('medicines.view',    'Lihat Obat',            'master',  NULL, TRUE),
  ('medicines.create',  'Tambah Obat',           'master',  NULL, TRUE),
  ('medicines.update',  'Ubah Obat',             'master',  NULL, TRUE),
  ('medicines.delete',  'Hapus Obat',            'master',  NULL, TRUE),

  -- Pasien & Kunjungan
  ('patients.view',     'Lihat Pasien',          'patient', NULL, TRUE),
  ('patients.create',   'Tambah Pasien',         'patient', NULL, TRUE),
  ('patients.update',   'Ubah Pasien',           'patient', NULL, TRUE),
  ('patients.delete',   'Hapus Pasien',          'patient', NULL, TRUE),
  ('visits.view',       'Lihat Kunjungan',       'visit',   NULL, TRUE),
  ('visits.create',     'Tambah Kunjungan',      'visit',   NULL, TRUE),
  ('visits.update',     'Ubah Kunjungan',        'visit',   NULL, TRUE),
  ('visits.delete',     'Hapus Kunjungan',       'visit',   NULL, TRUE),

  -- Pelayanan
  ('kb.view',           'Lihat KB',              'kb',      NULL, TRUE),
  ('kb.create',         'Tambah KB',             'kb',      NULL, TRUE),
  ('kb.update',         'Ubah KB',               'kb',      NULL, TRUE),
  ('kb.delete',         'Hapus KB',              'kb',      NULL, TRUE),
  ('anc.view',          'Lihat ANC',             'anc',     NULL, TRUE),
  ('anc.create',        'Tambah ANC',            'anc',     NULL, TRUE),
  ('anc.update',        'Ubah ANC',              'anc',     NULL, TRUE),
  ('anc.delete',        'Hapus ANC',             'anc',     NULL, TRUE),
  ('inc.view',          'Lihat INC (Persalinan)','inc',     NULL, TRUE),
  ('inc.create',        'Tambah INC',            'inc',     NULL, TRUE),
  ('inc.update',        'Ubah INC',              'inc',     NULL, TRUE),
  ('inc.delete',        'Hapus INC',             'inc',     NULL, TRUE),
  ('pnc.view',          'Lihat PNC (Nifas)',     'pnc',     NULL, TRUE),
  ('pnc.create',        'Tambah PNC',            'pnc',     NULL, TRUE),
  ('pnc.update',        'Ubah PNC',              'pnc',     NULL, TRUE),
  ('pnc.delete',        'Hapus PNC',             'pnc',     NULL, TRUE),
  ('kn.view',           'Lihat KN (Neonatus)',   'kn',      NULL, TRUE),
  ('kn.create',         'Tambah KN',             'kn',      NULL, TRUE),
  ('kn.update',         'Ubah KN',               'kn',      NULL, TRUE),
  ('kn.delete',         'Hapus KN',              'kn',      NULL, TRUE),
  ('child.view',        'Lihat Anak/Imunisasi',  'child',   NULL, TRUE),
  ('child.create',      'Tambah Anak',           'child',   NULL, TRUE),
  ('child.update',      'Ubah Anak',             'child',   NULL, TRUE),
  ('child.delete',      'Hapus Anak',            'child',   NULL, TRUE)
ON CONFLICT (name) DO UPDATE
  SET display_name = EXCLUDED.display_name,
      module       = EXCLUDED.module,
      is_active    = EXCLUDED.is_active;

-- ============================================================
-- 2. Grant SEMUA permission ke role admin / superadmin
-- ============================================================
INSERT INTO tbm_role_permissions (role_id, permission_id, granted_at)
SELECT r.id, p.id, CURRENT_TIMESTAMP
FROM tbm_roles r
CROSS JOIN tbm_permissions p
WHERE r.name IN ('admin', 'superadmin', 'super_admin', 'super-admin')
ON CONFLICT DO NOTHING;

COMMIT;

-- ============================================================
-- VERIFIKASI
-- ============================================================
SELECT 'tbm_permissions'       AS tabel, COUNT(*) AS rows FROM tbm_permissions
UNION ALL
SELECT 'tbm_role_permissions', COUNT(*)                  FROM tbm_role_permissions;

-- List role + permission count
SELECT r.name AS role, COUNT(rp.permission_id) AS total_permissions
FROM tbm_roles r
LEFT JOIN tbm_role_permissions rp ON rp.role_id = r.id
GROUP BY r.id, r.name
ORDER BY r.name;
