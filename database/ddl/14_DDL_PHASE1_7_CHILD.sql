-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.7 — Bayi/Anak (Imunisasi + Tumbuh Kembang)
-- DESC    : Modul setelah KN3 selesai (umur > 28 hari) hingga 5 tahun
--           - Imunisasi sesuai jadwal IDAI/Kemenkes
--           - Kunjungan KMS (Kartu Menuju Sehat) tumbuh kembang
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. tbr_immunization_records — Record pemberian imunisasi (per dose)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_immunization_records (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    neonate_id        BIGINT NOT NULL REFERENCES tbr_neonates(id) ON DELETE CASCADE,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id),       -- ID ibu (untuk filter)

    immunization_type_id BIGINT NOT NULL REFERENCES tbm_immunization_types(id),
    dose_number       INT NOT NULL CHECK (dose_number BETWEEN 1 AND 5),

    given_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    given_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    given_by          BIGINT REFERENCES tbm_users(id),

    no_batch          VARCHAR(50),
    tempat            VARCHAR(100),                                       -- klinik/posyandu/puskesmas
    catatan           TEXT,
    side_effects      TEXT,                                                -- KIPI (Kejadian Ikutan Pasca Imunisasi)

    next_due_date     DATE,                                                -- jadwal dose berikutnya

    created_by        BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,

    CONSTRAINT uq_imm_neonate_type_dose UNIQUE (neonate_id, immunization_type_id, dose_number)
);

CREATE INDEX IF NOT EXISTS idx_imm_neonate ON tbr_immunization_records(neonate_id);
CREATE INDEX IF NOT EXISTS idx_imm_type    ON tbr_immunization_records(immunization_type_id);
CREATE INDEX IF NOT EXISTS idx_imm_date    ON tbr_immunization_records(given_date DESC);

DROP TRIGGER IF EXISTS trg_tbr_imm_updated ON tbr_immunization_records;
CREATE TRIGGER trg_tbr_imm_updated BEFORE UPDATE ON tbr_immunization_records
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 2. tbr_child_visits — Kunjungan Anak (KMS Tumbuh Kembang + Sakit)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_child_visits (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    neonate_id        BIGINT NOT NULL REFERENCES tbr_neonates(id) ON DELETE CASCADE,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id),       -- ID ibu

    visit_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    visit_time        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    visit_type        VARCHAR(20) CHECK (visit_type IN ('rutin','sakit','imunisasi','kontrol_balik','lainnya') OR visit_type IS NULL),

    umur_hari         INT,                                                -- auto-calc dari neonate.tanggal_lahir
    umur_label        VARCHAR(50),                                        -- "3 bulan 12 hari"

    -- ===== KMS — Tumbuh Kembang =====
    berat_badan_gram  INT,
    panjang_badan_cm  NUMERIC(5,1),                                       -- atau tinggi badan
    lingkar_kepala_cm NUMERIC(4,1),
    lingkar_lengan_cm NUMERIC(4,1),
    suhu_celcius      NUMERIC(3,1),

    -- Status Gizi (KMS)
    status_gizi       VARCHAR(20) CHECK (status_gizi IN ('gizi_buruk','gizi_kurang','gizi_baik','gizi_lebih','obesitas') OR status_gizi IS NULL),
    stunting          BOOLEAN DEFAULT FALSE,                              -- TB/U sangat pendek/pendek
    wasting           BOOLEAN DEFAULT FALSE,                              -- BB/TB sangat kurus/kurus

    -- Perkembangan motorik & kognitif (KPSP - Kuesioner Pra Skrining Perkembangan)
    perkembangan_status VARCHAR(20) CHECK (perkembangan_status IN ('sesuai','meragukan','penyimpangan') OR perkembangan_status IS NULL),
    perkembangan_catatan TEXT,

    -- Klinis (kalau visit_type=sakit)
    keluhan           TEXT,
    diagnosis         TEXT,
    tindakan          TEXT,
    terapi            TEXT,

    -- ASI / Susu
    asi_eksklusif     BOOLEAN,                                            -- masih ASI eksklusif (untuk usia <6 bln)
    pmt               TEXT,                                                 -- PMT (Pemberian Makanan Tambahan)

    tanggal_kembali   DATE,
    rujukan           BOOLEAN DEFAULT FALSE,
    rujukan_alasan    TEXT,

    notes             TEXT,

    served_by         BIGINT REFERENCES tbm_users(id),
    created_by        BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_child_visits_neonate ON tbr_child_visits(neonate_id);
CREATE INDEX IF NOT EXISTS idx_child_visits_date    ON tbr_child_visits(visit_date DESC);

DROP TRIGGER IF EXISTS trg_tbr_child_visits_updated ON tbr_child_visits;
CREATE TRIGGER trg_tbr_child_visits_updated BEFORE UPDATE ON tbr_child_visits
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 3. Update master imunisasi (lengkapi sesuai IDAI)
-- =====================================================================
-- HB-0 sudah dicakup di tbr_neonates.hb0_diberi (lahir)
-- Tambah PCV, Rotavirus, MMR/Campak Rubella

INSERT INTO tbm_immunization_types (code, name, target_group, max_dose, description, sort_order) VALUES
    ('IMU-PCV',      'PCV',           'bayi', 3, 'Pneumococcal Conjugate Vaccine — 3 dose (2/3/12 bulan)',                    6),
    ('IMU-ROTA',     'Rotavirus',     'bayi', 3, 'Vaksin rotavirus oral — 2-3 dose (2/3/4 bulan)',                              7),
    ('IMU-MR',       'MR (Campak-Rubella)', 'anak', 2, 'Measles-Rubella — 2 dose (9 dan 18 bulan, gantikan campak)',           8),
    ('IMU-JE',       'JE (Japanese Encephalitis)', 'anak', 1, 'JE — 1 dose (umur 9 bulan, endemic area)',                       9),
    ('IMU-DT',       'DT Booster',    'anak', 1, 'DT Booster di kelas 1 SD',                                                   10),
    ('IMU-TD',       'Td Booster',    'anak', 1, 'Td Booster di kelas 2 & 5 SD',                                               11)
ON CONFLICT (code) DO NOTHING;

-- Update max_dose untuk yang sudah ada
UPDATE tbm_immunization_types SET max_dose = 4 WHERE code = 'IMU-POLIO';  -- OPV/IPV 4 dose
UPDATE tbm_immunization_types SET max_dose = 4 WHERE code = 'IMU-HBDTP';  -- DPT-HB-HiB 4 dose

-- =====================================================================
-- 4. PERMISSIONS
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    ('child.view',         'Lihat Data Bayi/Anak',  'pelayanan_anak', 'Akses data anak'),
    ('child.create',       'Catat Anak',            'pelayanan_anak', 'Daftar anak baru / kunjungan'),
    ('child.update',       'Update Data Anak',      'pelayanan_anak', 'Edit data anak'),
    ('child.delete',       'Hapus Data Anak',       'pelayanan_anak', 'Soft delete'),
    ('immunization.view',  'Lihat Imunisasi',       'pelayanan_anak', 'Akses data imunisasi'),
    ('immunization.create','Berikan Imunisasi',     'pelayanan_anak', 'Catat pemberian imunisasi'),
    ('immunization.update','Update Imunisasi',      'pelayanan_anak', 'Edit record imunisasi'),
    ('immunization.delete','Hapus Imunisasi',       'pelayanan_anak', 'Soft delete imunisasi')
ON CONFLICT (name) DO NOTHING;

-- Grant
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('super_admin', 'admin')
  AND p.module = 'pelayanan_anak'
ON CONFLICT DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('dokter', 'perawat')
  AND p.module = 'pelayanan_anak' AND p.name NOT LIKE '%.delete'
ON CONFLICT DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran' AND p.name IN ('child.view', 'immunization.view')
ON CONFLICT DO NOTHING;

-- =====================================================================
-- DONE Phase 1.7 DDL
-- =====================================================================
