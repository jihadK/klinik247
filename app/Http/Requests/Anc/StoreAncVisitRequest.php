<?php

namespace App\Http\Requests\Anc;

use App\Models\AncVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAncVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('anc.visit') ?? false;
    }

    public function rules(): array
    {
        return [
            'pregnancy_id'     => ['required', 'integer', Rule::exists('tbr_pregnancies', 'id')],
            'patient_visit_id' => ['nullable', 'integer'],
            'visit_date'       => ['required', 'date'],
            'tempat_periksa'   => ['nullable', 'string', 'max:150'],
            'keluhan'          => ['nullable', 'string', 'max:500'],

            'tfu_cm'           => ['nullable', 'numeric', 'min:0', 'max:50'],
            'uk_minggu'        => ['nullable', 'numeric', 'min:0', 'max:45'],
            'letak_janin'      => ['nullable', Rule::in(array_keys(AncVisit::letakOptions()))],
            'djj_per_menit'    => ['nullable', 'integer', 'min:60', 'max:220'],

            'berat_badan_kg'   => ['nullable', 'numeric', 'min:30', 'max:200'],
            'tekanan_darah'    => ['nullable', 'string', 'max:20'],
            'map'              => ['nullable', 'numeric', 'min:0', 'max:200'],

            'status_tt'        => ['nullable', Rule::in(AncVisit::statusTtOptions())],
            'pemberian_tt'     => ['nullable', 'boolean'],

            'terapi'           => ['nullable', 'string', 'max:500'],
            'hasil_lab'        => ['nullable', 'string', 'max:500'],
            'penatalaksanaan'  => ['nullable', 'string', 'max:500'],

            'tanggal_kembali'  => ['nullable', 'date', 'after:visit_date'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['pemberian_tt' => $this->boolean('pemberian_tt')]);
    }
}
