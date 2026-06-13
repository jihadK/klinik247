<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChildVisit extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_child_visits';
    const DELETED_AT = 'deleted_date';

    public static function visitTypeOptions(): array
    {
        return [
            'rutin'          => '🟢 Kunjungan Rutin (KMS)',
            'imunisasi'      => '💉 Imunisasi',
            'sakit'          => '🤒 Sakit',
            'kontrol_balik'  => '🔄 Kontrol Balik',
            'lainnya'        => 'Lainnya',
        ];
    }

    public static function statusGiziOptions(): array
    {
        return [
            'gizi_buruk'  => '🔴 Gizi Buruk',
            'gizi_kurang' => '🟡 Gizi Kurang',
            'gizi_baik'   => '🟢 Gizi Baik',
            'gizi_lebih'  => '🟠 Gizi Lebih',
            'obesitas'    => '🔴 Obesitas',
        ];
    }

    public static function perkembanganOptions(): array
    {
        return [
            'sesuai'        => '✅ Sesuai usia',
            'meragukan'     => '⚠ Meragukan (perlu stimulasi)',
            'penyimpangan'  => '🚨 Ada penyimpangan',
        ];
    }

    protected $fillable = [
        'site_id', 'neonate_id', 'patient_id',
        'visit_date', 'visit_time', 'visit_type',
        'umur_hari', 'umur_label',
        'berat_badan_gram', 'panjang_badan_cm', 'lingkar_kepala_cm', 'lingkar_lengan_cm', 'suhu_celcius',
        'status_gizi', 'stunting', 'wasting',
        'perkembangan_status', 'perkembangan_catatan',
        'keluhan', 'diagnosis', 'tindakan', 'terapi',
        'asi_eksklusif', 'pmt',
        'tanggal_kembali', 'rujukan', 'rujukan_alasan',
        'notes',
        'served_by', 'created_by',
    ];

    protected $casts = [
        'visit_date'        => 'date',
        'visit_time'        => 'datetime',
        'tanggal_kembali'   => 'date',
        'panjang_badan_cm'  => 'decimal:1',
        'lingkar_kepala_cm' => 'decimal:1',
        'lingkar_lengan_cm' => 'decimal:1',
        'suhu_celcius'      => 'decimal:1',
        'stunting'          => 'boolean',
        'wasting'           => 'boolean',
        'asi_eksklusif'     => 'boolean',
        'rujukan'           => 'boolean',
    ];

    public function neonate(): BelongsTo { return $this->belongsTo(Neonate::class, 'neonate_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function servedBy(): BelongsTo { return $this->belongsTo(User::class, 'served_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
