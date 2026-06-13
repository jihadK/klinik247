<?php

namespace App\Http\Requests\Anc;

use App\Models\Pregnancy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePregnancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('anc.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id'       => ['required', 'integer', Rule::exists('tbm_patients', 'id')],
            'patient_visit_id' => ['nullable', 'integer', Rule::exists('tbr_patient_visits', 'id')],

            'gravida'  => ['required', 'integer', 'min:1', 'max:30'],
            'partus'   => ['required', 'integer', 'min:0', 'max:30'],
            'abortus'  => ['required', 'integer', 'min:0', 'max:30'],
            'hamil_ke' => ['nullable', 'integer', 'min:1', 'max:30'],

            'tanggal_k1'        => ['required', 'date'],
            'hpht'              => ['nullable', 'date', 'before_or_equal:tanggal_k1'],
            'hpl'               => ['nullable', 'date', 'after:hpht'],
            'tinggi_badan_cm'   => ['nullable', 'numeric', 'min:100', 'max:250'],
            'berat_badan_awal'  => ['nullable', 'numeric', 'min:30', 'max:200'],
            'lila_cm'           => ['nullable', 'numeric', 'min:10', 'max:60'],
            'imt'               => ['nullable', 'numeric', 'min:10', 'max:80'],
            'recom_kenaikan_bb' => ['nullable', 'string', 'max:50'],
            'vital_sign_td'     => ['nullable', 'string', 'max:20'],

            'riwayat_alergi'    => ['nullable', 'string', 'max:500'],
            'riwayat_penyakit'  => ['nullable', 'string', 'max:500'],
            'keluhan_awal'      => ['nullable', 'string', 'max:500'],

            'suami_nama'           => ['nullable', 'string', 'max:150'],
            'suami_umur'           => ['nullable', 'integer', 'min:10', 'max:120'],
            'suami_pendidikan_id'  => ['nullable', 'integer', Rule::exists('tbm_education_levels', 'id')],
            'suami_pekerjaan'      => ['nullable', 'string', 'max:100'],

            'notes' => ['nullable', 'string', 'max:1000'],

            // ===== K1 = Kunjungan ANC pertama → field ini akan auto-create anc_visit #1 =====
            'tindakan_k1'          => ['nullable', 'string', 'max:500'],
            'penatalaksanaan_k1'   => ['nullable', 'string', 'max:500'],
            'tanggal_kontrol_k1'   => ['nullable', 'date', 'after:tanggal_k1'],
            'tempat_periksa_k1'    => ['nullable', Rule::in(array_keys(\App\Models\AncVisit::tempatPeriksaOptions()))],
            'status_tt_k1'         => ['nullable', Rule::in(\App\Models\AncVisit::statusTtOptions())],
            'pemberian_tt_k1'      => ['nullable', 'boolean'],

            // Pemeriksaan Obstetri K1 (optional, tergantung UK saat K1)
            'tfu_k1'               => ['nullable', 'numeric', 'min:0', 'max:50'],
            'djj_k1'               => ['nullable', 'integer', 'min:60', 'max:220'],
            'letak_janin_k1'       => ['nullable', Rule::in(array_keys(\App\Models\AncVisit::letakOptions()))],
            'map_k1'               => ['nullable', 'numeric', 'min:0', 'max:200'],

            // ===== Riwayat per anak sebelumnya (array, opsional) =====
            'histories'                  => ['nullable', 'array', 'max:30'],
            'histories.*.hamil_ke'       => ['required_with:histories', 'integer', 'min:1'],
            'histories.*.tahun'          => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y')+1)],
            'histories.*.jenis_kelamin'  => ['nullable', Rule::in(['L','P'])],
            'histories.*.cara_lahir'     => ['nullable', Rule::in(array_keys(\App\Models\PregnancyHistory::caraLahirOptions()))],
            'histories.*.bb_lahir_gram'  => ['nullable', 'integer', 'min:200', 'max:10000'],
            'histories.*.pb_lahir_cm'    => ['nullable', 'numeric', 'min:20', 'max:80'],
            'histories.*.tempat_bersalin'=> ['nullable', Rule::in(array_keys(\App\Models\PregnancyHistory::tempatBersalinOptions()))],
            'histories.*.penolong'       => ['nullable', Rule::in(array_keys(\App\Models\PregnancyHistory::penolongOptions()))],
            'histories.*.kondisi_anak'   => ['nullable', Rule::in(array_keys(\App\Models\PregnancyHistory::kondisiAnakOptions()))],
            'histories.*.komplikasi'     => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Pasien wajib dipilih.',
            'gravida.required'    => 'Gravida (jumlah kehamilan) wajib diisi.',
            'hpht.before_or_equal'=> 'HPHT tidak boleh setelah tanggal K1.',
            'hpl.after'           => 'HPL harus setelah HPHT.',
        ];
    }
}
