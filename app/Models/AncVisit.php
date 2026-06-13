<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AncVisit extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_anc_visits';
    const DELETED_AT = 'deleted_date';

    protected $fillable = [
        'site_id', 'pregnancy_id', 'patient_visit_id',
        'visit_date', 'tempat_periksa',
        'keluhan',
        'tfu_cm', 'uk_minggu', 'letak_janin', 'djj_per_menit',
        'berat_badan_kg', 'tekanan_darah', 'map',
        'status_tt', 'pemberian_tt',
        'terapi', 'hasil_lab', 'penatalaksanaan',
        'tanggal_kembali', 'notes',
        'served_by', 'created_by',
    ];

    protected $casts = [
        'visit_date'      => 'date',
        'tanggal_kembali' => 'date',
        'tfu_cm'          => 'decimal:1',
        'uk_minggu'       => 'decimal:1',
        'djj_per_menit'   => 'integer',
        'berat_badan_kg'  => 'decimal:1',
        'map'             => 'decimal:1',
        'pemberian_tt'    => 'boolean',
    ];

    public function pregnancy(): BelongsTo { return $this->belongsTo(Pregnancy::class, 'pregnancy_id'); }
    public function patientVisit(): BelongsTo { return $this->belongsTo(PatientVisit::class, 'patient_visit_id'); }
    public function servedBy(): BelongsTo { return $this->belongsTo(User::class, 'served_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public static function letakOptions(): array
    {
        return [
            'kepala'      => 'Kepala (presentasi normal)',
            'bokong'      => 'Bokong',
            'lintang'     => 'Lintang',
            'tidak_tentu' => 'Belum tentu (UK < 28)',
        ];
    }

    public static function statusTtOptions(): array
    {
        return ['TT0', 'TT1', 'TT2', 'TT3', 'TT4', 'TT5'];
    }

    public static function tempatPeriksaOptions(): array
    {
        return [
            'klinik'        => 'Klinik / BPM',
            'polindes'      => 'Polindes',
            'puskesmas'     => 'Puskesmas',
            'posyandu'      => 'Posyandu',
            'rs_pemerintah' => 'RS Pemerintah',
            'rs_swasta'     => 'RS Swasta',
            'kunjungan_rumah' => 'Kunjungan Rumah',
            'lainnya'       => 'Lainnya',
        ];
    }

    /** Suggest next TT level berdasar last visit (TT0→TT1, TT1→TT2, dst sampai TT5) */
    public static function suggestNextTt(?string $lastStatus, bool $lastDiberi): ?string
    {
        if (! $lastStatus || ! $lastDiberi) return $lastStatus;
        $levels = ['TT0', 'TT1', 'TT2', 'TT3', 'TT4', 'TT5'];
        $idx = array_search($lastStatus, $levels);
        if ($idx === false || $idx >= count($levels) - 1) return $lastStatus;
        return $levels[$idx + 1];
    }

    public function scopeOrderRecent(Builder $q): Builder
    {
        return $q->orderByDesc('visit_date');
    }
}
