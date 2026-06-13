-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.3+ — Ganti Alat Workflow
-- DESC    : Tambah kolom previous_acceptor_id untuk traceability history
--           saat akseptor ganti alat kontrasepsi
-- =====================================================================

SET client_min_messages = WARNING;

-- Tambah kolom link ke acceptor sebelumnya (untuk track history ganti alat)
ALTER TABLE tbr_kb_acceptors
    ADD COLUMN IF NOT EXISTS previous_acceptor_id BIGINT REFERENCES tbr_kb_acceptors(id);

CREATE INDEX IF NOT EXISTS idx_kb_acceptors_previous ON tbr_kb_acceptors(previous_acceptor_id) WHERE previous_acceptor_id IS NOT NULL;

-- =====================================================================
-- Verifikasi: SELECT column_name FROM information_schema.columns
--             WHERE table_name='tbr_kb_acceptors' AND column_name='previous_acceptor_id';
-- =====================================================================
