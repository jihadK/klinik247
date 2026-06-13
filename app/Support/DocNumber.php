<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Helper untuk panggil PG function fn_next_doc_number().
 *
 * Format ditentukan oleh tbs_document_sequences.format_pattern, contoh:
 *   {SITE2}-{YYYY}-{NNNNNN}        → 01-2026-000001  (no_rm)
 *   {KAT}-{SITE2}-{YYYY}-{NNNNNN}  → I-01-2026-000123 (register kunjungan)
 *   RX-{SITE2}-{YYYYMMDD}-{NNNN}   → RX-01-20260610-0123 (resep harian)
 *
 * Usage:
 *   DocNumber::next($siteId, 'RM');                  // 01-2026-000001
 *   DocNumber::next($siteId, 'REG', 'I');            // I-01-2026-000123
 *   DocNumber::next($siteId, 'MH');                  // MH-01-2026-000001
 *   DocNumber::next($siteId, 'RX');                  // RX-01-20260610-0123
 */
final class DocNumber
{
    public static function next(int $siteId, string $docType, ?string $category = null): string
    {
        $row = DB::selectOne(
            'SELECT fn_next_doc_number(?::bigint, ?::varchar, ?::varchar) AS num',
            [$siteId, $docType, $category]
        );

        if (! $row || empty($row->num)) {
            throw new \RuntimeException("fn_next_doc_number gagal: site=$siteId type=$docType cat=$category");
        }

        return $row->num;
    }
}
