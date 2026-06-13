<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostnatalVisit extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_postnatal_visits';
    const DELETED_AT = 'deleted_date';

    public static function kfPeriods(): array
    {
        return [
            1 => ['label' => 'KF 1', 'periode' => '6 jam — 2 hari',  'color' => 'danger',  'days_min' => 0,  'days_max' => 2],
            2 => ['label' => 'KF 2', 'periode' => '3 — 7 hari',      'color' => 'warning', 'days_min' => 3,  'days_max' => 7],
            3 => ['label' => 'KF 3', 'periode' => '8 — 28 hari',     'color' => 'primary', 'days_min' => 8,  'days_max' => 28],
            4 => ['label' => 'KF 4', 'periode' => '29 — 42 hari',    'color' => 'success', 'days_min' => 29, 'days_max' => 42],
        ];
    }

    /** 10 Nasehat Pasca Salin (sesuai Kartu Bu Tin) */
    public static function nasehatItems(): array
    {
        return [
            'gizi'           => '🥗 Makan gizi seimbang, aneka ragam',
            'minum'          => '💧 Minum 12-14 gelas/hari',
            'kebersihan'     => '🧼 Jaga kebersihan diri & bayi',
            'istirahat'      => '😴 Istirahat cukup',
            'aktivitas'      => '🚶 Aktivitas fisik 3-5×/minggu, 30 menit',
            'luka_sc'        => '🩹 Jaga luka SC, latihan fisik 3 bulan pasca salin',
            'menyusui'       => '🤱 Cara menyusui benar, ASI saja sampai 6 bulan',
            'perawatan_bayi' => '👶 Perawatan bayi yang benar',
            'bayi_stres'     => '🚫 Jangan biarkan bayi menangis lama (stres)',
            'stimulasi'      => '🗣 Stimulasi komunikasi bayi sejak dini',
            'kb'             => '💊 Konsultasi KB pasca salin',
        ];
    }

    public static function lokhiaOptions(): array
    {
        return [
            'rubra'         => 'Rubra (merah segar, hari 1-3)',
            'sanguinolenta' => 'Sanguinolenta (merah kecoklatan, hari 4-7)',
            'serosa'        => 'Serosa (kuning kecoklatan, hari 8-14)',
            'alba'          => 'Alba (putih kekuningan, hari 15-42)',
            'kering'        => 'Kering / tidak ada',
        ];
    }

    public static function statusOptions(): array
    {
        return ['sehat' => 'Sehat', 'sakit' => 'Sakit', 'dirujuk' => 'Dirujuk'];
    }

    protected $fillable = [
        'site_id', 'delivery_id', 'pregnancy_id', 'patient_id',
        'kf_number', 'visit_date', 'visit_time',
        'ttv_td', 'ttv_nadi', 'ttv_suhu', 'ttv_rr',
        'kondisi_umum',
        'lokhia', 'lokhia_jumlah', 'lokhia_bau',
        'jalan_lahir', 'tanda_infeksi',
        'kontraksi', 'tfu_cm',
        'payudara', 'asi', 'vit_a_dose',
        'eliminasi_bak', 'eliminasi_bab',
        'keluhan', 'komplikasi', 'tindakan', 'terapi',
        'nasehat_diberikan',
        'kb_dikonseling', 'kb_rencana',
        'tanggal_kembali', 'rujukan', 'rujukan_alasan',
        'status', 'notes',
        'served_by', 'created_by',
    ];

    protected $casts = [
        'visit_date'        => 'date',
        'visit_time'        => 'datetime',
        'tanggal_kembali'   => 'date',
        'ttv_suhu'          => 'decimal:1',
        'tfu_cm'            => 'decimal:1',
        'nasehat_diberikan' => 'array',
        'tanda_infeksi'     => 'boolean',
        'kb_dikonseling'    => 'boolean',
        'rujukan'           => 'boolean',
    ];

    public function site(): BelongsTo       { return $this->belongsTo(Site::class, 'site_id'); }
    public function delivery(): BelongsTo   { return $this->belongsTo(Delivery::class, 'delivery_id'); }
    public function pregnancy(): BelongsTo  { return $this->belongsTo(Pregnancy::class, 'pregnancy_id'); }
    public function patient(): BelongsTo    { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function servedBy(): BelongsTo   { return $this->belongsTo(User::class, 'served_by'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
}
