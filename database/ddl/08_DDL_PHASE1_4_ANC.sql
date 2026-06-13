-- =====================================================================
-- DATABASE: klinik
-- PHASE   : 1.4 — Modul Ibu Hamil (ANC — Ante Natal Care)
-- DESC    : Sesuai Kartu Pemeriksaan IBU Hamil.xlsx Bu Tin
--           - Section A: Identitas (auto dari pasien)
--           - Section B: Riwayat Obstetri (G P A) + tabel per anak
--           - Section C: Pemeriksaan K1 (HPHT, HPL, TB, BB, VS, LILA, IMT)
--           - Section D: Perawatan selama hamil (tabel ANC visits multi-row)
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- 1. tbr_pregnancies — Header Kehamilan (1 record per kehamilan)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_pregnancies (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    patient_id        BIGINT NOT NULL REFERENCES tbm_patients(id) ON DELETE RESTRICT,
    patient_visit_id  BIGINT REFERENCES tbr_patient_visits(id),       -- visit awal (saat K1)

    no_kartu_hamil    VARCHAR(30) NOT NULL,                            -- MH-01-2026-000001

    -- ===== Riwayat Obstetri G P A =====
    gravida           INT NOT NULL DEFAULT 1,                          -- jumlah kehamilan termasuk ini
    partus            INT NOT NULL DEFAULT 0,                          -- jumlah lahir hidup/mati sebelumnya
    abortus           INT NOT NULL DEFAULT 0,                          -- jumlah keguguran
    hamil_ke          INT,                                              -- kehamilan ke-N (= gravida biasanya)

    -- ===== Pemeriksaan K1 (Kunjungan Pertama) =====
    tanggal_k1        DATE NOT NULL DEFAULT CURRENT_DATE,
    hpht              DATE,                                              -- Hari Pertama Haid Terakhir
    hpl               DATE,                                              -- Hari Perkiraan Lahir (auto HPHT + 280 hari)

    tinggi_badan_cm   NUMERIC(5,1),                                     -- TB (kalau perlu konversi cm)
    berat_badan_awal  NUMERIC(5,1),                                     -- BB sebelum hamil / awal K1
    lila_cm           NUMERIC(4,1),                                     -- Lingkar Lengan Atas (cm)
    imt               NUMERIC(4,1),                                     -- Indeks Massa Tubuh (auto-calc dari BB/TB)
    recom_kenaikan_bb VARCHAR(50),                                      -- Rekomendasi kenaikan BB (mis. "11.5-16 kg")

    vital_sign_td     VARCHAR(20),                                      -- TD K1 (mis. 120/80)

    riwayat_alergi    TEXT,
    riwayat_penyakit  TEXT,
    keluhan_awal      TEXT,                                              -- keluhan saat K1

    -- ===== Suami (catatan untuk laporan, snapshot saat hamil) =====
    suami_nama        VARCHAR(150),
    suami_umur        INT,
    suami_pendidikan_id BIGINT REFERENCES tbm_education_levels(id),
    suami_pekerjaan   VARCHAR(100),

    -- ===== Status =====
    status            VARCHAR(20) NOT NULL DEFAULT 'aktif'
                       CHECK (status IN ('aktif','partus','abortus','rujuk','lost')),
    tanggal_partus    DATE,                                              -- diisi nanti saat persalinan (Phase 1.5)
    tanggal_abortus   DATE,
    tanggal_selesai   DATE,                                              -- end of pregnancy tracking
    keterangan_akhir  TEXT,

    notes             TEXT,

    -- ===== Meta =====
    created_by        BIGINT REFERENCES tbm_users(id),
    served_by         BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,

    CONSTRAINT uq_pregnancies_site_no UNIQUE (site_id, no_kartu_hamil)
);

CREATE INDEX IF NOT EXISTS idx_preg_site_status ON tbr_pregnancies(site_id, status) WHERE deleted_date IS NULL;
CREATE INDEX IF NOT EXISTS idx_preg_patient    ON tbr_pregnancies(patient_id);
CREATE INDEX IF NOT EXISTS idx_preg_hpl        ON tbr_pregnancies(hpl) WHERE status='aktif';

DROP TRIGGER IF EXISTS trg_tbr_pregnancies_updated ON tbr_pregnancies;
CREATE TRIGGER trg_tbr_pregnancies_updated
    BEFORE UPDATE ON tbr_pregnancies
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 2. tbr_pregnancy_histories — Riwayat Kehamilan Sebelumnya
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_pregnancy_histories (
    id              BIGSERIAL PRIMARY KEY,
    site_id         BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    pregnancy_id    BIGINT NOT NULL REFERENCES tbr_pregnancies(id) ON DELETE CASCADE,

    hamil_ke        INT NOT NULL,                                       -- 1, 2, 3, dst
    tahun           INT,                                                 -- tahun bersalin
    jenis_kelamin   VARCHAR(1) CHECK (jenis_kelamin IN ('L','P') OR jenis_kelamin IS NULL),
    cara_lahir      VARCHAR(30) CHECK (cara_lahir IN ('spontan','sc','vakum','forceps','induksi','abortus','lainnya') OR cara_lahir IS NULL),
    bb_lahir_gram   INT,
    pb_lahir_cm     NUMERIC(4,1),
    tempat_bersalin VARCHAR(100),
    penolong        VARCHAR(100),                                        -- bidan/dokter/dukun
    kondisi_anak    VARCHAR(30) CHECK (kondisi_anak IN ('hidup_sehat','meninggal','lahir_mati','abortus','sakit') OR kondisi_anak IS NULL),
    komplikasi      TEXT,                                                -- komplikasi kehamilan/persalinan

    created_date    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date    TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_preg_hist_pregnancy ON tbr_pregnancy_histories(pregnancy_id);

-- =====================================================================
-- 3. tbr_anc_visits — Kunjungan Kontrol Kehamilan (multi-row per pregnancy)
-- =====================================================================
CREATE TABLE IF NOT EXISTS tbr_anc_visits (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    pregnancy_id      BIGINT NOT NULL REFERENCES tbr_pregnancies(id) ON DELETE CASCADE,
    patient_visit_id  BIGINT REFERENCES tbr_patient_visits(id),

    visit_date        DATE NOT NULL DEFAULT CURRENT_DATE,
    tempat_periksa    VARCHAR(150),                                      -- klinik/posyandu/RS/rumah

    -- Keluhan & Tanda Vital
    keluhan           TEXT,

    -- Pemeriksaan Obstetri
    tfu_cm            NUMERIC(4,1),                                      -- Tinggi Fundus Uteri (cm)
    uk_minggu         NUMERIC(4,1),                                      -- Umur Kehamilan (minggu, auto-calc dari HPHT)
    letak_janin       VARCHAR(30) CHECK (letak_janin IN ('kepala','bokong','lintang','tidak_tentu') OR letak_janin IS NULL),
    djj_per_menit     INT,                                                -- Denyut Jantung Janin per menit (120-160 normal)

    -- Vital Sign Ibu
    berat_badan_kg    NUMERIC(5,1),                                      -- BB saat ini
    tekanan_darah     VARCHAR(20),                                       -- Tensi (120/80)
    map               NUMERIC(5,1),                                      -- Mean Arterial Pressure

    -- Imunisasi TT
    status_tt         VARCHAR(10),                                        -- TT0/TT1/TT2/TT3/TT4/TT5
    pemberian_tt      BOOLEAN DEFAULT FALSE,                              -- TT diberikan di kunjungan ini?

    -- Tindakan
    terapi            TEXT,                                                -- obat/suplemen yang diberi
    hasil_lab         TEXT,                                                -- hasil pemeriksaan lab (Hb, urine, dll)
    penatalaksanaan   TEXT,                                                -- konseling, rujukan, edukasi

    tanggal_kembali   DATE,                                                -- jadwal kontrol berikutnya
    notes             TEXT,

    served_by         BIGINT REFERENCES tbm_users(id),
    created_by        BIGINT REFERENCES tbm_users(id),
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_anc_pregnancy_date ON tbr_anc_visits(pregnancy_id, visit_date DESC);
CREATE INDEX IF NOT EXISTS idx_anc_site_date     ON tbr_anc_visits(site_id, visit_date DESC) WHERE deleted_date IS NULL;

DROP TRIGGER IF EXISTS trg_tbr_anc_visits_updated ON tbr_anc_visits;
CREATE TRIGGER trg_tbr_anc_visits_updated
    BEFORE UPDATE ON tbr_anc_visits
    FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();

-- =====================================================================
-- 4. PERMISSIONS
-- =====================================================================
INSERT INTO tbm_permissions (name, display_name, module, description) VALUES
    ('anc.view',   'Lihat Data Kehamilan',   'pelayanan_anc', 'Akses data ibu hamil'),
    ('anc.create', 'Daftar Kehamilan (K1)',  'pelayanan_anc', 'Catat kehamilan baru / kunjungan pertama'),
    ('anc.update', 'Update Data Kehamilan',  'pelayanan_anc', 'Edit data kehamilan'),
    ('anc.delete', 'Hapus Data Kehamilan',   'pelayanan_anc', 'Soft delete kehamilan'),
    ('anc.visit',  'Catat ANC Visit',        'pelayanan_anc', 'Tambah kunjungan kontrol'),
    ('anc.print',  'Cetak Kartu Ibu Hamil',  'pelayanan_anc', 'Cetak kartu ibu hamil')
ON CONFLICT (name) DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('super_admin', 'admin')
  AND p.name IN ('anc.view','anc.create','anc.update','anc.delete','anc.visit','anc.print')
ON CONFLICT DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name IN ('dokter', 'perawat')
  AND p.name IN ('anc.view','anc.create','anc.update','anc.visit','anc.print')
ON CONFLICT DO NOTHING;

INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'pendaftaran'
  AND p.name IN ('anc.view','anc.print')
ON CONFLICT DO NOTHING;

-- =====================================================================
-- DONE Phase 1.4 DDL
-- Sequence MH sudah di-seed di Phase 1.0 (MH-{SITE2}-{YYYY}-{NNNNNN})
-- =====================================================================
