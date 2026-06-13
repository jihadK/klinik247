-- =====================================================================
-- PATCH: tambah field letterhead ke tbm_sites untuk surat resmi
-- =====================================================================

SET client_min_messages = WARNING;

ALTER TABLE tbm_sites
    ADD COLUMN IF NOT EXISTS letterhead_subtitle    VARCHAR(200),   -- "Praktik Mandiri Bidan - Bidan I'Annatus Sa'diyah"
    ADD COLUMN IF NOT EXISTS letterhead_director    VARCHAR(150),   -- Nama bidan PJ default
    ADD COLUMN IF NOT EXISTS letterhead_sipb        VARCHAR(50),    -- No. SIPB
    ADD COLUMN IF NOT EXISTS letterhead_city        VARCHAR(100);   -- Kota tempat surat dibuat

-- Update untuk KLN-001 (Bu Tin) sebagai contoh
UPDATE tbm_sites
   SET letterhead_subtitle = 'Praktik Mandiri Bidan (PMB)',
       letterhead_director = 'I''annatus Sa''diyah, A.Md.Keb',
       letterhead_sipb     = 'SIPB/PMB-001/2024',
       letterhead_city     = 'Lamongan'
 WHERE code = 'KLN-001';

UPDATE tbm_sites
   SET letterhead_subtitle = 'Klinik Bidan',
       letterhead_director = 'Bidan Penanggung Jawab',
       letterhead_sipb     = 'SIPB/-',
       letterhead_city     = COALESCE(city, 'Lamongan')
 WHERE code = 'KLN-002';
