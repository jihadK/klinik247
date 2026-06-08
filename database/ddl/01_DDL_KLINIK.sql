-- =====================================================================
-- DATABASE: klinik
-- DBMS    : PostgreSQL 14+
-- DESC    : Aplikasi Klinik247 — Phase 0 (Master & Auth saja)
--           Multi-tenant via site_id (shared DB + isolation by column)
-- NOTE    : Tabel TRANSAKSI (appointments/visits/payments/prescriptions)
--           SKIP dulu — akan didesain ulang sesuai contoh manual klinik
-- =====================================================================

SET client_min_messages = WARNING;
SET timezone = 'Asia/Jakarta';

-- =====================================================================
-- EXTENSIONS
-- =====================================================================
CREATE EXTENSION IF NOT EXISTS pgcrypto;
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- =====================================================================
-- 1. MULTI-TENANT MASTER (no site_id — global)
-- =====================================================================

-- 1.1 SITES (master tenant/klinik)
CREATE TABLE tbm_sites (
    id                BIGSERIAL PRIMARY KEY,
    code              VARCHAR(20) UNIQUE NOT NULL,    -- KLN-001
    name              VARCHAR(150) NOT NULL,           -- Pondok Bersalin Bu Tin
    slug              VARCHAR(100) UNIQUE NOT NULL,    -- pondok-bu-tin
    address           TEXT,
    city              VARCHAR(100),
    phone             VARCHAR(20),
    email             VARCHAR(100),
    logo_url          VARCHAR(255),
    timezone          VARCHAR(50) DEFAULT 'Asia/Jakarta',
    settings          JSONB DEFAULT '{}'::jsonb,
    subscription_until DATE,
    is_active         BOOLEAN NOT NULL DEFAULT TRUE,
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ
);
CREATE INDEX idx_sites_slug ON tbm_sites(slug) WHERE deleted_date IS NULL;
CREATE INDEX idx_sites_active ON tbm_sites(is_active) WHERE deleted_date IS NULL;

-- 1.2 ROLES (master — sama untuk semua site)
CREATE TABLE tbm_roles (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    is_super    BOOLEAN NOT NULL DEFAULT FALSE,  -- TRUE = role akses lintas-site
    created_date TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date TIMESTAMPTZ
);

-- 1.3 PERMISSIONS (master — granular permission)
CREATE TABLE tbm_permissions (
    id           BIGSERIAL PRIMARY KEY,
    name         VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(150) NOT NULL,
    module       VARCHAR(50) NOT NULL,
    description  VARCHAR(255),
    is_active    BOOLEAN NOT NULL DEFAULT TRUE,
    created_date TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_permissions_module ON tbm_permissions(module);

-- 1.4 ROLE-PERMISSIONS (M:N)
CREATE TABLE tbm_role_permissions (
    id            BIGSERIAL PRIMARY KEY,
    role_id       BIGINT NOT NULL REFERENCES tbm_roles(id) ON DELETE CASCADE,
    permission_id BIGINT NOT NULL REFERENCES tbm_permissions(id) ON DELETE CASCADE,
    granted_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_role_perm UNIQUE (role_id, permission_id)
);

-- 1.5 SPECIALTIES (poli — global; bisa dipakai semua klinik)
CREATE TABLE tbm_specialties (
    id          BIGSERIAL PRIMARY KEY,
    code        VARCHAR(20) NOT NULL UNIQUE,    -- UMUM, GIGI, ANAK, KANDUNGAN
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_date TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- =====================================================================
-- 2. USERS & AUTH (site_id = NULL → super admin)
-- =====================================================================

-- 2.1 USERS
CREATE TABLE tbm_users (
    id                      BIGSERIAL PRIMARY KEY,
    site_id                 BIGINT REFERENCES tbm_sites(id) ON DELETE RESTRICT,  -- NULL = super admin
    role_id                 BIGINT NOT NULL REFERENCES tbm_roles(id) ON DELETE RESTRICT,
    username                VARCHAR(50) NOT NULL,
    email                   VARCHAR(100),
    password_hash           VARCHAR(255) NOT NULL,
    full_name               VARCHAR(100) NOT NULL,
    phone                   VARCHAR(20),
    is_active               BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at           TIMESTAMPTZ,
    failed_login_attempts   INT NOT NULL DEFAULT 0,
    locked_until            TIMESTAMPTZ,
    password_changed_at     TIMESTAMPTZ,
    must_change_password    BOOLEAN NOT NULL DEFAULT FALSE,
    created_date            TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date            TIMESTAMPTZ,
    deleted_date            TIMESTAMPTZ,
    -- Username unique per-site
    CONSTRAINT uq_users_site_username UNIQUE (site_id, username),
    CONSTRAINT chk_users_email CHECK (email IS NULL OR email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')
);
CREATE INDEX idx_users_site   ON tbm_users(site_id) WHERE deleted_date IS NULL;
CREATE INDEX idx_users_role   ON tbm_users(role_id);
CREATE INDEX idx_users_active ON tbm_users(is_active) WHERE deleted_date IS NULL;
CREATE INDEX idx_users_locked ON tbm_users(locked_until) WHERE locked_until IS NOT NULL;

-- 2.2 USER PERMISSIONS (override per-user)
CREATE TABLE tbm_user_permissions (
    id            BIGSERIAL PRIMARY KEY,
    user_id       BIGINT NOT NULL REFERENCES tbm_users(id) ON DELETE CASCADE,
    permission_id BIGINT NOT NULL REFERENCES tbm_permissions(id) ON DELETE CASCADE,
    grant_type    VARCHAR(10) NOT NULL DEFAULT 'allow' CHECK (grant_type IN ('allow', 'deny')),
    granted_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    granted_by    BIGINT REFERENCES tbm_users(id) ON DELETE SET NULL,
    expires_at    TIMESTAMPTZ,
    notes         VARCHAR(255),
    CONSTRAINT uq_user_perm UNIQUE (user_id, permission_id)
);
CREATE INDEX idx_user_perm_user ON tbm_user_permissions(user_id);

-- 2.3 LOGIN ATTEMPTS (audit)
CREATE TABLE tbh_login_attempts (
    id             BIGSERIAL PRIMARY KEY,
    site_id        BIGINT REFERENCES tbm_sites(id) ON DELETE SET NULL,
    user_id        BIGINT REFERENCES tbm_users(id) ON DELETE SET NULL,
    username       VARCHAR(100),
    user_type      VARCHAR(20) NOT NULL DEFAULT 'admin' CHECK (user_type IN ('admin','patient')),
    ip_address     INET NOT NULL,
    user_agent     VARCHAR(255),
    success        BOOLEAN NOT NULL,
    failure_reason VARCHAR(100),
    attempted_at   TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_login_user_date ON tbh_login_attempts(user_id, attempted_at DESC);
CREATE INDEX idx_login_ip_date   ON tbh_login_attempts(ip_address, attempted_at DESC);

-- =====================================================================
-- 3. MASTER DATA (per-site)
-- =====================================================================

-- 3.1 DOCTORS (dokter)
CREATE TABLE tbm_doctors (
    id                BIGSERIAL PRIMARY KEY,
    site_id           BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    user_id           BIGINT REFERENCES tbm_users(id) ON DELETE SET NULL,  -- linked ke akun login
    specialty_id      BIGINT REFERENCES tbm_specialties(id) ON DELETE SET NULL,
    code              VARCHAR(20) NOT NULL,
    name              VARCHAR(100) NOT NULL,
    str_number        VARCHAR(30),
    sip_number        VARCHAR(30),
    consultation_fee  NUMERIC(14,2) DEFAULT 0,
    photo_url         VARCHAR(255),
    bio               TEXT,
    phone             VARCHAR(20),
    email             VARCHAR(100),
    is_active         BOOLEAN NOT NULL DEFAULT TRUE,
    created_date      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date      TIMESTAMPTZ,
    deleted_date      TIMESTAMPTZ,
    CONSTRAINT uq_doctors_site_code UNIQUE (site_id, code)
);
CREATE INDEX idx_doctors_site ON tbm_doctors(site_id) WHERE deleted_date IS NULL;
CREATE INDEX idx_doctors_specialty ON tbm_doctors(specialty_id);

-- 3.2 PATIENTS (pasien)
CREATE TABLE tbm_patients (
    id                  BIGSERIAL PRIMARY KEY,
    site_id             BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    no_rm               VARCHAR(20) NOT NULL,           -- format: RM-YYYY-NNNNNN
    nik                 VARCHAR(16),                     -- NIK KTP
    name                VARCHAR(150) NOT NULL,
    birth_date          DATE NOT NULL,
    birth_place         VARCHAR(100),
    gender              VARCHAR(10) CHECK (gender IN ('L','P')),
    address             TEXT,
    rt_rw               VARCHAR(20),
    village             VARCHAR(100),
    sub_district        VARCHAR(100),
    city                VARCHAR(100),
    province            VARCHAR(100),
    postal_code         VARCHAR(10),
    phone               VARCHAR(20),
    email               VARCHAR(100),
    bpjs_number         VARCHAR(20),
    bpjs_class          VARCHAR(20) CHECK (bpjs_class IN ('1','2','3','Mandiri','PBI','PBPU','PPU')),
    blood_type          VARCHAR(5) CHECK (blood_type IN ('A+','A-','B+','B-','AB+','AB-','O+','O-')),
    marital_status      VARCHAR(20) CHECK (marital_status IN ('belum_menikah','menikah','cerai_hidup','cerai_mati')),
    occupation          VARCHAR(100),
    religion            VARCHAR(20) CHECK (religion IN ('Islam','Kristen','Katolik','Hindu','Buddha','Konghucu','Lainnya')),
    allergies           TEXT,                            -- alergi obat/makanan
    chronic_diseases    TEXT,                            -- penyakit kronis
    medical_history     TEXT,                            -- riwayat penyakit
    emergency_contact   VARCHAR(150),
    emergency_phone     VARCHAR(20),
    emergency_relation  VARCHAR(50),
    photo_url           VARCHAR(255),
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at       TIMESTAMPTZ,                     -- tracking portal login
    created_date        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date        TIMESTAMPTZ,
    deleted_date        TIMESTAMPTZ,
    CONSTRAINT uq_patients_site_norm UNIQUE (site_id, no_rm)
);
CREATE INDEX idx_patients_site ON tbm_patients(site_id) WHERE deleted_date IS NULL;
CREATE INDEX idx_patients_name_trgm ON tbm_patients USING gin (name gin_trgm_ops);
CREATE INDEX idx_patients_nik ON tbm_patients(nik) WHERE nik IS NOT NULL AND deleted_date IS NULL;
CREATE INDEX idx_patients_bpjs ON tbm_patients(bpjs_number) WHERE bpjs_number IS NOT NULL;

-- 3.3 SERVICES (layanan/tindakan)
CREATE TABLE tbm_services (
    id            BIGSERIAL PRIMARY KEY,
    site_id       BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    code          VARCHAR(20) NOT NULL,
    name          VARCHAR(150) NOT NULL,
    category      VARCHAR(30) NOT NULL DEFAULT 'tindakan' CHECK (category IN ('konsultasi','pemeriksaan','tindakan','lab','imunisasi','lainnya')),
    price         NUMERIC(14,2) NOT NULL DEFAULT 0,
    description   TEXT,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ,
    CONSTRAINT uq_services_site_code UNIQUE (site_id, code)
);
CREATE INDEX idx_services_site ON tbm_services(site_id);

-- 3.4 MEDICINES (obat)
CREATE TABLE tbm_medicines (
    id            BIGSERIAL PRIMARY KEY,
    site_id       BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    code          VARCHAR(30) NOT NULL,
    name          VARCHAR(150) NOT NULL,
    generic_name  VARCHAR(150),
    form          VARCHAR(30) CHECK (form IN ('tablet','kapsul','sirup','salep','krim','tetes','injeksi','suppositoria','inhaler','lainnya')),
    strength      VARCHAR(50),                          -- "500 mg", "5 mg/ml"
    unit          VARCHAR(20) DEFAULT 'tablet',          -- tablet, botol, tube
    price         NUMERIC(14,2) NOT NULL DEFAULT 0,
    stock         INT NOT NULL DEFAULT 0,
    min_stock     INT NOT NULL DEFAULT 0,
    is_active     BOOLEAN NOT NULL DEFAULT TRUE,
    created_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date  TIMESTAMPTZ,
    CONSTRAINT uq_medicines_site_code UNIQUE (site_id, code)
);
CREATE INDEX idx_medicines_site ON tbm_medicines(site_id);
CREATE INDEX idx_medicines_name_trgm ON tbm_medicines USING gin (name gin_trgm_ops);

-- 3.5 DOCTOR SCHEDULES (jadwal mingguan — master, bukan transaksi)
CREATE TABLE tbm_doctor_schedules (
    id                    BIGSERIAL PRIMARY KEY,
    site_id               BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE RESTRICT,
    doctor_id             BIGINT NOT NULL REFERENCES tbm_doctors(id) ON DELETE CASCADE,
    day_of_week           INT NOT NULL CHECK (day_of_week BETWEEN 0 AND 6),  -- 0=Minggu, 6=Sabtu
    start_time            TIME NOT NULL,
    end_time              TIME NOT NULL,
    slot_duration_minutes INT NOT NULL DEFAULT 15,
    max_patients          INT NOT NULL DEFAULT 20,
    is_active             BOOLEAN NOT NULL DEFAULT TRUE,
    created_date          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_date          TIMESTAMPTZ,
    CONSTRAINT chk_schedule_time CHECK (end_time > start_time)
);
CREATE INDEX idx_schedules_site_doctor ON tbm_doctor_schedules(site_id, doctor_id);

-- =====================================================================
-- 4. SYSTEM (audit, sequences, settings)
-- =====================================================================

-- 4.1 AUDIT LOGS (history change tracking)
CREATE TABLE tbh_audit_logs (
    id          BIGSERIAL PRIMARY KEY,
    site_id     BIGINT REFERENCES tbm_sites(id) ON DELETE SET NULL,
    user_id     BIGINT REFERENCES tbm_users(id) ON DELETE SET NULL,
    table_name  VARCHAR(50) NOT NULL,
    record_id   BIGINT NOT NULL,
    action      VARCHAR(20) NOT NULL CHECK (action IN ('create','update','delete','restore')),
    old_values  JSONB,
    new_values  JSONB,
    ip_address  INET,
    user_agent  VARCHAR(255),
    created_date TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_audit_record ON tbh_audit_logs(table_name, record_id);
CREATE INDEX idx_audit_site_date ON tbh_audit_logs(site_id, created_date DESC);

-- 4.2 DOCUMENT SEQUENCES (auto-numbering per site)
CREATE TABLE tbs_document_sequences (
    id              BIGSERIAL PRIMARY KEY,
    site_id         BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE CASCADE,
    doc_type        VARCHAR(20) NOT NULL,           -- RM (untuk no_rm pasien)
    prefix          VARCHAR(20) NOT NULL,
    current_number  INT NOT NULL DEFAULT 0,
    reset_period    VARCHAR(10) NOT NULL DEFAULT 'yearly' CHECK (reset_period IN ('never','yearly','monthly')),
    last_reset_at   DATE,
    updated_date    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_sequences_site_type UNIQUE (site_id, doc_type)
);

-- 4.3 SETTINGS (config per site)
CREATE TABLE tbs_settings (
    id            BIGSERIAL PRIMARY KEY,
    site_id       BIGINT NOT NULL REFERENCES tbm_sites(id) ON DELETE CASCADE,
    key           VARCHAR(100) NOT NULL,
    value         TEXT,
    description   VARCHAR(255),
    updated_date  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT uq_settings_site_key UNIQUE (site_id, key)
);

-- =====================================================================
-- 5. TRIGGERS
-- =====================================================================

-- 5.1 Generic updated_date trigger
CREATE OR REPLACE FUNCTION trg_set_updated_date()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_date = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DO $$
DECLARE
    t TEXT;
    tables TEXT[] := ARRAY[
        'tbm_sites','tbm_roles','tbm_users','tbm_doctors','tbm_patients',
        'tbm_services','tbm_medicines','tbm_doctor_schedules'
    ];
BEGIN
    FOREACH t IN ARRAY tables LOOP
        EXECUTE format(
            'CREATE TRIGGER trg_%s_updated_date BEFORE UPDATE ON %I
             FOR EACH ROW EXECUTE FUNCTION trg_set_updated_date();', t, t);
    END LOOP;
END$$;

-- =====================================================================
-- 6. FUNCTIONS
-- =====================================================================

-- 6.1 fn_user_has_permission(user_id, permission_name)
CREATE OR REPLACE FUNCTION fn_user_has_permission(
    p_user_id    BIGINT,
    p_permission VARCHAR
) RETURNS BOOLEAN AS $$
DECLARE
    v_has BOOLEAN;
BEGIN
    -- 1. Cek explicit deny (selalu menang)
    SELECT TRUE INTO v_has
    FROM tbm_user_permissions up
    JOIN tbm_permissions p ON p.id = up.permission_id
    WHERE up.user_id = p_user_id
      AND p.name = p_permission
      AND up.grant_type = 'deny'
      AND (up.expires_at IS NULL OR up.expires_at > NOW())
    LIMIT 1;
    IF v_has THEN RETURN FALSE; END IF;

    -- 2. Explicit allow override
    SELECT TRUE INTO v_has
    FROM tbm_user_permissions up
    JOIN tbm_permissions p ON p.id = up.permission_id
    WHERE up.user_id = p_user_id
      AND p.name = p_permission
      AND up.grant_type = 'allow'
      AND (up.expires_at IS NULL OR up.expires_at > NOW())
    LIMIT 1;
    IF v_has THEN RETURN TRUE; END IF;

    -- 3. From role
    SELECT TRUE INTO v_has
    FROM tbm_users u
    JOIN tbm_role_permissions rp ON rp.role_id = u.role_id
    JOIN tbm_permissions p ON p.id = rp.permission_id
    WHERE u.id = p_user_id
      AND p.name = p_permission
      AND p.is_active = TRUE
      AND u.is_active = TRUE
      AND u.deleted_date IS NULL
    LIMIT 1;

    RETURN COALESCE(v_has, FALSE);
END;
$$ LANGUAGE plpgsql STABLE;

-- 6.2 fn_next_doc_number(site_id, doc_type) — auto-numbering (untuk no_rm)
CREATE OR REPLACE FUNCTION fn_next_doc_number(p_site_id BIGINT, p_doc_type VARCHAR)
RETURNS VARCHAR AS $$
DECLARE
    v_seq          tbs_document_sequences%ROWTYPE;
    v_should_reset BOOLEAN := FALSE;
    v_new_number   VARCHAR;
BEGIN
    SELECT * INTO v_seq FROM tbs_document_sequences
    WHERE site_id = p_site_id AND doc_type = p_doc_type
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'doc_type % not found for site %', p_doc_type, p_site_id;
    END IF;

    IF v_seq.reset_period = 'yearly' AND
       (v_seq.last_reset_at IS NULL OR EXTRACT(YEAR FROM v_seq.last_reset_at) <> EXTRACT(YEAR FROM CURRENT_DATE))
    THEN
        v_should_reset := TRUE;
    ELSIF v_seq.reset_period = 'monthly' AND
       (v_seq.last_reset_at IS NULL OR DATE_TRUNC('month',v_seq.last_reset_at) <> DATE_TRUNC('month',CURRENT_DATE))
    THEN
        v_should_reset := TRUE;
    END IF;

    IF v_should_reset THEN
        UPDATE tbs_document_sequences
           SET current_number = 1, last_reset_at = CURRENT_DATE, updated_date = NOW()
         WHERE id = v_seq.id RETURNING * INTO v_seq;
    ELSE
        UPDATE tbs_document_sequences
           SET current_number = current_number + 1, updated_date = NOW()
         WHERE id = v_seq.id RETURNING * INTO v_seq;
    END IF;

    -- Format: {prefix}{YYYY}-{NNNNNN}
    -- mis. RM-2026-000001
    v_new_number := v_seq.prefix || TO_CHAR(CURRENT_DATE,'YYYY') || '-'
                 || LPAD(v_seq.current_number::TEXT, 6, '0');
    RETURN v_new_number;
END;
$$ LANGUAGE plpgsql;

-- =====================================================================
-- 7. VIEWS
-- =====================================================================

-- 7.1 User permissions effective
CREATE OR REPLACE VIEW v_user_permissions AS
SELECT DISTINCT
    u.id   AS user_id, u.site_id, u.username, u.full_name,
    p.id   AS permission_id, p.name AS permission_name,
    p.module, p.display_name, 'role' AS source
FROM tbm_users u
JOIN tbm_role_permissions rp ON rp.role_id = u.role_id
JOIN tbm_permissions p ON p.id = rp.permission_id
WHERE u.is_active = TRUE AND u.deleted_date IS NULL AND p.is_active = TRUE
  AND NOT EXISTS (
      SELECT 1 FROM tbm_user_permissions up
      WHERE up.user_id = u.id AND up.permission_id = p.id
        AND up.grant_type = 'deny'
        AND (up.expires_at IS NULL OR up.expires_at > NOW()))
UNION
SELECT DISTINCT
    u.id, u.site_id, u.username, u.full_name,
    p.id, p.name, p.module, p.display_name, 'override' AS source
FROM tbm_users u
JOIN tbm_user_permissions up ON up.user_id = u.id
JOIN tbm_permissions p ON p.id = up.permission_id
WHERE up.grant_type = 'allow'
  AND (up.expires_at IS NULL OR up.expires_at > NOW())
  AND u.is_active = TRUE AND u.deleted_date IS NULL;

SELECT 'Klinik247 DDL Phase 0 (master + auth + system) installed successfully.' AS status;
