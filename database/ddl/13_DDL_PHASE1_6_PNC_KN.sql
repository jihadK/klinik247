-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.6 — PNC (Nifas) + KN (Neonatus)
-- DESC    : Sesuai Kartu Bu Tin: 4 KF + 3 KN + 10 nasehat
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. tbr_postnatal_visits — Kunjungan Nifas (KF1-KF4)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_postnatal_visits (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    delivery_id       BIGINT NOT NULL REFERENCES tbr_deliveries(id) ON DELETE CASCADE,
    pregnancy_id      BIGINT REFERENCES tbr_pregnancies(id),
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id),

    kf_number         INT NOT NULL CHECK (kf_number BETWEEN 1 AND 4),
    visit_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    visit_time        TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Vital Sign Ibu
    ttv_td            VARCHAR(20),
    ttv_nadi          INT,
    ttv_suhu          NUMERIC(3,1),
    ttv_rr            INT,

    -- Pemantauan Ibu
    kondisi_umum      VARCHAR(20) CHECK (kondisi_umum IN ('sehat','sakit','komplikasi') OR kondisi_umum IS NULL),

    lokhia            VARCHAR(20) CHECK (lokhia IN ('rubra','sanguinolenta','serosa','alba','kering') OR lokhia IS NULL),
    lokhia_jumlah     VARCHAR(20) CHECK (lokhia_jumlah IN ('sedikit','sedang','banyak') OR lokhia_jumlah IS NULL),
    lokhia_bau        VARCHAR(20) CHECK (lokhia_bau IN ('normal','busuk') OR lokhia_bau IS NULL),

    jalan_lahir       VARCHAR(30) CHECK (jalan_lahir IN ('sehat','luka_basah','luka_kering','infeksi') OR jalan_lahir IS NULL),
    tanda_infeksi     BOOLEAN DEFAULT FALSE,

    kontraksi         VARCHAR(20) CHECK (kontraksi IN ('kuat','lemah','atonia') OR kontraksi IS NULL),
    tfu_cm            NUMERIC(4,1),

    payudara          VARCHAR(20) CHECK (payudara IN ('sehat','bengkak','lecet','infeksi','abses') OR payudara IS NULL),
    asi               VARCHAR(20) CHECK (asi IN ('lancar','sedikit','tidak') OR asi IS NULL),
    vit_a_dose        INT CHECK (vit_a_dose BETWEEN 0 AND 2),

    eliminasi_bak     VARCHAR(20) CHECK (eliminasi_bak IN ('lancar','sulit','tidak') OR eliminasi_bak IS NULL),
    eliminasi_bab     VARCHAR(20) CHECK (eliminasi_bab IN ('lancar','sulit','tidak') OR eliminasi_bab IS NULL),

    keluhan           TEXT,
    komplikasi        TEXT,
    tindakan          TEXT,
    terapi            TEXT,

    -- Nasehat (JSON array of keys: gizi/minum/kebersihan/istirahat/aktivitas/luka_sc/menyusui/perawatan_bayi/bayi_stres/stimulasi/kb)
    nasehat_diberikan JSONB DEFAULT '[]'::jsonb,

    -- KB Konseling
    kb_dikonseling    BOOLEAN DEFAULT FALSE,
    kb_rencana        VARCHAR(50),

    -- Tindak Lanjut
    tanggal_kembali   DATE,
    rujukan           BOOLEAN DEFAULT FALSE,
    rujukan_alasan    TEXT,

    status            VARCHAR(20) DEFAULT 'sehat' CHECK (status IN ('sehat','sakit','dirujuk') OR status IS NULL),
    notes             TEXT,

    served_by         BIGINT REFERENCES tbm_users(id),
    created_by        BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,

    CONSTRAINT uq_pnc_delivery_kf UNIQUE (delivery_id, kf_number)
);

CREATE INDEX IF NOT EXISTS idx_pnc_delivery ON tbr_postnatal_visits(delivery_id);
CREATE INDEX IF NOT EXISTS idx_pnc_patient_date ON tbr_postnatal_visits(patient_id, visit_date);

DROP TRIGGER IF EXISTS trg_tbr_pnc_updated ON tbr_postnatal_visits;
CREATE TRIGGER trg_tbr_pnc_updated BEFORE UPDATE ON tbr_postnatal_visits
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 2. tbr_neonates — Header Bayi Baru Lahir (1 per bayi)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_neonates (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    delivery_id       BIGINT NOT NULL REFERENCES tbr_deliveries(id) ON DELETE RESTRICT,
    pregnancy_id      BIGINT REFERENCES tbr_pregnancies(id),
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id),     -- ID ibu (untuk filter & laporan)

    no_kartu_bayi     VARCHAR(30) NOT NULL,                              -- BB-01-2026-000001
    nama_bayi         VARCHAR(150) NOT NULL,
    jenis_kelamin     VARCHAR(1) CHECK (jenis_kelamin IN ('L','P')),

    tanggal_lahir     DATE,
    jam_lahir         TIME,
    bb_lahir_gram     INT,
    pb_lahir_cm       NUMERIC(4,1),
    cara_lahir        VARCHAR(30),
    apgar_1           INT,
    apgar_5           INT,

    -- Tindakan saat lahir (carry-over dari delivery)
    imd_dilakukan     BOOLEAN DEFAULT FALSE,
    vit_k1_diberi     BOOLEAN DEFAULT FALSE,
    vit_k1_at         TIMESTAMPTZ,
    salep_mata        BOOLEAN DEFAULT FALSE,
    hb0_diberi        BOOLEAN DEFAULT FALSE,
    hb0_at            TIMESTAMPTZ,
    hb0_batch         VARCHAR(50),

    -- Status
    status            VARCHAR(30) NOT NULL DEFAULT 'hidup_sehat'
                       CHECK (status IN ('hidup_sehat','hidup_sakit','dirujuk','meninggal')),
    keterangan_akhir  TEXT,

    notes             TEXT,

    created_by        BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,

    CONSTRAINT uq_neonates_site_no UNIQUE (site_id, no_kartu_bayi)
);

CREATE INDEX IF NOT EXISTS idx_neonates_delivery ON tbr_neonates(delivery_id);
CREATE INDEX IF NOT EXISTS idx_neonates_ibu      ON tbr_neonates(patient_id);
CREATE INDEX IF NOT EXISTS idx_neonates_status   ON tbr_neonates(status) WHERE deleted_date IS NULL;

DROP TRIGGER IF EXISTS trg_tbr_neonates_updated ON tbr_neonates;
CREATE TRIGGER trg_tbr_neonates_updated BEFORE UPDATE ON tbr_neonates
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 3. tbr_neonatal_visits — Kunjungan Neonatus (KN1-KN3)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_neonatal_visits (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    neonate_id        BIGINT NOT NULL REFERENCES tbr_neonates(id) ON DELETE CASCADE,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id),       -- ID ibu

    kn_number         INT NOT NULL CHECK (kn_number BETWEEN 1 AND 3),
    visit_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    visit_time        TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Pengukuran
    berat_badan_gram  INT,
    panjang_badan_cm  NUMERIC(4,1),
    lingkar_kepala_cm NUMERIC(4,1),
    suhu_celcius      NUMERIC(3,1),

    -- Pemeriksaan
    tali_pusat        VARCHAR(20) CHECK (tali_pusat IN ('basah','kering','lepas','infeksi') OR tali_pusat IS NULL),
    menyusu           VARCHAR(20) CHECK (menyusu IN ('lancar','kurang','tidak') OR menyusu IS NULL),
    ikterus_level     INT CHECK (ikterus_level BETWEEN 0 AND 4),         -- Kramer 0-IV

    -- Tanda bahaya (JSON list: kejang/sesak/malas_minum/dingin/panas/mata_merah/dll)
    tanda_bahaya      JSONB DEFAULT '[]'::jsonb,

    masalah_lain      TEXT,
    tindakan          TEXT,
    terapi            TEXT,

    dirujuk           BOOLEAN DEFAULT FALSE,
    rujukan_alasan    TEXT,

    tanggal_kembali   DATE,
    notes             TEXT,

    served_by         BIGINT REFERENCES tbm_users(id),
    created_by        BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,

    CONSTRAINT uq_kn_neonate_kn UNIQUE (neonate_id, kn_number)
);

CREATE INDEX IF NOT EXISTS idx_kn_neonate     ON tbr_neonatal_visits(neonate_id);
CREATE INDEX IF NOT EXISTS idx_kn_patient_date ON tbr_neonatal_visits(patient_id, visit_date);

DROP TRIGGER IF EXISTS trg_tbr_kn_updated ON tbr_neonatal_visits;
CREATE TRIGGER trg_tbr_kn_updated BEFORE UPDATE ON tbr_neonatal_visits
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 4. PERMISSIONS
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    -- PNC
    ('pnc.view',   'Lihat Data Nifas',     'pelayanan_pnc', 'Akses data nifas (KF)'),
    ('pnc.create', 'Catat Kunjungan KF',   'pelayanan_pnc', 'Tambah kunjungan nifas'),
    ('pnc.update', 'Update Data Nifas',    'pelayanan_pnc', 'Edit data nifas'),
    ('pnc.delete', 'Hapus Data Nifas',     'pelayanan_pnc', 'Soft delete nifas'),
    -- KN
    ('kn.view',    'Lihat Data Neonatus',  'pelayanan_kn',  'Akses data bayi'),
    ('kn.create',  'Catat Bayi/KN',        'pelayanan_kn',  'Tambah bayi & kunjungan KN'),
    ('kn.update',  'Update Data Bayi',     'pelayanan_kn',  'Edit data bayi & KN'),
    ('kn.delete',  'Hapus Data Bayi',      'pelayanan_kn',  'Soft delete bayi/KN')
ON CONFLICT (name) DO NOTHING;

-- Grant ke super_admin & admin
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('super_admin', 'admin')
  AND p.module IN ('pelayanan_pnc', 'pelayanan_kn')
ON CONFLICT DO NOTHING;

-- Dokter & Bidan: full kecuali delete
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('dokter', 'perawat')
  AND p.module IN ('pelayanan_pnc', 'pelayanan_kn')
  AND p.name NOT LIKE '%.delete'
ON CONFLICT DO NOTHING;

-- Pendaftaran: view only
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran'
  AND p.name IN ('pnc.view', 'kn.view')
ON CONFLICT DO NOTHING;

-- =====================================================================
-- DONE Phase 1.6 DDL
-- Sequence NF (nifas) & BB (bayi) sudah di-seed di Phase 1.0
-- =====================================================================
