<?php

namespace App\Http\Requests\Kb;

use App\Models\KbAcceptor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKbAcceptorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('kb.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id'        => ['required', 'integer', Rule::exists('tbm_patients', 'id')],
            'patient_visit_id'  => ['nullable', 'integer', Rule::exists('tbr_patient_visits', 'id')],
            'kontrasepsi_id'    => ['required', 'integer', Rule::exists('tbm_kontrasepsi_methods', 'id')],

            'akseptor_kawin_ke' => ['nullable', 'integer', 'min:1', 'max:20'],
            'suami_name'        => ['nullable', 'string', 'max:150'],
            'suami_age'         => ['nullable', 'integer', 'min:10', 'max:120'],
            'suami_education_id'=> ['nullable', 'integer', Rule::exists('tbm_education_levels', 'id')],
            'suami_kawin_ke'    => ['nullable', 'integer', 'min:1', 'max:20'],
            'suami_occupation'  => ['nullable', 'string', 'max:100'],

            'jumlah_anak_hidup'           => ['nullable', 'integer', 'min:0', 'max:30'],
            'keinginan_punya_anak_lagi'   => ['nullable', Rule::in(['ya','tidak','tidak_tahu'])],
            'kapan_ingin_anak_lagi'       => ['nullable', 'string', 'max:100'],
            'status_kehamilan_saat_ini'   => ['nullable', Rule::in(['hamil','tidak_hamil','tidak_tahu'])],
            'riwayat_komplikasi_kehamilan'=> ['nullable', 'string', 'max:500'],
            'sikap_pasangan_terhadap_kb'  => ['nullable', Rule::in(['setuju','tidak_setuju','netral'])],
            'edukasi_hiv_aids_pms'        => ['nullable', 'boolean'],
            'metode_ganda_pakai_kondom'   => ['nullable', 'boolean'],

            'tekanan_darah'              => ['nullable', 'string', 'max:20'],
            'berat_badan'                => ['nullable', 'numeric', 'min:0', 'max:300'],
            'haid_terakhir'              => ['nullable', 'date'],
            'kebiasaan_merokok'          => ['nullable', 'boolean'],
            'sedang_menyusui'            => ['nullable', 'boolean'],
            'tanggal_persalinan_terakhir'=> ['nullable', 'date'],
            'sakit_kuning'               => ['nullable', 'boolean'],
            'perdarahan_per_vaginam'     => ['nullable', 'boolean'],
            'tumor_payudara'             => ['nullable', 'boolean'],
            'keluhan'                    => ['nullable', 'string', 'max:500'],
            'fluoralbus_gatal'           => ['nullable', 'boolean'],
            'fluoralbus_seperti_susu'    => ['nullable', 'boolean'],
            'fluoralbus_busa'            => ['nullable', 'boolean'],
            'fluoralbus_cair'            => ['nullable', 'boolean'],

            'iud_tanda_radang'            => ['nullable', 'boolean'],
            'iud_tumor'                   => ['nullable', 'boolean'],
            'iud_posisi_rahim'            => ['nullable', Rule::in(['retro','antefleksi','normal'])],
            'iud_genetalia_varices'       => ['nullable', 'boolean'],
            'iud_genetalia_jengger'       => ['nullable', 'boolean'],
            'iud_genetalia_condilo'       => ['nullable', 'boolean'],
            'iud_genetalia_bartholinitis' => ['nullable', 'boolean'],

            'tanggal_dilayani'      => ['required', 'date'],
            'tanggal_pesan_kontrol' => ['nullable', 'date', 'after_or_equal:tanggal_dilayani'],

            'consent_signed'  => ['nullable', 'boolean'],
            'consent_witness' => ['nullable', 'string', 'max:150'],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function prepareForValidation(): void
    {
        $boolFields = [
            'edukasi_hiv_aids_pms','metode_ganda_pakai_kondom','kebiasaan_merokok','sedang_menyusui',
            'sakit_kuning','perdarahan_per_vaginam','tumor_payudara',
            'fluoralbus_gatal','fluoralbus_seperti_susu','fluoralbus_busa','fluoralbus_cair',
            'iud_tanda_radang','iud_tumor',
            'iud_genetalia_varices','iud_genetalia_jengger','iud_genetalia_condilo','iud_genetalia_bartholinitis',
            'consent_signed',
        ];
        $data = [];
        foreach ($boolFields as $f) $data[$f] = $this->boolean($f);
        $this->merge($data);
    }
}
