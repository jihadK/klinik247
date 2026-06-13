<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PregnancyHistory extends BaseModel
{
    protected $table = 'tbr_pregnancy_histories';

    protected $fillable = [
        'site_id', 'pregnancy_id',
        'hamil_ke', 'tahun', 'jenis_kelamin', 'cara_lahir',
        'bb_lahir_gram', 'pb_lahir_cm', 'tempat_bersalin', 'penolong',
        'kondisi_anak', 'komplikasi',
    ];

    protected $casts = [
        'hamil_ke'      => 'integer',
        'tahun'         => 'integer',
        'bb_lahir_gram' => 'integer',
        'pb_lahir_cm'   => 'decimal:1',
    ];

    public function pregnancy(): BelongsTo { return $this->belongsTo(Pregnancy::class, 'pregnancy_id'); }

    public static function caraLahirOptions(): array
    {
        return [
            'spontan'  => 'Spontan',
            'sc'       => 'Sectio Caesarea (SC)',
            'vakum'    => 'Vakum',
            'forceps'  => 'Forceps',
            'induksi'  => 'Induksi',
            'abortus'  => 'Abortus',
            'lainnya'  => 'Lainnya',
        ];
    }

    public static function kondisiAnakOptions(): array
    {
        return [
            'hidup_sehat' => 'Hidup Sehat',
            'meninggal'   => 'Meninggal',
            'lahir_mati'  => 'Lahir Mati',
            'abortus'     => 'Abortus',
            'sakit'       => 'Sakit/Cacat',
        ];
    }

    public static function tempatBersalinOptions(): array
    {
        return [
            'rumah'         => 'Rumah',
            'polindes'      => 'Polindes',
            'puskesmas'     => 'Puskesmas',
            'klinik'        => 'Klinik / BPM',
            'rs_pemerintah' => 'RS Pemerintah',
            'rs_swasta'     => 'RS Swasta',
            'lainnya'       => 'Lainnya',
        ];
    }

    public static function penolongOptions(): array
    {
        return [
            'bidan'                => 'Bidan',
            'dokter_umum'          => 'Dokter Umum',
            'dokter_spog'          => 'Dokter SpOG',
            'perawat'              => 'Perawat',
            'dukun_terlatih'       => 'Dukun Terlatih',
            'dukun_tidak_terlatih' => 'Dukun Tidak Terlatih',
            'keluarga'             => 'Keluarga / Sendiri',
            'lainnya'              => 'Lainnya',
        ];
    }
}
