-- ===================================================================
-- SEED tbs_document_sequences — defensive seed untuk site baru
-- ===================================================================
-- Schema actual (verified):
--   site_id, doc_type, prefix, current_number, reset_period,
--   category (NULL allowed), format_pattern
--   UNIQUE (site_id, doc_type, category)
-- ===================================================================
-- IDEMPOTENT: ON CONFLICT DO NOTHING — sequence existing tidak ke-reset
-- Auto-seed semua doc_type ke semua site aktif yang belum punya
-- ===================================================================

INSERT INTO tbs_document_sequences
    (site_id, doc_type, prefix, current_number, reset_period, category, format_pattern)
SELECT s.id, d.doc_type, d.prefix, 0, d.reset_period, d.category, d.format_pattern
FROM tbm_sites s
CROSS JOIN (VALUES
    ('RM',  '',   'yearly', NULL::TEXT, '{SITE2}-{YYYY}-{NNNNNN}'),
    ('REG', '',   'yearly', 'A',        '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    ('REG', '',   'yearly', 'I',        '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    ('REG', '',   'yearly', 'K',        '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    ('REG', '',   'yearly', 'R',        '{KAT}-{SITE2}-{YYYY}-{NNNNNN}'),
    ('MH',  'MH', 'yearly', NULL,       'MH-{SITE2}-{YYYY}-{NNNNNN}'),
    ('PS',  'PS', 'yearly', NULL,       'PS-{SITE2}-{YYYY}-{NNNNNN}'),
    ('BB',  'BB', 'yearly', NULL,       'BB-{SITE2}-{YYYY}-{NNNNNN}'),
    ('KB',  'KB', 'yearly', NULL,       'KB-{SITE2}-{YYYY}-{NNNNNN}'),
    ('NF',  'NF', 'yearly', NULL,       'NF-{SITE2}-{YYYY}-{NNNNNN}'),
    ('RX',  'RX', 'daily',  NULL,       'RX-{SITE2}-{YYYYMMDD}-{NNNN}'),
    ('INV', 'INV','daily',  NULL,       'INV-{SITE2}-{YYYYMMDD}-{NNNN}'),
    ('STK', 'STK','daily',  NULL,       'STK-{SITE2}-{YYYYMMDD}-{NNNN}'),
    ('EMP', 'EMP','never',  NULL,       'EMP-{SITE2}-{NNNN}')
) AS d(doc_type, prefix, reset_period, category, format_pattern)
WHERE s.is_active = TRUE
ON CONFLICT (site_id, doc_type, category) DO NOTHING;


-- ============================================================
-- VERIFIKASI
-- ============================================================
SELECT
    s.code   AS site_code,
    s.name   AS site_name,
    seq.doc_type,
    seq.category,
    seq.current_number,
    seq.format_pattern
FROM tbs_document_sequences seq
JOIN tbm_sites s ON s.id = seq.site_id
WHERE seq.doc_type IN ('MH', 'PS', 'BB', 'KB', 'RM', 'REG')
ORDER BY s.code, seq.doc_type, seq.category NULLS FIRST;
