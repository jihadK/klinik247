-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.0 — Foundation Master + Wilayah + Auto-Gen Format
-- DESC    :
--   1. Master lookup baru: payer_types, kontrasepsi, immunization, education, religion
--   2. Master wilayah (Kemendagri): provinces, regencies, districts, villages
--   3. Tambahan kolom di tbs_document_sequences untuk multi-format
--   4. Drop & recreate tbm_patients dengan schema lengkap (per workflow Bu Tin)
--   5. Recreate fn_next_doc_number untuk multi-format (per-tahun & per-hari)
-- NOTE    :
--   - Format register pakai kategori prefix: I-01-2026-000123
--   - Format harian: RX-01-20260610-0123
--   - Master kecil: semantic code (PAY-BPJS), master besar: auto-running
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. EXTEND tbs_document_sequences UNTUK MULTI-FORMAT
-- =====================================================================

-- Tambah kolom yang dibutuhkan untuk format flexible
ALTER TABLE tbs_document_sequences
    ADD COLUMN IF NOT EXISTS category VARCHAR(5),         -- A/I/K/R untuk register kunjungan
    ADD COLUMN IF NOT EXISTS format_pattern VARCHAR(100) NOT NULL
        DEFAULT '{SITE2}-{YYYY}-{NNNNNN}',
    ADD COLUMN IF NOT EXISTS last_reset_date DATE;        -- untuk reset harian

-- Update CHECK constraint reset_period (tambah 'daily')
ALTER TABLE tbs_document_sequences DROP CONSTRAINT IF EXISTS tbs_document_sequences_reset_period_check;
ALTER TABLE tbs_document_sequences
    ADD CONSTRAINT tbs_document_sequences_reset_period_check
    CHECK (reset_period IN ('never','daily','monthly','yearly'));

-- Update unique constraint untuk include category (1 site bisa punya beberapa doc_type per kategori)
ALTER TABLE tbs_document_sequences DROP CONSTRAINT IF EXISTS uq_sequences_site_type;
ALTER TABLE tbs_document_sequences
    ADD CONSTRAINT uq_sequences_site_type_cat UNIQUE (site_id, doc_type, category);

-- =====================================================================
-- 2. MASTER LOOKUP (Semantic Code — Global, no site_id)
-- =====================================================================

-- 2.1 PAYER TYPES (BPJS/UMUM/ASURANSI)
CREATE TABLE IF NOT EXISTS tbm_payer_types (
    id            BIGSERIAL PRIMARY KEY,
    code          VARCHAR(30) UNIQUE NOT NULL,    -- PAY-BPJS
    name          VARCHAR(100) NOT NULL,           -- BPJS Kesehatan
    description   VARCHAR(255),
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order    INT NOT NULL DEFAULT 0,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ
);

-- 2.2 KONTRASEPSI METHODS (alat KB)
CREATE TABLE IF NOT EXISTS tbm_kontrasepsi_methods (
    id            BIGSERIAL PRIMARY KEY,
    code          VARCHAR(30) UNIQUE NOT NULL,    -- KTR-KONDOM
    name          VARCHAR(100) NOT NULL,           -- Kondom
    method_type   VARCHAR(30),                     -- jangka_pendek/jangka_panjang/permanent
    description   VARCHAR(255),
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order    INT NOT NULL DEFAULT 0,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ
);

-- 2.3 IMMUNIZATION TYPES (jenis imunisasi)
CREATE TABLE IF NOT EXISTS tbm_immunization_types (
    id            BIGSERIAL PRIMARY KEY,
    code          VARCHAR(30) UNIQUE NOT NULL,    -- IMU-BCG
    name          VARCHAR(100) NOT NULL,           -- BCG
    target_group  VARCHAR(30),                     -- bayi/anak/ibu_hamil
    max_dose      INT NOT NULL DEFAULT 1,          -- berapa dose (I,II,III,IV)
    description   VARCHAR(255),
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    sort_order    INT NOT NULL DEFAULT 0,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ
);

-- 2.4 EDUCATION LEVELS (pendidikan)
CREATE TABLE IF NOT EXISTS tbm_education_levels (
    id            BIGSERIAL PRIMARY KEY,
    code          VARCHAR(30) UNIQUE NOT NULL,    -- EDU-SD
    name          VARCHAR(100) NOT NULL,           -- SD/Sederajat
    sort_order    INT NOT NULL DEFAULT 0,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- 2.5 RELIGIONS (agama)
CREATE TABLE IF NOT EXISTS tbm_religions (
    id            BIGSERIAL PRIMARY KEY,
    code          VARCHAR(30) UNIQUE NOT NULL,    -- AGM-ISLAM
    name          VARCHAR(100) NOT NULL,           -- Islam
    sort_order    INT NOT NULL DEFAULT 0,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- =====================================================================
-- 3. MASTER WILAYAH (Kemendagri Standard — 4 Level)
-- =====================================================================

-- 3.1 PROVINCES (provinsi — kode 2 digit)
CREATE TABLE IF NOT EXISTS tbm_provinces (
    code          VARCHAR(2) PRIMARY KEY,          -- 35
    name          VARCHAR(100) NOT NULL,            -- Jawa Timur
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- 3.2 REGENCIES (kabupaten/kota — kode 4 digit)
CREATE TABLE IF NOT EXISTS tbm_regencies (
    code          VARCHAR(4) PRIMARY KEY,           -- 3524
    province_code VARCHAR(2) NOT NULL REFERENCES tbm_provinces(code),
    name          VARCHAR(100) NOT NULL,            -- Kab. Lamongan
    type          VARCHAR(20) DEFAULT 'kabupaten' CHECK (type IN ('kabupaten','kota')),
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_regencies_province ON tbm_regencies(province_code);

-- 3.3 DISTRICTS (kecamatan — kode 7 digit)
CREATE TABLE IF NOT EXISTS tbm_districts (
    code          VARCHAR(7) PRIMARY KEY,           -- 3524141
    regency_code  VARCHAR(4) NOT NULL REFERENCES tbm_regencies(code),
    name          VARCHAR(100) NOT NULL,            -- Paciran
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_districts_regency ON tbm_districts(regency_code);

-- 3.4 VILLAGES (desa/kelurahan — kode 10 digit)
CREATE TABLE IF NOT EXISTS tbm_villages (
    code          VARCHAR(10) PRIMARY KEY,          -- 3524141009
    district_code VARCHAR(7) NOT NULL REFERENCES tbm_districts(code),
    name          VARCHAR(100) NOT NULL,            -- Sidokumpul
    type          VARCHAR(20) DEFAULT 'desa' CHECK (type IN ('desa','kelurahan')),
    postal_code   VARCHAR(10),
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_villages_district ON tbm_villages(district_code);

-- =====================================================================
-- 4. DROP & RECREATE tbm_patients DENGAN SCHEMA LENGKAP
-- =====================================================================

-- Backup struktur lama dulu (kalau ada data — meskipun belum)
DROP TABLE IF EXISTS tbm_patients CASCADE;

CREATE TABLE tbm_patients (
    id                  BIGSERIAL PRIMARY KEY,
    site_id             BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,

    -- Identitas Utama (auto-generate)
    no_rm               VARCHAR(30) NOT NULL,           -- 01-2026-000001 (SS-YYYY-NNNNNN)
    cm_lama             VARCHAR(50),                     -- Optional: RM dari klinik lain

    -- KTP & KK
    nik                 VARCHAR(16),                     -- NIK KTP (optional)
    no_kk               VARCHAR(20),                     -- No. Kartu Keluarga
    nama_kk             VARCHAR(150),                    -- Nama Kepala Keluarga
    no_bpjs             VARCHAR(20),                     -- No. BPJS (jika ada)

    -- Identitas Pribadi
    name                VARCHAR(150) NOT NULL,
    birth_place         VARCHAR(100),
    birth_date          DATE NOT NULL,
    gender              VARCHAR(1) NOT NULL CHECK (gender IN ('L','P')),

    -- Sosial-Demografi
    payer_type_id       BIGINT REFERENCES tbm_payer_types(id),
    education_id        BIGINT REFERENCES tbm_education_levels(id),
    religion_id         BIGINT REFERENCES tbm_religions(id),
    occupation          VARCHAR(100),
    marital_status      VARCHAR(20) CHECK (marital_status IN ('belum_menikah','menikah','cerai_hidup','cerai_mati')),
    blood_type          VARCHAR(5) CHECK (blood_type IN ('A+','A-','B+','B-','AB+','AB-','O+','O-')),

    -- Alamat (4 level wilayah Kemendagri)
    province_code       VARCHAR(2)  REFERENCES tbm_provinces(code),
    regency_code        VARCHAR(4)  REFERENCES tbm_regencies(code),
    district_code       VARCHAR(7)  REFERENCES tbm_districts(code),
    village_code        VARCHAR(10) REFERENCES tbm_villages(code),
    address             TEXT,                            -- Jalan, RT/RW, dll detail
    rt_rw               VARCHAR(20),
    postal_code         VARCHAR(10),
    wilayah_type        VARCHAR(20) CHECK (wilayah_type IN ('dalam_wilayah','luar_wilayah')),

    -- Kontak
    phone               VARCHAR(20),
    email               VARCHAR(100),

    -- Riwayat Medis
    allergies           TEXT,                            -- alergi obat/makanan
    chronic_diseases    TEXT,                            -- penyakit kronis
    medical_history     TEXT,                            -- riwayat penyakit umum

    -- Emergency
    emergency_contact   VARCHAR(150),
    emergency_phone     VARCHAR(20),
    emergency_relation  VARCHAR(50),

    -- Meta
    photo_url           VARCHAR(255),
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at       TIMESTAMPTZ,
    notes               TEXT,
    created_by          BIGINT REFERENCES tbm_users(id),
    created_date        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date        TIMESTAMPTZ,
    deleted_date        TIMESTAMPTZ,

    CONSTRAINT uq_patients_site_norm UNIQUE (site_id, no_rm)
);

CREATE INDEX idx_patients_site ON tbm_patients(site_id) WHERE deleted_date IS NULL;
CREATE INDEX idx_patients_name_trgm ON tbm_patients USING gin (name gin_trgm_ops);
CREATE INDEX idx_patients_nik ON tbm_patients(nik) WHERE nik IS NOT NULL AND deleted_date IS NULL;
CREATE INDEX idx_patients_bpjs ON tbm_patients(no_bpjs) WHERE no_bpjs IS NOT NULL;
CREATE INDEX idx_patients_no_kk ON tbm_patients(no_kk) WHERE no_kk IS NOT NULL;
CREATE INDEX idx_patients_village ON tbm_patients(village_code);
CREATE INDEX idx_patients_payer ON tbm_patients(payer_type_id);

-- Auto-update trigger (sudah ada generic trigger di Phase 0)
DROP TRIGGER IF EXISTS trg_tbm_patients_updated ON tbm_patients;
CREATE TRIGGER trg_tbm_patients_updated
    BEFORE UPDATE ON tbm_patients
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 5. RECREATE fn_next_doc_number — MULTI-FORMAT GENERATOR
-- =====================================================================

DROP FUNCTION IF EXISTS fn_next_doc_number(BIGINT, VARCHAR);
DROP FUNCTION IF EXISTS fn_next_doc_number(BIGINT, VARCHAR, VARCHAR);

CREATE OR REPLACE FUNCTION fn_next_doc_number(
    p_site_id   BIGINT,
    p_doc_type  VARCHAR,
    p_category  VARCHAR DEFAULT NULL    -- A/I/K/R untuk register kunjungan, NULL untuk yang lain
)
RETURNS VARCHAR AS $$
DECLARE
    v_seq           tbs_document_sequences%ROWTYPE;
    v_should_reset  BOOLEAN := FALSE;
    v_result        VARCHAR;
    v_site_code     VARCHAR(2);
    v_today         DATE := CURRENT_DATE;
BEGIN
    -- 1. Lock & ambil sequence row
    SELECT * INTO v_seq FROM tbs_document_sequences
    WHERE site_id = p_site_id
      AND doc_type = p_doc_type
      AND (category IS NOT DISTINCT FROM p_category)
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Sequence not found: site=% doc_type=% category=%',
            p_site_id, p_doc_type, p_category;
    END IF;

    -- 2. Cek perlu reset?
    IF v_seq.reset_period = 'yearly' AND
       (v_seq.last_reset_at IS NULL OR EXTRACT(YEAR FROM v_seq.last_reset_at) <> EXTRACT(YEAR FROM v_today))
    THEN
        v_should_reset := TRUE;
    ELSIF v_seq.reset_period = 'monthly' AND
       (v_seq.last_reset_at IS NULL OR DATE_TRUNC('month', v_seq.last_reset_at) <> DATE_TRUNC('month', v_today))
    THEN
        v_should_reset := TRUE;
    ELSIF v_seq.reset_period = 'daily' AND
       (v_seq.last_reset_date IS NULL OR v_seq.last_reset_date <> v_today)
    THEN
        v_should_reset := TRUE;
    END IF;

    -- 3. Update counter
    IF v_should_reset THEN
        UPDATE tbs_document_sequences
           SET current_number = 1,
               last_reset_at = v_today,
               last_reset_date = v_today,
               updated_date = NOW()
         WHERE id = v_seq.id RETURNING * INTO v_seq;
    ELSE
        UPDATE tbs_document_sequences
           SET current_number = current_number + 1,
               updated_date = NOW()
         WHERE id = v_seq.id RETURNING * INTO v_seq;
    END IF;

    -- 4. Format hasil sesuai pattern
    v_site_code := LPAD(p_site_id::TEXT, 2, '0');

    v_result := v_seq.format_pattern;
    v_result := REPLACE(v_result, '{KAT}',      COALESCE(p_category, ''));
    v_result := REPLACE(v_result, '{SITE2}',    v_site_code);
    v_result := REPLACE(v_result, '{YYYY}',     TO_CHAR(v_today, 'YYYY'));
    v_result := REPLACE(v_result, '{YYYYMMDD}', TO_CHAR(v_today, 'YYYYMMDD'));
    v_result := REPLACE(v_result, '{NNNN}',     LPAD(v_seq.current_number::TEXT, 4, '0'));
    v_result := REPLACE(v_result, '{NNNNNN}',   LPAD(v_seq.current_number::TEXT, 6, '0'));

    RETURN v_result;
END;
$$ LANGUAGE plpgsql;

-- =====================================================================
-- 6. CLEAR OLD SEQUENCES (kalau ada dari Phase 0 seed lama)
-- =====================================================================
DELETE FROM tbs_document_sequences;

-- =====================================================================
-- DONE Phase 1.0 — Foundation Schema
-- Eksekusi: 04_SEED_PHASE1_FOUNDATION.sql untuk isi data
-- =====================================================================
