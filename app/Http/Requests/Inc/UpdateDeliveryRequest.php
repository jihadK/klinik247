<?php

namespace App\Http\Requests\Inc;

use App\Models\Delivery;
use Illuminate\Validation\Rule;

class UpdateDeliveryRequest extends StoreDeliveryRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('inc.update') ?? false;
    }

    public function rules(): array
    {
        $additional = [
            'status' => ['nullable', Rule::in(array_keys(Delivery::statuses()))],

            // Kala I
            'kala1_mulai_at'      => ['nullable', 'date'],
            'kala1_selesai_at'    => ['nullable', 'date'],
            'kala1_keterangan'    => ['nullable', 'string', 'max:1000'],

            // Kala II
            'kala2_mulai_at'      => ['nullable', 'date'],
            'bayi_lahir_at'       => ['nullable', 'date'],
            'bayi_jenis_kelamin'  => ['nullable', Rule::in(['L','P'])],
            'bayi_bb_gram'        => ['nullable', 'integer', 'min:200', 'max:8000'],
            'bayi_pb_cm'          => ['nullable', 'numeric', 'min:20', 'max:80'],
            'bayi_lahir_spontan'  => ['nullable', 'boolean'],
            'bayi_lgs_menangis'   => ['nullable', 'boolean'],
            'bayi_apgar_1'        => ['nullable', 'integer', 'min:0', 'max:10'],
            'bayi_apgar_5'        => ['nullable', 'integer', 'min:0', 'max:10'],

            // Kala III
            'kala3_mulai_at'         => ['nullable', 'date'],
            'plasenta_lahir_at'      => ['nullable', 'date'],
            'plasenta_lahir_spontan' => ['nullable', 'boolean'],
            'mak_iii_dilakukan'      => ['nullable', 'boolean'],
            'amniotomi'              => ['nullable', 'boolean'],
            'tfu_sepusat'            => ['nullable', 'boolean'],
            'uc_kuat'                => ['nullable', 'boolean'],
            'eksplorasi_dilakukan'   => ['nullable', 'boolean'],
            'sisa_plasenta'          => ['nullable', 'boolean'],
            'selaput_lengkap'        => ['nullable', 'boolean'],

            // Kala IV
            'kala4_mulai_at'     => ['nullable', 'date'],
            'kala4_selesai_at'   => ['nullable', 'date'],
            'perineum_laserasi'  => ['nullable', Rule::in(array_keys(Delivery::laserasiOptions()))],
            'heckting_dilakukan' => ['nullable', 'boolean'],
            'heckting_lidocain'  => ['nullable', 'boolean'],
            'perdarahan_ml'      => ['nullable', 'integer', 'min:0', 'max:5000'],
            'kala4_keluhan'      => ['nullable', 'string', 'max:1000'],

            // Outcome
            'ibu_kondisi'    => ['nullable', Rule::in(array_keys(Delivery::ibuKondisiOptions()))],
            'bayi_kondisi'   => ['nullable', Rule::in(array_keys(Delivery::bayiKondisiOptions()))],
            'rujukan_ke'     => ['nullable', 'string', 'max:150'],
            'rujukan_alasan' => ['nullable', 'string', 'max:500'],

            // ===== Siklus Rujukan =====
            'rujuk_siklus_status'      => ['nullable', Rule::in(array_keys(Delivery::rujukSiklusStatuses()))],
            'rujuk_dikirim_at'         => ['nullable', 'date'],
            'rujuk_transport'          => ['nullable', Rule::in(array_keys(Delivery::transportOptions()))],
            'rujuk_pendamping'         => ['nullable', 'string', 'max:150'],
            'rujuk_bawa'               => ['nullable', 'string', 'max:500'],
            'rujuk_kontak_rs'          => ['nullable', 'string', 'max:50'],
            'rujuk_diterima_at'        => ['nullable', 'date'],
            'rujuk_diterima_by'        => ['nullable', 'string', 'max:150'],
            'rujuk_balik_no'           => ['nullable', 'string', 'max:50'],
            'rujuk_balik_diterima_at'  => ['nullable', 'date'],
            'rujuk_balik_diagnosis'    => ['nullable', 'string', 'max:1000'],
            'rujuk_balik_tindakan'     => ['nullable', 'string', 'max:1000'],
            'rujuk_balik_outcome_ibu'  => ['nullable', 'string', 'max:500'],
            'rujuk_balik_outcome_bayi' => ['nullable', 'string', 'max:500'],
            'rujuk_balik_rekomendasi'  => ['nullable', 'string', 'max:1000'],
            'rujuk_balik_dokter_rs'    => ['nullable', 'string', 'max:150'],

            // Terapi Ibu
            'terapi_amoxicillin'     => ['nullable', 'boolean'],
            'terapi_asam_mef'        => ['nullable', 'boolean'],
            'terapi_fe'              => ['nullable', 'boolean'],
            'terapi_metergin'        => ['nullable', 'boolean'],
            'terapi_vita_dose1_at'   => ['nullable', 'date'],
            'terapi_vita_dose2_at'   => ['nullable', 'date'],
            'terapi_ibu_dosis_notes' => ['nullable', 'string', 'max:500'],

            // Terapi Bayi
            'bayi_injeksi_neo_k'  => ['nullable', 'boolean'],
            'bayi_neo_k_at'       => ['nullable', 'date'],
            'bayi_salep_mata'     => ['nullable', 'boolean'],
            'bayi_imunisasi_hb0'  => ['nullable', 'boolean'],
            'bayi_hb0_at'         => ['nullable', 'date'],
            'bayi_hb0_no_batch'   => ['nullable', 'string', 'max:50'],
        ];

        return array_merge(parent::rules(), $additional);
    }
}
