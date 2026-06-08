-- =====================================================================
-- SEED Data Awal Klinik247 — Phase 0
-- DEPS: Jalankan SETELAH 01_DDL_KLINIK.sql
-- NOTE: Permissions terkait transaksi (appointments/visits/payments)
--       SKIP dulu — akan ditambahkan saat modul transaksi dibuat
-- =====================================================================

BEGIN;

-- =====================================================================
-- 1. SPECIALTIES (master global)
-- =====================================================================
INSERT INTO tbm_specialties (code, name, description) VALUES
    ('UMUM',       'Dokter Umum',               'Pelayanan umum/general practitioner'),
    ('GIGI',       'Dokter Gigi',               'Pelayanan gigi & mulut'),
    ('ANAK',       'Spesialis Anak',            'Pelayanan kesehatan anak'),
    ('KANDUNGAN',  'Spesialis Kandungan',       'Obstetri & ginekologi'),
    ('BIDAN',      'Bidan',                     'Pelayanan kebidanan'),
    ('PERAWAT',    'Perawat',                   'Pelayanan keperawatan'),
    ('FARMASI',    'Farmasi',                   'Pelayanan obat & resep');

-- =====================================================================
-- 2. ROLES
-- =====================================================================
INSERT INTO tbm_roles (name, description, is_super) VALUES
    ('super_admin',  'Super Admin (akses semua klinik)',                TRUE),
    ('admin',        'Admin klinik (kelola data + user)',               FALSE),
    ('dokter',       'Dokter (rekam medis + resep)',                    FALSE),
    ('perawat',      'Perawat (vital signs + asisten)',                 FALSE),
    ('kasir',        'Kasir (pembayaran)',                              FALSE),
    ('pendaftaran',  'Pendaftaran (registrasi pasien + appointment)',   FALSE);

-- =====================================================================
-- 3. PERMISSIONS (Phase 0 — Master/Auth/System saja)
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    -- Sites (super admin only)
    ('sites.view',          'View Sites',            'site',  'Lihat daftar klinik'),
    ('sites.create',        'Create Site',           'site',  'Tambah klinik baru'),
    ('sites.update',        'Update Site',           'site',  'Edit klinik'),
    ('sites.delete',        'Delete Site',           'site',  'Hapus klinik'),

    -- Users
    ('users.view',          'View Users',            'auth',  'Lihat daftar user'),
    ('users.create',        'Create User',           'auth',  'Tambah user'),
    ('users.update',        'Update User',           'auth',  'Edit user'),
    ('users.delete',        'Delete User',           'auth',  'Hapus user'),
    ('users.reset_password','Reset Password',        'auth',  'Reset password user'),

    -- Roles
    ('roles.view',          'View Roles',            'auth',  'Lihat roles'),
    ('roles.manage',        'Manage Roles',          'auth',  'Kelola role & permission'),

    -- Specialties
    ('specialties.view',    'View Specialties',      'master','Lihat spesialisasi'),
    ('specialties.manage',  'Manage Specialties',    'master','Kelola spesialisasi'),

    -- Doctors
    ('doctors.view',        'View Doctors',          'master','Lihat dokter'),
    ('doctors.create',      'Create Doctor',         'master','Tambah dokter'),
    ('doctors.update',      'Update Doctor',         'master','Edit dokter'),
    ('doctors.delete',      'Delete Doctor',         'master','Hapus dokter'),

    -- Patients
    ('patients.view',       'View Patients',         'master','Lihat pasien'),
    ('patients.create',     'Create Patient',        'master','Tambah pasien'),
    ('patients.update',     'Update Patient',        'master','Edit pasien'),
    ('patients.delete',     'Delete Patient',        'master','Hapus pasien'),

    -- Services
    ('services.view',       'View Services',         'master','Lihat layanan'),
    ('services.manage',     'Manage Services',       'master','Kelola layanan + tarif'),

    -- Medicines
    ('medicines.view',      'View Medicines',        'master','Lihat obat'),
    ('medicines.manage',    'Manage Medicines',      'master','Kelola obat + stok'),

    -- Schedules
    ('schedules.view',      'View Schedules',        'schedule','Lihat jadwal mingguan'),
    ('schedules.manage',    'Manage Schedules',      'schedule','Kelola jadwal mingguan'),

    -- Settings
    ('settings.view',       'View Settings',         'system', 'Lihat pengaturan'),
    ('settings.manage',     'Manage Settings',       'system', 'Ubah pengaturan'),

    -- Audit
    ('audit.view',          'View Audit Log',        'system', 'Lihat audit log');

-- =====================================================================
-- 4. ROLE-PERMISSION MATRIX (Phase 0)
-- =====================================================================

-- super_admin: SEMUA
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'super_admin';

-- admin: SEMUA kecuali sites.*
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'admin' AND p.name NOT LIKE 'sites.%';

-- dokter: lihat master + kelola jadwal sendiri (transaksi nanti)
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'dokter' AND p.name IN (
    'doctors.view','patients.view','services.view','medicines.view',
    'schedules.view','schedules.manage'
);

-- perawat: view master saja
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'perawat' AND p.name IN (
    'patients.view','services.view','medicines.view','schedules.view'
);

-- kasir: view master (transaksi nanti)
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'kasir' AND p.name IN (
    'patients.view','services.view','medicines.view'
);

-- pendaftaran: kelola pasien + view dokter/jadwal
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran' AND p.name IN (
    'doctors.view','services.view','schedules.view',
    'patients.view','patients.create','patients.update'
);

-- =====================================================================
-- 5. SITES (2 demo)
-- =====================================================================
INSERT INTO tbm_sites (code, name, slug, address, city, phone, email, is_active) VALUES
    ('KLN-001', 'Pondok Bersalin Bu Tin', 'pondok-bu-tin',
     'Jl. Desa Sukamaju RT 02/RW 04', 'Jepara', '081234567001',
     'admin@pondokbutin.klinik247.id', TRUE),
    ('KLN-002', 'Klinik Amanah', 'amanah',
     'Jl. Raya Amanah No. 12', 'Demak', '081234567002',
     'admin@amanah.klinik247.id', TRUE);

-- =====================================================================
-- 6. DOCUMENT SEQUENCES — RM saja (untuk no_rm pasien)
-- =====================================================================
INSERT INTO tbs_document_sequences (site_id, doc_type, prefix, reset_period)
SELECT s.id, 'RM', 'RM-', 'yearly' FROM tbm_sites s;

-- =====================================================================
-- 7. SETTINGS DEFAULT (per site)
-- =====================================================================
INSERT INTO tbs_settings (site_id, key, value, description)
SELECT s.id, k.key, k.value, k.description
FROM tbm_sites s
CROSS JOIN (VALUES
    ('opening_hours',       '{"mon":"08:00-21:00","tue":"08:00-21:00","wed":"08:00-21:00","thu":"08:00-21:00","fri":"08:00-21:00","sat":"08:00-15:00","sun":"closed"}', 'Jam buka klinik per hari'),
    ('default_consultation_fee', '50000', 'Tarif konsultasi default (Rp)'),
    ('print_logo_on_receipt', 'true', 'Cetak logo di kuitansi')
) AS k(key, value, description);

-- =====================================================================
-- 8. USERS — 1 super admin + 1 admin per site
-- Password placeholder: bcrypt dari "AdminKlinik123"
-- ⚠️  WAJIB regenerate password via tinker setelah seed:
--     User::where('username','superadmin')->first()->update(['password_hash' => Hash::make('PASSWORD_BARU')]);
-- =====================================================================
INSERT INTO tbm_users (site_id, role_id, username, email, password_hash, full_name, phone, is_active) VALUES
    -- Super admin (site_id = NULL → akses semua klinik)
    (NULL, (SELECT id FROM tbm_roles WHERE name='super_admin'),
     'superadmin', 'super@klinik247.id',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Super Administrator', '081234567000', TRUE),

    -- Admin Site 1 (Bu Tin)
    ((SELECT id FROM tbm_sites WHERE code='KLN-001'),
     (SELECT id FROM tbm_roles WHERE name='admin'),
     'admin', 'admin@pondokbutin.id',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Admin Pondok Bu Tin', '081234567101', TRUE),

    -- Admin Site 2 (Amanah)
    ((SELECT id FROM tbm_sites WHERE code='KLN-002'),
     (SELECT id FROM tbm_roles WHERE name='admin'),
     'admin', 'admin@amanah.id',
     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
     'Admin Klinik Amanah', '081234567201', TRUE);

COMMIT;

-- =====================================================================
-- VERIFIKASI
-- =====================================================================
SELECT 'Sites:'        AS info, COUNT(*) AS jumlah FROM tbm_sites UNION ALL
SELECT 'Roles:',       COUNT(*) FROM tbm_roles UNION ALL
SELECT 'Permissions:', COUNT(*) FROM tbm_permissions UNION ALL
SELECT 'Users:',       COUNT(*) FROM tbm_users UNION ALL
SELECT 'Specialties:', COUNT(*) FROM tbm_specialties UNION ALL
SELECT 'Sequences:',   COUNT(*) FROM tbs_document_sequences UNION ALL
SELECT 'Settings:',    COUNT(*) FROM tbs_settings;
