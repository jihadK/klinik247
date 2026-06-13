-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.3 — Modul KB (Akseptor + Kunjungan Ulang + Informed Consent)
-- DESC    : Field sesuai Kartu KB Pondok Bersalin Bu Tin (3 section)
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. tbr_kb_acceptors — Akseptor KB (1 record per patient per periode aktif)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_kb_acceptors (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,
    patient_visit_id  BIGINT REFERENCES tbr_patient_visits(id),     -- visit awal yang trigger pendaftaran

    no_kartu_kb       VARCHAR(30) NOT NULL,                          -- KB-01-2026-000001
    kontrasepsi_id    BIGINT NOT NULL REFERENCES tbm_kontrasepsi_methods(id),

    -- ===== A. Identitas Akseptor (data tambahan, lainnya dari tbm_patients) =====
    akseptor_kawin_ke INT,

    -- ===== B. Identitas Suami =====
    suami_name        VARCHAR(150),
    suami_age         INT,
    suami_education_id BIGINT REFERENCES tbm_education_levels(id),
    suami_kawin_ke    INT,
    suami_occupation  VARCHAR(100),

    -- ===== C. Status Peserta KB Baru (8 pertanyaan) =====
    jumlah_anak_hidup            INT,
    keinginan_punya_anak_lagi    VARCHAR(20) CHECK (keinginan_punya_anak_lagi IN ('ya','tidak','tidak_tahu')),
    kapan_ingin_anak_lagi        VARCHAR(100),
    status_kehamilan_saat_ini    VARCHAR(20) CHECK (status_kehamilan_saat_ini IN ('hamil','tidak_hamil','tidak_tahu')),
    riwayat_komplikasi_kehamilan TEXT,
    sikap_pasangan_terhadap_kb   VARCHAR(20) CHECK (sikap_pasangan_terhadap_kb IN ('setuju','tidak_setuju','netral')),
    edukasi_hiv_aids_pms         BOOLEAN DEFAULT FALSE,
    metode_ganda_pakai_kondom    BOOLEAN DEFAULT FALSE,

    -- ===== D. Pemeriksaan Awal (9 items) =====
    tekanan_darah               VARCHAR(20),                          -- mis. "120/80"
    berat_badan                 NUMERIC(5,1),                         -- kg
    haid_terakhir               DATE,
    kebiasaan_merokok           BOOLEAN DEFAULT FALSE,
    sedang_menyusui             BOOLEAN DEFAULT FALSE,
    tanggal_persalinan_terakhir DATE,

    sakit_kuning                BOOLEAN DEFAULT FALSE,
    perdarahan_per_vaginam      BOOLEAN DEFAULT FALSE,
    tumor_payudara              BOOLEAN DEFAULT FALSE,

    keluhan                     TEXT,
    fluoralbus_gatal            BOOLEAN DEFAULT FALSE,
    fluoralbus_seperti_susu     BOOLEAN DEFAULT FALSE,
    fluoralbus_busa             BOOLEAN DEFAULT FALSE,
    fluoralbus_cair             BOOLEAN DEFAULT FALSE,

    -- ===== E. Khusus IUD =====
    iud_tanda_radang            BOOLEAN DEFAULT FALSE,
    iud_tumor                   BOOLEAN DEFAULT FALSE,
    iud_posisi_rahim            VARCHAR(20) CHECK (iud_posisi_rahim IN ('retro','antefleksi','normal') OR iud_posisi_rahim IS NULL),
    iud_genetalia_varices       BOOLEAN DEFAULT FALSE,
    iud_genetalia_jengger       BOOLEAN DEFAULT FALSE,
    iud_genetalia_condilo       BOOLEAN DEFAULT FALSE,
    iud_genetalia_bartholinitis BOOLEAN DEFAULT FALSE,

    -- ===== F. Tanggal Pelayanan & Status =====
    tanggal_dilayani            DATE NOT NULL DEFAULT CURRENT_DATE,
    tanggal_pesan_kontrol       DATE,                                 -- kontrol berikutnya
    tanggal_dilepas             DATE,                                 -- saat akseptor drop / lepas IUD-Implan
    status                      VARCHAR(20) NOT NULL DEFAULT 'aktif'
                                 CHECK (status IN ('aktif','drop','ganti_metode','selesai')),
    drop_reason                 TEXT,

    -- ===== G. Informed Consent =====
    consent_signed              BOOLEAN NOT NULL DEFAULT FALSE,
    consent_signed_at           TIMESTAMPTZ,
    consent_witness             VARCHAR(150),

    notes                       TEXT,

    -- Meta
    created_by    BIGINT REFERENCES tbm_users(id),
    served_by     BIGINT REFERENCES tbm_users(id),                    -- bidan yang layani
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ,
    deleted_date  TIMESTAMPTZ,

    CONSTRAINT uq_kb_acceptor_site_kartu UNIQUE (site_id, no_kartu_kb)
);

CREATE INDEX IF NOT EXISTS idx_kb_acceptors_site_status ON tbr_kb_acceptors(site_id, status) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_kb_acceptors_patient    ON tbr_kb_acceptors(patient_id);
CREATE INDEX IF NOT EXISTS idx_kb_acceptors_kontrasepsi ON tbr_kb_acceptors(kontrasepsi_id);

DROP TRIGGER IF EXISTS trg_tbr_kb_acceptors_updated ON tbr_kb_acceptors;
CREATE TRIGGER trg_tbr_kb_acceptors_updated
    BEFORE UPDATE ON tbr_kb_acceptors
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 2. tbr_kb_visits — Kunjungan Ulang KB
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_kb_visits (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    acceptor_id       BIGINT NOT NULL REFERENCES tbr_kb_acceptors(id) ON DELETE CASCADE,
    patient_visit_id  BIGINT REFERENCES tbr_patient_visits(id),  -- visit harian yang trigger kunjungan ini

    visit_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    haid_tanggal      DATE,
    berat_badan       NUMERIC(5,1),
    tekanan_darah     VARCHAR(20),

    keluhan           TEXT,
    efek_samping      TEXT,
    komplikasi        TEXT,
    tindakan          TEXT,

    tanggal_kembali   DATE,                                     -- jadwal kontrol berikutnya
    notes             TEXT,

    served_by     BIGINT REFERENCES tbm_users(id),
    created_by    BIGINT REFERENCES tbm_users(id),
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ,
    deleted_date  TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_kb_visits_acceptor_date ON tbr_kb_visits(acceptor_id, visit_date DESC);
CREATE INDEX IF NOT EXISTS idx_kb_visits_site_date     ON tbr_kb_visits(site_id, visit_date DESC) WHERE deleted_date IS NULL;

DROP TRIGGER IF EXISTS trg_tbr_kb_visits_updated ON tbr_kb_visits;
CREATE TRIGGER trg_tbr_kb_visits_updated
    BEFORE UPDATE ON tbr_kb_visits
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 3. tbr_informed_consents — Generic Informed Consent
--    (untuk KB, persalinan, anak, dll — module field via 'document_type')
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_informed_consents (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,

    document_type     VARCHAR(30) NOT NULL,           -- kb / anak / persalinan / dll
    document_id       BIGINT,                          -- ID record terkait (mis. acceptor_id)

    consent_text      TEXT NOT NULL,                   -- isi persetujuan
    signed_at         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    signed_by_name    VARCHAR(150) NOT NULL,           -- nama penandatangan (pasien/suami)
    signed_by_role    VARCHAR(50),                     -- akseptor / suami / wali
    witness_name      VARCHAR(150),                    -- bidan/saksi
    signature_url     VARCHAR(255),                    -- URL gambar ttd (jika digital)

    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    deleted_date  TIMESTAMPTZ
);
CREATE INDEX IF NOT EXISTS idx_consents_doc ON tbr_informed_consents(document_type, document_id);
CREATE INDEX IF NOT EXISTS idx_consents_patient ON tbr_informed_consents(patient_id);

-- =====================================================================
-- 4. PERMISSIONS
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    ('kb.view',     'Lihat Akseptor KB',     'pelayanan_kb', 'Akses data akseptor KB'),
    ('kb.create',   'Daftar Akseptor KB',    'pelayanan_kb', 'Catat akseptor baru'),
    ('kb.update',   'Update Akseptor KB',    'pelayanan_kb', 'Edit data akseptor / drop'),
    ('kb.delete',   'Hapus Akseptor KB',     'pelayanan_kb', 'Soft delete akseptor'),
    ('kb.visit',    'Catat Kunjungan KB',    'pelayanan_kb', 'Buat kunjungan ulang KB'),
    ('kb.print',    'Cetak Kartu KB',        'pelayanan_kb', 'Cetak kartu KB akseptor')
ON CONFLICT (name) DO NOTHING;

-- Grant ke super_admin, admin, dokter, perawat
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('super_admin', 'admin')
  AND p.name IN ('kb.view','kb.create','kb.update','kb.delete','kb.visit','kb.print')
ON CONFLICT DO NOTHING;

-- Dokter & Bidan: full kecuali delete
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('dokter', 'perawat')
  AND p.name IN ('kb.view','kb.create','kb.update','kb.visit','kb.print')
ON CONFLICT DO NOTHING;

-- Pendaftaran: view + print saja
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran'
  AND p.name IN ('kb.view','kb.print')
ON CONFLICT DO NOTHING;

-- =====================================================================
-- DONE Phase 1.3
-- Sequence KB sudah di-seed di Phase 1.0 (KB-{SITE2}-{YYYY}-{NNNNNN})
-- =====================================================================

-- Verifikasi:
-- SELECT fn_next_doc_number(1, 'KB');  -- KB-01-2026-000001
