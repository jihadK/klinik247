-- ===================================================================
-- ALTER tbr_neonates — make delivery_id & pregnancy_id NULLABLE
-- ===================================================================
-- Untuk support walk-in anak (anak datang langsung tanpa rekam medis
-- ibu/persalinan di klinik ini).
--
-- IDEMPOTENT — kalau sudah nullable, tidak ada efek (PG ignore)
-- ===================================================================

BEGIN;

-- delivery_id → NULL allowed (untuk walk-in anak tanpa delivery)
ALTER TABLE tbr_neonates
    ALTER COLUMN delivery_id DROP NOT NULL;

-- pregnancy_id → NULL allowed (anak walk-in juga tanpa pregnancy record)
ALTER TABLE tbr_neonates
    ALTER COLUMN pregnancy_id DROP NOT NULL;

COMMIT;


-- ============================================================
-- VERIFIKASI
-- ============================================================
SELECT column_name, is_nullable, data_type
FROM information_schema.columns
WHERE table_name = 'tbr_neonates'
  AND column_name IN ('delivery_id', 'pregnancy_id', 'patient_id', 'site_id')
ORDER BY column_name;

-- Expected:
-- delivery_id    | YES | bigint
-- patient_id     | NO  | bigint
-- pregnancy_id   | YES | bigint
-- site_id        | NO  | bigint
