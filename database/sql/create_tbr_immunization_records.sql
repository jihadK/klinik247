-- ============================================================
-- Tabel: tbr_immunization_records
-- Modul: Phase 1.7 — Imunisasi Anak
-- Idempotent: aman dijalankan berulang (CREATE TABLE IF NOT EXISTS)
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

-- Indexes untuk performa query yang umum dipakai
CREATE INDEX IF NOT EXISTS idx_imm_neonate_id     ON tbr_immunization_records(neonate_id);
CREATE INDEX IF NOT EXISTS idx_imm_patient_id     ON tbr_immunization_records(patient_id);
CREATE INDEX IF NOT EXISTS idx_imm_type_id        ON tbr_immunization_records(immunization_type_id);
CREATE INDEX IF NOT EXISTS idx_imm_given_date     ON tbr_immunization_records(given_date);
CREATE INDEX IF NOT EXISTS idx_imm_next_due_date  ON tbr_immunization_records(next_due_date) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_imm_site_id        ON tbr_immunization_records(site_id);
CREATE INDEX IF NOT EXISTS idx_imm_deleted_date   ON tbr_immunization_records(deleted_date);

-- Trigger untuk auto-update updated_date (kalau function-nya ada di DB)
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_proc WHERE proname = 'set_updated_date') THEN
        DROP TRIGGER IF EXISTS trg_imm_updated_date ON tbr_immunization_records;
        CREATE TRIGGER trg_imm_updated_date
            BEFORE UPDATE ON tbr_immunization_records
            FOR EACH ROW EXECUTE FUNCTION set_updated_date();
    END IF;
END $$;

COMMENT ON TABLE tbr_immunization_records IS 'Catatan imunisasi anak — Phase 1.7';
COMMENT ON COLUMN tbr_immunization_records.dose_number IS 'Dosis ke berapa (1,2,3,...)';
COMMENT ON COLUMN tbr_immunization_records.no_batch IS 'No batch vaksin (untuk traceability)';
COMMENT ON COLUMN tbr_immunization_records.next_due_date IS 'Jadwal dosis berikutnya';
