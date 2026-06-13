<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Neonate extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_neonates';
    const DELETED_AT = 'deleted_date';

    public static function statuses(): array
    {
        return [
            'hidup_sehat' => ['label' => 'Hidup Sehat',  'color' => 'success'],
            'hidup_sakit' => ['label' => 'Hidup Sakit',  'color' => 'warning'],
            'dirujuk'     => ['label' => 'Dirujuk',      'color' => 'info'],
            'meninggal'   => ['label' => 'Meninggal',    'color' => 'danger'],
        ];
    }

    protected $fillable = [
        'site_id', 'delivery_id', 'pregnancy_id', 'patient_id',
        'no_kartu_bayi', 'nama_bayi', 'jenis_kelamin',
        'tanggal_lahir', 'jam_lahir',
        'bb_lahir_gram', 'pb_lahir_cm', 'cara_lahir',
        'apgar_1', 'apgar_5',
        'imd_dilakukan', 'vit_k1_diberi', 'vit_k1_at',
        'salep_mata', 'hb0_diberi', 'hb0_at', 'hb0_batch',
        'status', 'keterangan_akhir', 'notes',
        'created_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'vit_k1_at'     => 'datetime',
        'hb0_at'        => 'datetime',
        'pb_lahir_cm'   => 'decimal:1',
        'imd_dilakukan' => 'boolean',
        'vit_k1_diberi' => 'boolean',
        'salep_mata'    => 'boolean',
        'hb0_diberi'    => 'boolean',
    ];

    public function site(): BelongsTo      { return $this->belongsTo(Site::class, 'site_id'); }
    public function delivery(): BelongsTo  { return $this->belongsTo(Delivery::class, 'delivery_id'); }
    public function pregnancy(): BelongsTo { return $this->belongsTo(Pregnancy::class, 'pregnancy_id'); }
    public function patient(): BelongsTo   { return $this->belongsTo(Patient::class, 'patient_id'); }      // ibu
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function visits(): HasMany
    {
        return $this->hasMany(NeonatalVisit::class, 'neonate_id')->orderBy('kn_number');
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(ImmunizationRecord::class, 'neonate_id')->orderBy('given_date');
    }

    public function childVisits(): HasMany
    {
        return $this->hasMany(ChildVisit::class, 'neonate_id')->orderByDesc('visit_date');
    }

    /** Apakah sudah masuk fase anak (> 28 hari = post neonatal period) */
    public function getIsChildAttribute(): bool
    {
        return $this->umur_hari !== null && $this->umur_hari > 28;
    }

    /** Umur dalam label readable (mis. "3 bulan 12 hari" / "1 tahun 2 bulan") */
    public function getUmurLabelAttribute(): ?string
    {
        if (! $this->tanggal_lahir) return null;
        $days = (int) $this->tanggal_lahir->diffInDays(now());
        if ($days < 30)  return $days . ' hari';
        if ($days < 365) {
            $months = (int) floor($days / 30);
            $remDays = $days - ($months * 30);
            return $months . ' bulan' . ($remDays > 0 ? ' ' . $remDays . ' hari' : '');
        }
        $years = (int) floor($days / 365);
        $remDays = $days - ($years * 365);
        $months = (int) floor($remDays / 30);
        return $years . ' tahun' . ($months > 0 ? ' ' . $months . ' bulan' : '');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) return $q;
        $like = '%' . $term . '%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('no_kartu_bayi', 'ilike', $like)
               ->orWhere('nama_bayi', 'ilike', $like)
               ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'ilike', $like)->orWhere('no_rm', 'ilike', $like));
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status]['label'] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::statuses()[$this->status]['color'] ?? 'secondary';
    }

    public function getUmurHariAttribute(): ?int
    {
        if (! $this->tanggal_lahir) return null;
        return (int) $this->tanggal_lahir->diffInDays(now());
    }
}
