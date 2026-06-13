<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientVisit extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_patient_visits';
    const DELETED_AT = 'deleted_date';

    /** Kategori register sesuai workflow Bu Tin */
    public const CAT_ANAK       = 'A';
    public const CAT_IBU        = 'I';
    public const CAT_KB         = 'K';
    public const CAT_REPRODUKSI = 'R';

    public const STATUS_WAITING    = 'waiting';
    public const STATUS_IN_SERVICE = 'in_service';
    public const STATUS_DONE       = 'done';
    public const STATUS_CANCELLED  = 'cancelled';
    public const STATUS_NO_SHOW    = 'no_show';

    protected $fillable = [
        'site_id', 'patient_id',
        'no_register', 'category', 'visit_type',
        'visit_date', 'visit_time',
        'is_new_patient', 'payer_type_id',
        'queue_number', 'chief_complaint', 'notes',
        'status', 'cancel_reason',
        'created_by', 'served_by', 'served_at', 'completed_at',
    ];

    protected $casts = [
        'visit_date'     => 'date',
        'visit_time'     => 'datetime',
        'served_at'      => 'datetime',
        'completed_at'   => 'datetime',
        'is_new_patient' => 'boolean',
        'queue_number'   => 'integer',
    ];

    public static function categories(): array
    {
        return [
            self::CAT_IBU        => ['label' => 'Ibu',        'color' => 'danger',  'icon' => 'ki-heart-circle'],
            self::CAT_ANAK       => ['label' => 'Anak',       'color' => 'info',    'icon' => 'ki-baby'],
            self::CAT_KB         => ['label' => 'KB',         'color' => 'primary', 'icon' => 'ki-medical-cross'],
            self::CAT_REPRODUKSI => ['label' => 'Reproduksi', 'color' => 'warning', 'icon' => 'ki-flower'],
        ];
    }

    public static function visitTypes(): array
    {
        return [
            'baru'    => 'Pasien Baru',
            'kontrol' => 'Kontrol/Ulangan',
            'rujukan' => 'Rujukan',
            'darurat' => 'Darurat',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_WAITING    => ['label' => 'Menunggu',    'color' => 'warning'],
            self::STATUS_IN_SERVICE => ['label' => 'Dilayani',    'color' => 'info'],
            self::STATUS_DONE       => ['label' => 'Selesai',     'color' => 'success'],
            self::STATUS_CANCELLED  => ['label' => 'Dibatalkan',  'color' => 'danger'],
            self::STATUS_NO_SHOW    => ['label' => 'Tidak Hadir', 'color' => 'dark'],
        ];
    }

    /* ========== Relations ========== */
    public function site(): BelongsTo       { return $this->belongsTo(Site::class, 'site_id'); }
    public function patient(): BelongsTo    { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function payerType(): BelongsTo  { return $this->belongsTo(PayerType::class, 'payer_type_id'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
    public function servedBy(): BelongsTo   { return $this->belongsTo(User::class, 'served_by'); }

    /* ========== Scopes ========== */
    public function scopeToday(Builder $q): Builder
    {
        return $q->whereDate('visit_date', today());
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', [self::STATUS_WAITING, self::STATUS_IN_SERVICE]);
    }

    public function scopeOfCategory(Builder $q, ?string $cat): Builder
    {
        return $cat ? $q->where('category', $cat) : $q;
    }

    public function scopeOfStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) return $q;
        $like = '%' . $term . '%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('no_register', 'ilike', $like)
               ->orWhere('chief_complaint', 'ilike', $like)
               ->orWhereHas('patient', function ($pq) use ($like) {
                   $pq->where('no_rm', 'ilike', $like)
                      ->orWhere('name', 'ilike', $like)
                      ->orWhere('nik', 'ilike', $like);
               });
        });
    }

    /* ========== Accessors ========== */
    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category]['label'] ?? '-';
    }

    public function getCategoryColorAttribute(): string
    {
        return self::categories()[$this->category]['color'] ?? 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status]['label'] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::statuses()[$this->status]['color'] ?? 'secondary';
    }
}
