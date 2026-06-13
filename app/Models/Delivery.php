<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_deliveries';
    const DELETED_AT = 'deleted_date';

    public const STATUS_MASUK    = 'masuk';
    public const STATUS_INPARTU  = 'inpartu';
    public const STATUS_KALA_II  = 'kala_ii';
    public const STATUS_KALA_III = 'kala_iii';
    public const STATUS_KALA_IV  = 'kala_iv';
    public const STATUS_SELESAI  = 'selesai';
    public const STATUS_RUJUK    = 'rujuk';

    public static function statuses(): array
    {
        return [
            self::STATUS_MASUK    => ['label' => 'Masuk PMB',         'color' => 'info',    'icon' => 'ki-arrow-right'],
            self::STATUS_INPARTU  => ['label' => 'Inpartu / Kala I',  'color' => 'primary', 'icon' => 'ki-pulse'],
            self::STATUS_KALA_II  => ['label' => 'Kala II (Mengejan)', 'color' => 'warning','icon' => 'ki-baby'],
            self::STATUS_KALA_III => ['label' => 'Kala III (Plasenta)','color' => 'warning','icon' => 'ki-tablet'],
            self::STATUS_KALA_IV  => ['label' => 'Kala IV (Observasi)','color' => 'info',   'icon' => 'ki-time'],
            self::STATUS_SELESAI  => ['label' => 'Selesai',           'color' => 'success', 'icon' => 'ki-check-circle'],
            self::STATUS_RUJUK    => ['label' => 'Dirujuk',           'color' => 'danger',  'icon' => 'ki-arrow-right-square'],
        ];
    }

    /** 18 item Penapisan */
    public static function penapisanItems(): array
    {
        return [
            'p_riwayat_sc'              => 'Riwayat SC (Sectio Caesarea)',
            'p_pendarahan_pervaginam'   => 'Perdarahan pervaginam',
            'p_kehamilan_kurang_bulan'  => 'Kehamilan kurang bulan (UK < 37 mg)',
            'p_ketuban_mekonial'        => 'Ketuban pecah dengan mekonial kental',
            'p_ketuban_lama'            => 'Ketuban pecah lama (> 24 jam)',
            'p_ketuban_kurang_bulan'    => 'Ketuban pecah pada kehamilan kurang bulan',
            'p_ikterus'                 => 'Ikterus (jaundice)',
            'p_anemia_berat'            => 'Anemia berat (Hb < 7)',
            'p_pre_eklampsi_berat'      => 'Pre Eklampsi berat',
            'p_tfu_40'                  => 'TFU > 40 cm (Makrosomia / Kembar)',
            'p_demam'                   => 'Demam (> 38°C)',
            'p_gawat_janin'             => 'Gawat janin (DJJ <100 atau >180)',
            'p_presentasi_bukan_kepala' => 'Presentasi bukan belakang kepala',
            'p_tali_pusat_menumbung'    => 'Tali pusat menumbung',
            'p_gi_fase_aktif'           => 'GI fase aktif penurunan kepala 5/5',
            'p_letak_majemuk'           => 'Letak majemuk',
            'p_gemelli'                 => 'Gemelli (kehamilan kembar)',
            'p_syok'                    => 'Syok',
        ];
    }

    public static function ketubanOptions(): array
    {
        return ['utuh' => 'Utuh', 'jernih' => 'Jernih', 'mekonial' => 'Mekonial', 'keruh' => 'Keruh'];
    }

    public static function laserasiOptions(): array
    {
        return [
            'none'       => 'Tidak ada',
            'derajat_1'  => 'Derajat I (kulit & mukosa)',
            'derajat_2'  => 'Derajat II (otot perineum)',
            'derajat_3'  => 'Derajat III (sfingter ani)',
            'derajat_4'  => 'Derajat IV (mukosa rektum)',
        ];
    }

    public static function ibuKondisiOptions(): array
    {
        return ['sehat' => 'Sehat', 'sakit' => 'Sakit', 'rujuk' => 'Dirujuk', 'meninggal' => 'Meninggal'];
    }

    public static function bayiKondisiOptions(): array
    {
        return [
            'hidup_sehat' => 'Hidup Sehat',
            'hidup_sakit' => 'Hidup Sakit',
            'lahir_mati'  => 'Lahir Mati',
            'meninggal'   => 'Meninggal Setelah Lahir',
        ];
    }

    public static function keputusanPenapisanOptions(): array
    {
        return ['lanjut' => 'Lanjut Persalinan', 'observasi' => 'Observasi Dulu', 'rujuk' => 'RUJUK'];
    }

    public static function rujukSiklusStatuses(): array
    {
        return [
            'belum_kirim'   => ['label' => 'Belum Dikirim',    'color' => 'secondary', 'icon' => 'ki-time'],
            'dikirim'       => ['label' => 'Dikirim ke RS',    'color' => 'info',      'icon' => 'ki-arrow-right'],
            'diterima_rs'   => ['label' => 'Diterima RS',      'color' => 'primary',   'icon' => 'ki-check'],
            'ada_balasan'   => ['label' => 'Ada Balasan RS',   'color' => 'warning',   'icon' => 'ki-message-text'],
            'selesai'       => ['label' => 'Selesai',          'color' => 'success',   'icon' => 'ki-check-circle'],
            'batal'         => ['label' => 'Batal',            'color' => 'danger',    'icon' => 'ki-cross-circle'],
        ];
    }

    public static function transportOptions(): array
    {
        return [
            'ambulans'      => '🚑 Ambulans',
            'mobil_pribadi' => '🚗 Mobil Pribadi',
            'motor'         => '🏍 Motor',
            'taksi_online'  => '🚕 Taksi/Online',
            'lainnya'       => 'Lainnya',
        ];
    }

    protected $fillable = [
        'site_id', 'pregnancy_id', 'patient_id', 'patient_visit_id',
        'no_persalinan', 'visit_date', 'masuk_at',
        // Penapisan
        'p_riwayat_sc', 'p_pendarahan_pervaginam', 'p_kehamilan_kurang_bulan',
        'p_ketuban_mekonial', 'p_ketuban_lama', 'p_ketuban_kurang_bulan',
        'p_ikterus', 'p_anemia_berat', 'p_pre_eklampsi_berat',
        'p_tfu_40', 'p_demam', 'p_gawat_janin',
        'p_presentasi_bukan_kepala', 'p_tali_pusat_menumbung', 'p_gi_fase_aktif',
        'p_letak_majemuk', 'p_gemelli', 'p_syok',
        'penapisan_skor', 'penapisan_keputusan',
        // Masuk PMB
        'masuk_ttv_td', 'masuk_ttv_nadi', 'masuk_ttv_suhu', 'masuk_ttv_rr',
        'masuk_djj', 'masuk_his_per_10', 'masuk_vt_pembukaan', 'masuk_ketuban', 'masuk_keluhan',
        // Kala I-IV
        'kala1_mulai_at', 'kala1_selesai_at', 'kala1_keterangan',
        'kala2_mulai_at', 'bayi_lahir_at', 'bayi_jenis_kelamin', 'bayi_bb_gram', 'bayi_pb_cm',
        'bayi_lahir_spontan', 'bayi_lgs_menangis', 'bayi_apgar_1', 'bayi_apgar_5',
        'kala3_mulai_at', 'plasenta_lahir_at', 'plasenta_lahir_spontan', 'mak_iii_dilakukan',
        'amniotomi', 'tfu_sepusat', 'uc_kuat', 'eksplorasi_dilakukan', 'sisa_plasenta', 'selaput_lengkap',
        'kala4_mulai_at', 'kala4_selesai_at', 'perineum_laserasi', 'heckting_dilakukan',
        'heckting_lidocain', 'perdarahan_ml', 'kala4_keluhan',
        // Outcome
        'status', 'ibu_kondisi', 'bayi_kondisi', 'rujukan_ke', 'rujukan_alasan',
        // Siklus Rujukan
        'rujuk_dikirim_at', 'rujuk_transport', 'rujuk_pendamping', 'rujuk_bawa', 'rujuk_kontak_rs',
        'rujuk_diterima_at', 'rujuk_diterima_by',
        'rujuk_balik_no', 'rujuk_balik_diterima_at',
        'rujuk_balik_diagnosis', 'rujuk_balik_tindakan',
        'rujuk_balik_outcome_ibu', 'rujuk_balik_outcome_bayi',
        'rujuk_balik_rekomendasi', 'rujuk_balik_dokter_rs',
        'rujuk_siklus_status',
        // Terapi
        'terapi_amoxicillin', 'terapi_asam_mef', 'terapi_fe', 'terapi_metergin',
        'terapi_vita_dose1_at', 'terapi_vita_dose2_at', 'terapi_ibu_dosis_notes',
        'bayi_injeksi_neo_k', 'bayi_neo_k_at', 'bayi_salep_mata',
        'bayi_imunisasi_hb0', 'bayi_hb0_at', 'bayi_hb0_no_batch',
        'notes', 'created_by', 'served_by',
    ];

    protected $casts = [
        'visit_date'          => 'date',
        'masuk_at'            => 'datetime',
        'kala1_mulai_at'      => 'datetime',
        'kala1_selesai_at'    => 'datetime',
        'kala2_mulai_at'      => 'datetime',
        'bayi_lahir_at'       => 'datetime',
        'kala3_mulai_at'      => 'datetime',
        'plasenta_lahir_at'   => 'datetime',
        'kala4_mulai_at'      => 'datetime',
        'kala4_selesai_at'    => 'datetime',
        'terapi_vita_dose1_at'=> 'datetime',
        'terapi_vita_dose2_at'=> 'datetime',
        'bayi_neo_k_at'       => 'datetime',
        'bayi_hb0_at'         => 'datetime',
        'rujuk_dikirim_at'        => 'datetime',
        'rujuk_diterima_at'       => 'datetime',
        'rujuk_balik_diterima_at' => 'datetime',
        'masuk_ttv_suhu'      => 'decimal:1',
        'masuk_vt_pembukaan'  => 'decimal:1',
        'bayi_pb_cm'          => 'decimal:1',
    ];

    protected $booleans = [
        'p_riwayat_sc','p_pendarahan_pervaginam','p_kehamilan_kurang_bulan',
        'p_ketuban_mekonial','p_ketuban_lama','p_ketuban_kurang_bulan',
        'p_ikterus','p_anemia_berat','p_pre_eklampsi_berat',
        'p_tfu_40','p_demam','p_gawat_janin',
        'p_presentasi_bukan_kepala','p_tali_pusat_menumbung','p_gi_fase_aktif',
        'p_letak_majemuk','p_gemelli','p_syok',
        'bayi_lahir_spontan','bayi_lgs_menangis',
        'plasenta_lahir_spontan','mak_iii_dilakukan','amniotomi',
        'tfu_sepusat','uc_kuat','eksplorasi_dilakukan','sisa_plasenta','selaput_lengkap',
        'heckting_dilakukan','heckting_lidocain',
        'terapi_amoxicillin','terapi_asam_mef','terapi_fe','terapi_metergin',
        'bayi_injeksi_neo_k','bayi_salep_mata','bayi_imunisasi_hb0',
    ];

    protected function casts(): array
    {
        $base = $this->casts;
        foreach ($this->booleans as $b) {
            $base[$b] = 'boolean';
        }
        return $base;
    }

    /* ========== Relations ========== */
    public function site(): BelongsTo      { return $this->belongsTo(Site::class, 'site_id'); }
    public function patient(): BelongsTo   { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function pregnancy(): BelongsTo { return $this->belongsTo(Pregnancy::class, 'pregnancy_id'); }
    public function visit(): BelongsTo     { return $this->belongsTo(PatientVisit::class, 'patient_visit_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function servedBy(): BelongsTo  { return $this->belongsTo(User::class, 'served_by'); }

    public function soaps(): HasMany
    {
        return $this->hasMany(DeliverySoap::class, 'delivery_id')->orderBy('observed_at');
    }

    /* ========== Scopes ========== */
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNotIn('status', [self::STATUS_SELESAI, self::STATUS_RUJUK]);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) return $q;
        $like = '%' . $term . '%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('no_persalinan', 'ilike', $like)
               ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'ilike', $like)->orWhere('no_rm', 'ilike', $like));
        });
    }

    /* ========== Accessors & Helpers ========== */
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status]['label'] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::statuses()[$this->status]['color'] ?? 'secondary';
    }

    /** Hitung skor penapisan (jumlah YA) — dipakai sebelum save */
    public function calculatePenapisanSkor(): int
    {
        $items = array_keys(self::penapisanItems());
        $count = 0;
        foreach ($items as $key) {
            if ($this->{$key}) $count++;
        }
        return $count;
    }

    /** Lama Kala I dalam jam */
    public function getKala1DurationAttribute(): ?float
    {
        if (! $this->kala1_mulai_at || ! $this->kala1_selesai_at) return null;
        return round($this->kala1_mulai_at->diffInMinutes($this->kala1_selesai_at) / 60, 1);
    }

    /** Total lama persalinan (masuk → kala IV selesai) */
    public function getTotalDurationAttribute(): ?float
    {
        if (! $this->masuk_at || ! $this->kala4_selesai_at) return null;
        return round($this->masuk_at->diffInMinutes($this->kala4_selesai_at) / 60, 1);
    }
}
