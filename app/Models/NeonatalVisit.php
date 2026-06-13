<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NeonatalVisit extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_neonatal_visits';
    const DELETED_AT = 'deleted_date';

    public static function knPeriods(): array
    {
        return [
            1 => ['label' => 'KN 1', 'periode' => '6 jam — 2 hari',  'color' => 'danger',  'days_min' => 0,  'days_max' => 2],
            2 => ['label' => 'KN 2', 'periode' => '3 — 7 hari',      'color' => 'warning', 'days_min' => 3,  'days_max' => 7],
            3 => ['label' => 'KN 3', 'periode' => '8 — 28 hari',     'color' => 'primary', 'days_min' => 8,  'days_max' => 28],
        ];
    }

    public static function taliPusatOptions(): array
    {
        return [
            'basah'    => 'Basah (normal hari 1-2)',
            'kering'   => 'Kering (mau lepas)',
            'lepas'    => 'Sudah Lepas',
            'infeksi'  => 'Infeksi (kemerahan/nanah)',
        ];
    }

    public static function menyusuOptions(): array
    {
        return [
            'lancar'   => 'Lancar',
            'kurang'   => 'Kurang',
            'tidak'    => 'Tidak menyusu',
        ];
    }

    /** Tanda bahaya bayi baru lahir (Kemenkes) */
    public static function tandaBahayaItems(): array
    {
        return [
            'kejang'         => 'Kejang',
            'sesak'          => 'Sesak nafas',
            'malas_minum'    => 'Malas minum / tidak mau menyusu',
            'dingin'         => 'Suhu rendah / dingin',
            'panas'          => 'Demam / suhu tinggi',
            'mata_merah'     => 'Mata merah / bernanah',
            'kulit_kuning'   => 'Kulit kuning (ikterus berat)',
            'pusar_infeksi'  => 'Tali pusat berbau/bernanah',
            'muntah'         => 'Muntah terus-menerus',
            'diare'          => 'Diare / BAB cair berulang',
        ];
    }

    protected $fillable = [
        'site_id', 'neonate_id', 'patient_id',
        'kn_number', 'visit_date', 'visit_time',
        'berat_badan_gram', 'panjang_badan_cm', 'lingkar_kepala_cm', 'suhu_celcius',
        'tali_pusat', 'menyusu', 'ikterus_level',
        'tanda_bahaya', 'masalah_lain', 'tindakan', 'terapi',
        'dirujuk', 'rujukan_alasan',
        'tanggal_kembali', 'notes',
        'served_by', 'created_by',
    ];

    protected $casts = [
        'visit_date'        => 'date',
        'visit_time'        => 'datetime',
        'tanggal_kembali'   => 'date',
        'panjang_badan_cm'  => 'decimal:1',
        'lingkar_kepala_cm' => 'decimal:1',
        'suhu_celcius'      => 'decimal:1',
        'tanda_bahaya'      => 'array',
        'dirujuk'           => 'boolean',
    ];

    public function neonate(): BelongsTo { return $this->belongsTo(Neonate::class, 'neonate_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function servedBy(): BelongsTo { return $this->belongsTo(User::class, 'served_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
