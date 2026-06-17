-- ===================================================================
-- PHASE 1.7 — Modul Anak (Imunisasi + Tumbuh Kembang)
-- ===================================================================
-- 2 tabel: tbr_immunization_records + tbr_child_visits
-- + Seed initial data jenis imunisasi standar Kemenkes
-- IDEMPOTENT — aman dijalankan berulang
-- ===================================================================

BEGIN;

-- ============================================================
-- 1. tbr_immunization_records — Catatan Imunisasi Anak
-- ============================================================
CREATE TABLE IF NOT EXISTS tbr_immunization_records (
    id                   BIGSERIAL PRIMARY KEY,
    site_id              BIGINT       NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    neonate_id           BIGINT       NULL     REFERENCES tbr_neonates(id) ON DELETE SET NULL,
    patient_id           BIGINT       NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,
    immunization_type_id BIGINT       NOT NULL REFERENCES tbm_immunization_types(id) ON DELETE RESTRICT,
    dose_number          SMALLINT     NULL,
    given_date           DATE         NOT NULL,
    given_at             TIMESTAMP    NULL,
    given_by             BIGINT       NULL     REFERENCES tbm_users(id) ON DELETE SET NULL,
    no_batch             VARCHAR(50)  NULL,
    tempat               VARCHAR(100) NULL,
    catatan              TEXT         NULL,
    side_effects         TEXT         NULL,
    next_due_date        DATE         NULL,
    created_by           BIGINT       NULL     REFERENCES tbm_users(id) ON DELETE SET NULL,
    created_date         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_date         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_date         TIMESTAMP    NULL
);

CREATE INDEX IF NOT EXISTS idx_imm_neonate_id     ON tbr_immunization_records(neonate_id);
CREATE INDEX IF NOT EXISTS idx_imm_patient_id     ON tbr_immunization_records(patient_id);
CREATE INDEX IF NOT EXISTS idx_imm_type_id        ON tbr_immunization_records(immunization_type_id);
CREATE INDEX IF NOT EXISTS idx_imm_given_date     ON tbr_immunization_records(given_date);
CREATE INDEX IF NOT EXISTS idx_imm_site_id        ON tbr_immunization_records(site_id);
CREATE INDEX IF NOT EXISTS idx_imm_next_due_date  ON tbr_immunization_records(next_due_date) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_imm_deleted_date   ON tbr_immunization_records(deleted_date);

COMMENT ON TABLE tbr_immunization_records IS 'Catatan imunisasi anak — Phase 1.7';

-- ============================================================
-- 2. tbr_child_visits — Kunjungan Anak (Tumbuh Kembang)
-- ============================================================
CREATE TABLE IF NOT EXISTS tbr_child_visits (
    id                    BIGSERIAL PRIMARY KEY,
    site_id               BIGINT        NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    neonate_id            BIGINT        NULL     REFERENCES tbr_neonates(id) ON DELETE SET NULL,
    patient_id            BIGINT        NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,
    visit_date            DATE          NOT NULL,
    visit_time            TIMESTAMP     NULL,
    visit_type            VARCHAR(30)   NULL,    -- rutin / imunisasi / sakit / kontrol_balik / lainnya
    umur_hari             INT           NULL,
    umur_label            VARCHAR(50)   NULL,    -- mis "1 bulan 5 hari"
    -- Antropometri
    berat_badan_gram      INT           NULL,
    panjang_badan_cm      DECIMAL(5,1)  NULL,
    lingkar_kepala_cm     DECIMAL(5,1)  NULL,
    lingkar_lengan_cm     DECIMAL(5,1)  NULL,
    suhu_celcius          DECIMAL(4,1)  NULL,
    -- Status gizi & tumbuh
    status_gizi           VARCHAR(20)   NULL,    -- gizi_buruk / gizi_kurang / gizi_baik / gizi_lebih / obesitas
    stunting              BOOLEAN       NULL,
    wasting               BOOLEAN       NULL,
    -- Perkembangan (SDIDTK)
    perkembangan_status   VARCHAR(20)   NULL,    -- sesuai / meragukan / penyimpangan
    perkembangan_catatan  TEXT          NULL,
    -- Pemeriksaan
    keluhan               TEXT          NULL,
    diagnosis             TEXT          NULL,
    tindakan              TEXT          NULL,
    terapi                TEXT          NULL,
    -- Pemberian khusus
    asi_eksklusif         BOOLEAN       NULL,
    pmt                   VARCHAR(255)  NULL,    -- Pemberian Makanan Tambahan
    -- Tindak lanjut
    tanggal_kembali       DATE          NULL,
    rujukan               BOOLEAN       NOT NULL DEFAULT FALSE,
    rujukan_alasan        TEXT          NULL,
    notes                 TEXT          NULL,
    -- Audit
    served_by             BIGINT        NULL     REFERENCES tbm_users(id) ON DELETE SET NULL,
    created_by            BIGINT        NULL     REFERENCES tbm_users(id) ON DELETE SET NULL,
    created_date          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_date          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_date          TIMESTAMP     NULL
);

CREATE INDEX IF NOT EXISTS idx_cv_neonate_id      ON tbr_child_visits(neonate_id);
CREATE INDEX IF NOT EXISTS idx_cv_patient_id      ON tbr_child_visits(patient_id);
CREATE INDEX IF NOT EXISTS idx_cv_visit_date      ON tbr_child_visits(visit_date);
CREATE INDEX IF NOT EXISTS idx_cv_visit_type      ON tbr_child_visits(visit_type);
CREATE INDEX IF NOT EXISTS idx_cv_site_id         ON tbr_child_visits(site_id);
CREATE INDEX IF NOT EXISTS idx_cv_tanggal_kembali ON tbr_child_visits(tanggal_kembali) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_cv_deleted_date    ON tbr_child_visits(deleted_date);

COMMENT ON TABLE tbr_child_visits IS 'Kunjungan anak (rutin/imunisasi/sakit/tumbuh kembang) — Phase 1.7';

-- ============================================================
-- 3. Seed initial data — Jenis Imunisasi standar Kemenkes
--    Cuma INSERT kalau tabel masih kosong
-- ============================================================
DO $$
DECLARE
    cnt INT;
BEGIN
    SELECT COUNT(*) INTO cnt FROM tbm_immunization_types;
    IF cnt = 0 THEN
        INSERT INTO tbm_immunization_types (name, code, description, is_active) VALUES
        ('Hepatitis B (HB-0)',          'HB0',     'Vaksin Hepatitis B dosis 0 — diberikan < 24 jam setelah lahir',                TRUE),
        ('BCG',                         'BCG',     'Vaksin BCG — diberikan usia 1 bulan, untuk TBC',                                TRUE),
        ('Polio (OPV/IPV)',             'POLIO',   'Polio tetes/suntik — usia 1-4 bulan, dosis 1-4',                                TRUE),
        ('DPT-HB-Hib (Pentavalen)',     'DPT',     'DPT + HB + Hib — usia 2,3,4 bulan, dosis 1-3',                                  TRUE),
        ('Campak / MR',                 'MR',      'Campak Rubella — usia 9 bulan & 18 bulan',                                      TRUE),
        ('Rotavirus',                   'ROTA',    'Vaksin rotavirus — usia 2,3,4 bulan',                                           TRUE),
        ('PCV (Pneumokokus)',           'PCV',     'Pneumococcal Conjugate Vaccine — usia 2,3,12 bulan',                            TRUE),
        ('JE (Japanese Encephalitis)',  'JE',      'Vaksin JE — usia 9 bulan (di daerah endemik)',                                  TRUE),
        ('DPT Booster',                 'DPT_BST', 'Booster DPT — usia 18 bulan & 5-7 tahun',                                       TRUE),
        ('MR Booster',                  'MR_BST',  'Booster Campak/Rubella — usia 18 bulan & SD',                                   TRUE),
        ('HPV',                         'HPV',     'Vaksin HPV — anak perempuan kelas 5-6 SD',                                      TRUE),
        ('Influenza',                   'INFLU',   'Vaksin influenza — tahunan, mulai usia 6 bulan',                                TRUE);

        RAISE NOTICE '✅ Seed 12 jenis imunisasi standar Kemenkes';
    ELSE
        RAISE NOTICE 'ℹ Skip seed imunisasi — sudah ada % record', cnt;
    END IF;
END $$;

COMMIT;

-- ============================================================
-- VERIFIKASI HASIL
-- ============================================================
SELECT 'tbr_immunization_records' AS tabel, COUNT(*) AS rows FROM tbr_immunization_records
UNION ALL
SELECT 'tbr_child_visits',         COUNT(*) FROM tbr_child_visits
UNION ALL
SELECT 'tbm_immunization_types',   COUNT(*) FROM tbm_immunization_types;
