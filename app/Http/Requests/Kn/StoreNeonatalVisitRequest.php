<?php

namespace App\Http\Requests\Kn;

use App\Models\NeonatalVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNeonatalVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('kn.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'kn_number'         => ['required', 'integer', 'min:1', 'max:3'],
            'visit_date'        => ['required', 'date'],
            'visit_time'        => ['nullable', 'date'],
            'berat_badan_gram'  => ['nullable', 'integer', 'min:200', 'max:15000'],
            'panjang_badan_cm'  => ['nullable', 'numeric', 'min:20', 'max:100'],
            'lingkar_kepala_cm' => ['nullable', 'numeric', 'min:20', 'max:60'],
            'suhu_celcius'      => ['nullable', 'numeric', 'min:30', 'max:45'],
            'tali_pusat'        => ['nullable', Rule::in(array_keys(NeonatalVisit::taliPusatOptions()))],
            'menyusu'           => ['nullable', Rule::in(array_keys(NeonatalVisit::menyusuOptions()))],
            'ikterus_level'     => ['nullable', 'integer', 'min:0', 'max:4'],
            'tanda_bahaya'      => ['nullable', 'array'],
            'tanda_bahaya.*'    => ['string'],
            'masalah_lain'      => ['nullable', 'string', 'max:500'],
            'tindakan'          => ['nullable', 'string', 'max:500'],
            'terapi'            => ['nullable', 'string', 'max:500'],
            'dirujuk'           => ['nullable', 'boolean'],
            'rujukan_alasan'    => ['nullable', 'string', 'max:500'],
            'tanggal_kembali'   => ['nullable', 'date'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['dirujuk' => $this->boolean('dirujuk')]);
    }
}
