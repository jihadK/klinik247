-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.2 — Pendaftaran Kunjungan Pasien
-- DESC    : Tabel transaksi kunjungan dengan 4 kategori (A/I/K/R) +
--           auto-generate no_register pakai fn_next_doc_number('REG', kategori)
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. TABEL tbr_patient_visits
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_patient_visits (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,

    -- Identitas Kunjungan
    no_register       VARCHAR(30) NOT NULL,                                 -- I-01-2026-000001
    category          VARCHAR(1)  NOT NULL CHECK (category IN ('A','I','K','R')),
    -- A=Anak, I=Ibu (Hamil/Nifas/Reguler), K=KB, R=Reproduksi

    visit_type        VARCHAR(30),                                          -- baru/kontrol/rujukan/darurat
    visit_date        DATE        NOT NULL DEFAULT CURRENT_DATE,
    visit_time        TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Pasien
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,
    is_new_patient    BOOLEAN NOT NULL DEFAULT FALSE,                       -- TRUE = pasien baru daftar sekalian saat datang

    -- Pembiayaan
    payer_type_id     BIGINT REFERENCES tbm_payer_types(id),

    -- Antrian
    queue_number      INT,                                                  -- Nomor antrian per kategori per hari
    chief_complaint   TEXT,                                                 -- Keluhan utama
    notes             TEXT,                                                 -- Catatan pendaftaran

    -- Status
    status            VARCHAR(20) NOT NULL DEFAULT 'waiting'
                       CHECK (status IN ('waiting','in_service','done','cancelled','no_show')),
    cancel_reason     TEXT,

    -- Tracking
    created_by        BIGINT REFERENCES tbm_users(id),                      -- admin pendaftaran
    served_by         BIGINT REFERENCES tbm_users(id),                      -- bidan/dokter
    served_at         TIMESTAMPTZ,                                          -- start service
    completed_at      TIMESTAMPTZ,                                          -- selesai service

    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,

    CONSTRAINT uq_visits_site_register UNIQUE (site_id, no_register)
);

CREATE INDEX IF NOT EXISTS idx_visits_site_date   ON tbr_patient_visits(site_id, visit_date DESC) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_visits_patient     ON tbr_patient_visits(patient_id);
CREATE INDEX IF NOT EXISTS idx_visits_category    ON tbr_patient_visits(category);
CREATE INDEX IF NOT EXISTS idx_visits_status      ON tbr_patient_visits(status) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_visits_no_register ON tbr_patient_visits(no_register);

-- Trigger auto-update updated_date
DROP TRIGGER IF EXISTS trg_tbr_visits_updated ON tbr_patient_visits;
CREATE TRIGGER trg_tbr_visits_updated
    BEFORE UPDATE ON tbr_patient_visits
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 2. PERMISSIONS untuk visits
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    ('visits.view',     'Lihat Kunjungan',        'transaksi', 'Akses daftar kunjungan pasien'),
    ('visits.create',   'Daftar Kunjungan',       'transaksi', 'Buat kunjungan baru'),
    ('visits.update',   'Update Kunjungan',       'transaksi', 'Edit data / status kunjungan'),
    ('visits.delete',   'Hapus/Cancel Kunjungan', 'transaksi', 'Batalkan kunjungan'),
    ('visits.serve',    'Layani Kunjungan',       'transaksi', 'Bidan/dokter mulai layani pasien')
ON CONFLICT (name) DO NOTHING;

-- Grant ke super_admin & admin (full akses)
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('super_admin', 'admin')
  AND p.name IN ('visits.view', 'visits.create', 'visits.update', 'visits.delete', 'visits.serve')
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- Pendaftaran: bisa create/update/cancel tapi tidak serve
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran'
  AND p.name IN ('visits.view', 'visits.create', 'visits.update', 'visits.delete')
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- Dokter/Bidan: bisa serve + view (tidak create/cancel)
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('dokter', 'perawat')
  AND p.name IN ('visits.view', 'visits.serve', 'visits.update')
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- Kasir: view saja (untuk lookup saat bayar)
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'kasir'
  AND p.name = 'visits.view'
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- =====================================================================
-- DONE Phase 1.2 DDL
-- Note: tbs_document_sequences untuk REG (A/I/K/R) sudah di-seed di Phase 1.0
-- =====================================================================

-- Verifikasi:
-- SELECT category, name FROM (
--   VALUES ('A','Anak'),('I','Ibu'),('K','KB'),('R','Reproduksi')
-- ) v(category, name);
--
-- Test generate:
-- SELECT fn_next_doc_number(1, 'REG', 'I') AS first_ibu;   -- I-01-2026-000001
-- SELECT fn_next_doc_number(1, 'REG', 'A') AS first_anak;  -- A-01-2026-000001
