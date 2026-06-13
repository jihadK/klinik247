-- Tambah kolom upload kop surat (image) ke tbm_sites
SET client_min_messages = WARNING;

ALTER TABLE tbm_sites
    ADD COLUMN IF NOT EXISTS kop_image_url VARCHAR(255);

-- Grant sites.view & sites.update ke admin biar bisa edit klinik sendiri
-- (sites.create & sites.delete tetap super_admin only)
INSERT INTO tbm_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM tbm_roles r CROSS JOIN tbm_permissions p
WHERE r.name = 'admin'
  AND p.name IN ('sites.view', 'sites.update')
ON CONFLICT (role_id, permission_id) DO NOTHING;
