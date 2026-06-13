<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KbAcceptor extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_kb_acceptors';
    const DELETED_AT = 'deleted_date';

    public const STATUS_AKTIF        = 'aktif';
    public const STATUS_DROP         = 'drop';
    public const STATUS_GANTI_METODE = 'ganti_metode';
    public const STATUS_SELESAI      = 'selesai';

    public static function statuses(): array
    {
        return [
            self::STATUS_AKTIF        => ['label' => 'Aktif',         'color' => 'success'],
            self::STATUS_GANTI_METODE => ['label' => 'Ganti Metode',  'color' => 'warning'],
            self::STATUS_DROP         => ['label' => 'Drop Out',      'color' => 'danger'],
            self::STATUS_SELESAI      => ['label' => 'Selesai',       'color' => 'secondary'],
        ];
    }

    protected $fillable = [
        'site_id', 'patient_id', 'patient_visit_id', 'previous_acceptor_id',
        'no_kartu_kb', 'kontrasepsi_id',
        'akseptor_kawin_ke',
        'suami_name', 'suami_age', 'suami_education_id', 'suami_kawin_ke', 'suami_occupation',
        // Status peserta baru
        'jumlah_anak_hidup', 'keinginan_punya_anak_lagi', 'kapan_ingin_anak_lagi',
        'status_kehamilan_saat_ini', 'riwayat_komplikasi_kehamilan',
        'sikap_pasangan_terhadap_kb', 'edukasi_hiv_aids_pms', 'metode_ganda_pakai_kondom',
        // Pemeriksaan
        'tekanan_darah', 'berat_badan', 'haid_terakhir', 'kebiasaan_merokok',
        'sedang_menyusui', 'tanggal_persalinan_terakhir',
        'sakit_kuning', 'perdarahan_per_vaginam', 'tumor_payudara',
        'keluhan', 'fluoralbus_gatal', 'fluoralbus_seperti_susu', 'fluoralbus_busa', 'fluoralbus_cair',
        // IUD
        'iud_tanda_radang', 'iud_tumor', 'iud_posisi_rahim',
        'iud_genetalia_varices', 'iud_genetalia_jengger', 'iud_genetalia_condilo', 'iud_genetalia_bartholinitis',
        // Tanggal & status
        'tanggal_dilayani', 'tanggal_pesan_kontrol', 'tanggal_dilepas',
        'status', 'drop_reason',
        // Consent
        'consent_signed', 'consent_signed_at', 'consent_witness',
        'notes', 'created_by', 'served_by',
    ];

    protected $casts = [
        'haid_terakhir'              => 'date',
        'tanggal_persalinan_terakhir'=> 'date',
        'tanggal_dilayani'           => 'date',
        'tanggal_pesan_kontrol'      => 'date',
        'tanggal_dilepas'            => 'date',
        'consent_signed_at'          => 'datetime',
        'berat_badan'                => 'decimal:1',
        'edukasi_hiv_aids_pms'       => 'boolean',
        'metode_ganda_pakai_kondom'  => 'boolean',
        'kebiasaan_merokok'          => 'boolean',
        'sedang_menyusui'            => 'boolean',
        'sakit_kuning'               => 'boolean',
        'perdarahan_per_vaginam'     => 'boolean',
        'tumor_payudara'             => 'boolean',
        'fluoralbus_gatal'           => 'boolean',
        'fluoralbus_seperti_susu'    => 'boolean',
        'fluoralbus_busa'            => 'boolean',
        'fluoralbus_cair'            => 'boolean',
        'iud_tanda_radang'           => 'boolean',
        'iud_tumor'                  => 'boolean',
        'iud_genetalia_varices'      => 'boolean',
        'iud_genetalia_jengger'      => 'boolean',
        'iud_genetalia_condilo'      => 'boolean',
        'iud_genetalia_bartholinitis'=> 'boolean',
        'consent_signed'             => 'boolean',
    ];

    /* ========== Relations ========== */
    public function site(): BelongsTo         { return $this->belongsTo(Site::class, 'site_id'); }
    public function patient(): BelongsTo      { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function visit(): BelongsTo        { return $this->belongsTo(PatientVisit::class, 'patient_visit_id'); }
    public function kontrasepsi(): BelongsTo  { return $this->belongsTo(KontrasepsiMethod::class, 'kontrasepsi_id'); }
    public function suamiEducation(): BelongsTo { return $this->belongsTo(EducationLevel::class, 'suami_education_id'); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function servedBy(): BelongsTo     { return $this->belongsTo(User::class, 'served_by'); }

    public function visits(): HasMany
    {
        return $this->hasMany(KbVisit::class, 'acceptor_id')->orderByDesc('visit_date');
    }

    public function previousAcceptor(): BelongsTo
    {
        return $this->belongsTo(KbAcceptor::class, 'previous_acceptor_id');
    }

    public function nextAcceptor(): HasMany
    {
        return $this->hasMany(KbAcceptor::class, 'previous_acceptor_id');
    }

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
            $qq->where('no_kartu_kb', 'ilike', $like)
               ->orWhere('suami_name', 'ilike', $like)
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

    public function getIsIudAttribute(): bool
    {
        return optional($this->kontrasepsi)->code === 'KTR-IUD';
    }
}
