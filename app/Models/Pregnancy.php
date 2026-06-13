<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pregnancy extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_pregnancies';
    const DELETED_AT = 'deleted_date';

    public const STATUS_AKTIF   = 'aktif';
    public const STATUS_PARTUS  = 'partus';
    public const STATUS_ABORTUS = 'abortus';
    public const STATUS_RUJUK   = 'rujuk';
    public const STATUS_LOST    = 'lost';

    public static function statuses(): array
    {
        return [
            self::STATUS_AKTIF   => ['label' => 'Aktif',        'color' => 'success', 'icon' => 'ki-heart-circle'],
            self::STATUS_PARTUS  => ['label' => 'Bersalin',     'color' => 'primary', 'icon' => 'ki-baby'],
            self::STATUS_ABORTUS => ['label' => 'Abortus',      'color' => 'danger',  'icon' => 'ki-cross-circle'],
            self::STATUS_RUJUK   => ['label' => 'Dirujuk',      'color' => 'warning', 'icon' => 'ki-arrow-right-square'],
            self::STATUS_LOST    => ['label' => 'Lost Follow-up','color' => 'secondary','icon' => 'ki-question-2'],
        ];
    }

    protected $fillable = [
        'site_id', 'patient_id', 'patient_visit_id',
        'no_kartu_hamil',
        'gravida', 'partus', 'abortus', 'hamil_ke',
        'tanggal_k1', 'hpht', 'hpl',
        'tinggi_badan_cm', 'berat_badan_awal', 'lila_cm', 'imt', 'recom_kenaikan_bb',
        'vital_sign_td',
        'riwayat_alergi', 'riwayat_penyakit', 'keluhan_awal',
        'suami_nama', 'suami_umur', 'suami_pendidikan_id', 'suami_pekerjaan',
        'status', 'tanggal_partus', 'tanggal_abortus', 'tanggal_selesai', 'keterangan_akhir',
        'notes', 'created_by', 'served_by',
    ];

    protected $casts = [
        'tanggal_k1'        => 'date',
        'hpht'              => 'date',
        'hpl'               => 'date',
        'tanggal_partus'    => 'date',
        'tanggal_abortus'   => 'date',
        'tanggal_selesai'   => 'date',
        'tinggi_badan_cm'   => 'decimal:1',
        'berat_badan_awal'  => 'decimal:1',
        'lila_cm'           => 'decimal:1',
        'imt'               => 'decimal:1',
        'gravida'           => 'integer',
        'partus'            => 'integer',
        'abortus'           => 'integer',
        'hamil_ke'          => 'integer',
        'suami_umur'        => 'integer',
    ];

    /* ========== Relations ========== */
    public function site(): BelongsTo       { return $this->belongsTo(Site::class, 'site_id'); }
    public function patient(): BelongsTo    { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function visit(): BelongsTo      { return $this->belongsTo(PatientVisit::class, 'patient_visit_id'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function servedBy(): BelongsTo   { return $this->belongsTo(User::class, 'served_by'); }
    public function suamiEducation(): BelongsTo { return $this->belongsTo(EducationLevel::class, 'suami_pendidikan_id'); }

    public function histories(): HasMany    { return $this->hasMany(PregnancyHistory::class, 'pregnancy_id')->orderBy('hamil_ke'); }
    public function ancVisits(): HasMany    { return $this->hasMany(AncVisit::class, 'pregnancy_id')->orderByDesc('visit_date'); }

    /* ========== Scopes ========== */
    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_AKTIF);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) return $q;
        $like = '%' . $term . '%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('no_kartu_hamil', 'ilike', $like)
               ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'ilike', $like)->orWhere('no_rm', 'ilike', $like));
        });
    }

    /* ========== Accessors ========== */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status]['label'] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::statuses()[$this->status]['color'] ?? 'secondary';
    }

    public function getGpaLabelAttribute(): string
    {
        return "G{$this->gravida} P{$this->partus} A{$this->abortus}";
    }

    /** Umur Kehamilan saat ini (minggu) — dihitung dari HPHT */
    public function getUkSekarangAttribute(): ?float
    {
        if (! $this->hpht) return null;
        // Arah HPHT→now agar positif untuk HPHT di masa lalu (kasus normal)
        $days = $this->hpht->startOfDay()->diffInDays(now()->startOfDay());
        return round($days / 7, 1);
    }

    /** Hari menuju HPL */
    public function getHariMenujuHplAttribute(): ?int
    {
        if (! $this->hpl) return null;
        // Arah now→HPL agar positif kalau HPL di masa depan (belum lahir), negatif kalau sudah lewat
        return (int) now()->startOfDay()->diffInDays($this->hpl->startOfDay());
    }

    /** Trimester saat ini (1/2/3) */
    public function getTrimesterAttribute(): ?int
    {
        $uk = $this->uk_sekarang;
        if ($uk === null) return null;
        if ($uk < 13) return 1;
        if ($uk < 28) return 2;
        return 3;
    }
}
