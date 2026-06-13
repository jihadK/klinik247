-- =====================================================================
-- PATCH: Siklus Rujukan Utuh (PMB → RS → Balasan → Follow-up)
-- =====================================================================

SET client_min_messages = WARNING;

ALTER TABLE tbr_deliveries
    -- ===== Pengiriman =====
    ADD COLUMN IF NOT EXISTS rujuk_dikirim_at        TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS rujuk_transport         VARCHAR(50),       -- ambulans/mobil_pribadi/motor/lain
    ADD COLUMN IF NOT EXISTS rujuk_pendamping        VARCHAR(150),      -- suami/keluarga/bidan
    ADD COLUMN IF NOT EXISTS rujuk_bawa              TEXT,              -- yang dibawa (RM, sample, obat)
    ADD COLUMN IF NOT EXISTS rujuk_kontak_rs         VARCHAR(50),       -- nomor kontak RS

    -- ===== Penerimaan di RS =====
    ADD COLUMN IF NOT EXISTS rujuk_diterima_at       TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS rujuk_diterima_by       VARCHAR(150),

    -- ===== Surat Balik (Counter-referral) =====
    ADD COLUMN IF NOT EXISTS rujuk_balik_no              VARCHAR(50),
    ADD COLUMN IF NOT EXISTS rujuk_balik_diterima_at     TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS rujuk_balik_diagnosis       TEXT,
    ADD COLUMN IF NOT EXISTS rujuk_balik_tindakan        TEXT,
    ADD COLUMN IF NOT EXISTS rujuk_balik_outcome_ibu     TEXT,
    ADD COLUMN IF NOT EXISTS rujuk_balik_outcome_bayi    TEXT,
    ADD COLUMN IF NOT EXISTS rujuk_balik_rekomendasi     TEXT,
    ADD COLUMN IF NOT EXISTS rujuk_balik_dokter_rs       VARCHAR(150),  -- dokter RS yang sign surat balik

    -- ===== Status Siklus =====
    ADD COLUMN IF NOT EXISTS rujuk_siklus_status      VARCHAR(30) DEFAULT 'belum_kirim'
        CHECK (rujuk_siklus_status IN ('belum_kirim','dikirim','diterima_rs','ada_balasan','selesai','batal') OR rujuk_siklus_status IS NULL);

CREATE INDEX IF NOT EXISTS idx_deliveries_rujuk_status ON tbr_deliveries(rujuk_siklus_status)
    WHERE rujuk_siklus_status IS NOT NULL AND rujuk_siklus_status != 'belum_kirim';
