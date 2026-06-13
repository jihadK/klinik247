<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbm_patients';
    const DELETED_AT = 'deleted_date';

    protected $fillable = [
        'site_id',
        // Identitas Utama
        'no_rm', 'cm_lama',
        // KTP & KK
        'nik', 'no_kk', 'nama_kk', 'no_bpjs',
        // Pribadi
        'name', 'birth_place', 'birth_date', 'gender',
        // Sosial-Demografi
        'payer_type_id', 'education_id', 'religion_id', 'occupation',
        'marital_status', 'blood_type',
        // Alamat
        'province_code', 'regency_code', 'district_code', 'village_code',
        'address', 'rt_rw', 'postal_code', 'wilayah_type',
        // Kontak
        'phone', 'email',
        // Medis
        'allergies', 'chronic_diseases', 'medical_history',
        // Emergency
        'emergency_contact', 'emergency_phone', 'emergency_relation',
        // Meta
        'photo_url', 'is_active', 'notes', 'created_by',
    ];

    protected $casts = [
        'birth_date'    => 'date',
        'is_active'     => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /* ========== Relations ========== */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function payerType(): BelongsTo
    {
        return $this->belongsTo(PayerType::class, 'payer_type_id');
    }

    public function education(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_id');
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_code', 'code');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ========== Scopes ========== */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) return $q;
        $like = '%' . $term . '%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('no_rm', 'ilike', $like)
               ->orWhere('cm_lama', 'ilike', $like)
               ->orWhere('nik', 'ilike', $like)
               ->orWhere('no_kk', 'ilike', $like)
               ->orWhere('no_bpjs', 'ilike', $like)
               ->orWhere('name', 'ilike', $like)
               ->orWhere('nama_kk', 'ilike', $like)
               ->orWhere('phone', 'ilike', $like);
        });
    }

    /* ========== Accessors ========== */
    public function getAgeAttribute(): ?string
    {
        if (! $this->birth_date) return null;
        $now = now();
        $bd  = $this->birth_date;
        $y   = (int) floor($bd->diffInYears($now));
        $m   = (int) floor($bd->copy()->addYears($y)->diffInMonths($now));
        return $y . ' th ' . $m . ' bln';
    }

    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'L' ? 'Laki-laki' : ($this->gender === 'P' ? 'Perempuan' : '-');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->rt_rw ? "RT/RW {$this->rt_rw}" : null,
            optional($this->village)->name ? "Ds. " . $this->village->name : null,
            optional($this->district)->name ? "Kec. " . $this->district->name : null,
            optional($this->regency)->name,
            optional($this->province)->name,
            $this->postal_code,
        ]);
        return implode(', ', $parts);
    }
}
