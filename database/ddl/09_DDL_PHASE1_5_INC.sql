-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.5 — Modul Persalinan (INC — Intra Natal Care)
-- DESC    : Sesuai Kartu Asuhan Persalinan Bu Tin (XLSX detail Sheet 2)
--           - Penapisan 18 item ibu bersalin
--           - Pengkajian SOAP timeline
--           - 4 Kala persalinan (I-IV)
--           - Terapi pasca salin Ibu + Bayi
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. tbr_deliveries — Header Persalinan (1 per kehamilan)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_deliveries (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    pregnancy_id      BIGINT NOT NULL REFERENCES tbr_pregnancies(id) ON DELETE RESTRICT,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,
    patient_visit_id  BIGINT REFERENCES tbr_patient_visits(id),

    no_persalinan     VARCHAR(30) NOT NULL,                              -- PS-01-2026-000001

    visit_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    masuk_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),                 -- Ibu masuk klinik

    -- ===== A. PENAPISAN 18 ITEM (Ya/Tidak) =====
    p_riwayat_sc                BOOLEAN NOT NULL DEFAULT FALSE,
    p_pendarahan_pervaginam     BOOLEAN NOT NULL DEFAULT FALSE,
    p_kehamilan_kurang_bulan    BOOLEAN NOT NULL DEFAULT FALSE,
    p_ketuban_mekonial          BOOLEAN NOT NULL DEFAULT FALSE,
    p_ketuban_lama              BOOLEAN NOT NULL DEFAULT FALSE,
    p_ketuban_kurang_bulan      BOOLEAN NOT NULL DEFAULT FALSE,
    p_ikterus                   BOOLEAN NOT NULL DEFAULT FALSE,
    p_anemia_berat              BOOLEAN NOT NULL DEFAULT FALSE,
    p_pre_eklampsi_berat        BOOLEAN NOT NULL DEFAULT FALSE,
    p_tfu_40                    BOOLEAN NOT NULL DEFAULT FALSE,
    p_demam                     BOOLEAN NOT NULL DEFAULT FALSE,
    p_gawat_janin               BOOLEAN NOT NULL DEFAULT FALSE,
    p_presentasi_bukan_kepala   BOOLEAN NOT NULL DEFAULT FALSE,
    p_tali_pusat_menumbung      BOOLEAN NOT NULL DEFAULT FALSE,
    p_gi_fase_aktif             BOOLEAN NOT NULL DEFAULT FALSE,
    p_letak_majemuk             BOOLEAN NOT NULL DEFAULT FALSE,
    p_gemelli                   BOOLEAN NOT NULL DEFAULT FALSE,
    p_syok                      BOOLEAN NOT NULL DEFAULT FALSE,
    penapisan_skor              INT NOT NULL DEFAULT 0,                  -- jumlah YA (auto by app)
    penapisan_keputusan         VARCHAR(20) CHECK (penapisan_keputusan IN ('lanjut','rujuk','observasi') OR penapisan_keputusan IS NULL),

    -- ===== Identitas saat masuk PMB =====
    masuk_ttv_td       VARCHAR(20),
    masuk_ttv_nadi     INT,
    masuk_ttv_suhu     NUMERIC(3,1),
    masuk_ttv_rr       INT,
    masuk_djj          INT,
    masuk_his_per_10   INT,                                    -- frekuensi kontraksi /10 mnt
    masuk_vt_pembukaan NUMERIC(3,1),                           -- pembukaan cm 0-10
    masuk_ketuban      VARCHAR(30) CHECK (masuk_ketuban IN ('utuh','jernih','mekonial','keruh') OR masuk_ketuban IS NULL),
    masuk_keluhan      TEXT,

    -- ===== Kala I (Fase Aktif) =====
    kala1_mulai_at     TIMESTAMPTZ,
    kala1_selesai_at   TIMESTAMPTZ,
    kala1_keterangan   TEXT,

    -- ===== Kala II (Bayi Lahir) =====
    kala2_mulai_at         TIMESTAMPTZ,
    bayi_lahir_at          TIMESTAMPTZ,
    bayi_jenis_kelamin     VARCHAR(1) CHECK (bayi_jenis_kelamin IN ('L','P') OR bayi_jenis_kelamin IS NULL),
    bayi_bb_gram           INT,
    bayi_pb_cm             NUMERIC(4,1),
    bayi_lahir_spontan     BOOLEAN DEFAULT TRUE,
    bayi_lgs_menangis      BOOLEAN,
    bayi_apgar_1           INT CHECK (bayi_apgar_1 BETWEEN 0 AND 10 OR bayi_apgar_1 IS NULL),
    bayi_apgar_5           INT CHECK (bayi_apgar_5 BETWEEN 0 AND 10 OR bayi_apgar_5 IS NULL),

    -- ===== Kala III (Plasenta) =====
    kala3_mulai_at          TIMESTAMPTZ,
    plasenta_lahir_at       TIMESTAMPTZ,
    plasenta_lahir_spontan  BOOLEAN DEFAULT TRUE,
    mak_iii_dilakukan       BOOLEAN DEFAULT TRUE,
    amniotomi               BOOLEAN DEFAULT FALSE,
    tfu_sepusat             BOOLEAN,
    uc_kuat                 BOOLEAN,
    eksplorasi_dilakukan    BOOLEAN,
    sisa_plasenta           BOOLEAN,
    selaput_lengkap         BOOLEAN DEFAULT TRUE,

    -- ===== Kala IV (Observasi 2 jam) =====
    kala4_mulai_at          TIMESTAMPTZ,
    kala4_selesai_at        TIMESTAMPTZ,
    perineum_laserasi       VARCHAR(20) CHECK (perineum_laserasi IN ('none','derajat_1','derajat_2','derajat_3','derajat_4') OR perineum_laserasi IS NULL),
    heckting_dilakukan      BOOLEAN DEFAULT FALSE,
    heckting_lidocain       BOOLEAN DEFAULT FALSE,
    perdarahan_ml           INT,
    kala4_keluhan           TEXT,

    -- ===== Outcome =====
    status              VARCHAR(20) NOT NULL DEFAULT 'masuk'
                         CHECK (status IN ('masuk','inpartu','kala_ii','kala_iii','kala_iv','selesai','rujuk')),
    ibu_kondisi         VARCHAR(30) CHECK (ibu_kondisi IN ('sehat','sakit','rujuk','meninggal') OR ibu_kondisi IS NULL),
    bayi_kondisi        VARCHAR(30) CHECK (bayi_kondisi IN ('hidup_sehat','hidup_sakit','lahir_mati','meninggal') OR bayi_kondisi IS NULL),
    rujukan_ke          VARCHAR(150),
    rujukan_alasan      TEXT,

    -- ===== Terapi Ibu =====
    terapi_amoxicillin       BOOLEAN DEFAULT FALSE,
    terapi_asam_mef          BOOLEAN DEFAULT FALSE,
    terapi_fe                BOOLEAN DEFAULT FALSE,
    terapi_metergin          BOOLEAN DEFAULT FALSE,
    terapi_vita_dose1_at     TIMESTAMPTZ,
    terapi_vita_dose2_at     TIMESTAMPTZ,
    terapi_ibu_dosis_notes   TEXT,

    -- ===== Terapi Bayi =====
    bayi_injeksi_neo_k      BOOLEAN DEFAULT FALSE,
    bayi_neo_k_at           TIMESTAMPTZ,
    bayi_salep_mata         BOOLEAN DEFAULT FALSE,
    bayi_imunisasi_hb0      BOOLEAN DEFAULT FALSE,
    bayi_hb0_at             TIMESTAMPTZ,
    bayi_hb0_no_batch       VARCHAR(50),

    notes               TEXT,

    -- ===== Meta =====
    created_by   BIGINT REFERENCES tbm_users(id),
    served_by    BIGINT REFERENCES tbm_users(id),
    created_date TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date TIMESTAMPTZ,
    deleted_date TIMESTAMPTZ,

    CONSTRAINT uq_deliveries_site_no UNIQUE (site_id, no_persalinan)
);

CREATE INDEX IF NOT EXISTS idx_deliveries_site_status ON tbr_deliveries(site_id, status) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_deliveries_pregnancy ON tbr_deliveries(pregnancy_id);
CREATE INDEX IF NOT EXISTS idx_deliveries_patient ON tbr_deliveries(patient_id);
CREATE INDEX IF NOT EXISTS idx_deliveries_date ON tbr_deliveries(visit_date DESC) WHERE deleted_date IS NULL;

DROP TRIGGER IF EXISTS trg_tbr_deliveries_updated ON tbr_deliveries;
CREATE TRIGGER trg_tbr_deliveries_updated
    BEFORE UPDATE ON tbr_deliveries
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 2. tbr_delivery_soap — Timeline pengkajian SOAP (multi-row per delivery)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_delivery_soap (
    id              BIGSERIAL PRIMARY KEY,
    site_id         BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    delivery_id     BIGINT NOT NULL REFERENCES tbr_deliveries(id) ON DELETE CASCADE,

    observed_at     TIMESTAMPTZ NOT NULL,                  -- Tgl/Pkl observasi
    kala            VARCHAR(10) CHECK (kala IN ('masuk','kala_i','kala_ii','kala_iii','kala_iv') OR kala IS NULL),

    -- S — Subjective
    subjective      TEXT,

    -- O — Objective (Vital Sign + Pemeriksaan Persalinan)
    ttv_td          VARCHAR(20),
    ttv_nadi        INT,
    ttv_suhu        NUMERIC(3,1),
    ttv_rr          INT,
    djj             INT,
    his_per_10      INT,                                    -- jumlah kontraksi /10 mnt
    his_durasi      VARCHAR(20),                            -- "30-40 detik"
    vt_pembukaan    NUMERIC(3,1),
    vt_penurunan    VARCHAR(20),                            -- Hodge II, 5/5, dst
    ketuban         VARCHAR(30) CHECK (ketuban IN ('utuh','jernih','mekonial','keruh') OR ketuban IS NULL),
    hb_gr_dl        NUMERIC(4,1),
    alb             VARCHAR(20),

    -- A — Assessment
    assessment      TEXT,

    -- P — Plan
    plan            TEXT,

    notes           TEXT,

    created_by      BIGINT REFERENCES tbm_users(id),
    created_date    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_soap_delivery_time ON tbr_delivery_soap(delivery_id, observed_at);

-- =====================================================================
-- 3. PERMISSIONS
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    ('inc.view',   'Lihat Persalinan',          'pelayanan_inc', 'Akses data persalinan'),
    ('inc.create', 'Mulai Persalinan',          'pelayanan_inc', 'Catat persalinan baru (penapisan + masuk)'),
    ('inc.update', 'Update Persalinan',         'pelayanan_inc', 'Edit data persalinan / 4 Kala'),
    ('inc.delete', 'Hapus Data Persalinan',     'pelayanan_inc', 'Soft delete persalinan'),
    ('inc.soap',   'Catat SOAP Timeline',       'pelayanan_inc', 'Tambah/edit observasi SOAP'),
    ('inc.print',  'Cetak Kartu/Partograf',     'pelayanan_inc', 'Cetak dokumen persalinan')
ON CONFLICT (name) DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('super_admin', 'admin')
  AND p.name IN ('inc.view','inc.create','inc.update','inc.delete','inc.soap','inc.print')
ON CONFLICT DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('dokter', 'perawat')
  AND p.name IN ('inc.view','inc.create','inc.update','inc.soap','inc.print')
ON CONFLICT DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran'
  AND p.name IN ('inc.view','inc.print')
ON CONFLICT DO NOTHING;

-- =====================================================================
-- DONE Phase 1.5 DDL
-- Sequence PS sudah di-seed di Phase 1.0 (PS-{SITE2}-{YYYY}-{NNNNNN})
-- =====================================================================
