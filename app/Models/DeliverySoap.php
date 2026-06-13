<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySoap extends BaseModel
{
    protected $table = 'tbr_delivery_soap';

    public $timestamps = true;
    const UPDATED_AT = null;
    const CREATED_AT = 'created_date';

    public static function kalaOptions(): array
    {
        return [
            'masuk'    => 'Masuk PMB',
            'kala_i'   => 'Kala I (Fase Aktif)',
            'kala_ii'  => 'Kala II (Bayi Lahir)',
            'kala_iii' => 'Kala III (Plasenta)',
            'kala_iv'  => 'Kala IV (Observasi 2 jam)',
        ];
    }

    public static function ketubanOptions(): array
    {
        return ['utuh' => 'Utuh', 'jernih' => 'Jernih', 'mekonial' => 'Mekonial', 'keruh' => 'Keruh'];
    }

    protected $fillable = [
        'site_id', 'delivery_id',
        'observed_at', 'kala',
        'subjective',
        'ttv_td', 'ttv_nadi', 'ttv_suhu', 'ttv_rr',
        'djj', 'his_per_10', 'his_durasi',
        'vt_pembukaan', 'vt_penurunan', 'ketuban',
        'hb_gr_dl', 'alb',
        'assessment', 'plan', 'notes',
        'created_by',
    ];

    protected $casts = [
        'observed_at'  => 'datetime',
        'ttv_suhu'     => 'decimal:1',
        'vt_pembukaan' => 'decimal:1',
        'hb_gr_dl'     => 'decimal:1',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getKalaLabelAttribute(): string
    {
        return self::kalaOptions()[$this->kala] ?? '-';
    }
}
