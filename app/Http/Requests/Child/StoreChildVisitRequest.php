<?php

namespace App\Http\Requests\Child;

use App\Models\ChildVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChildVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('child.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'visit_date'        => ['required', 'date'],
            'visit_time'        => ['nullable', 'date'],
            'visit_type'        => ['nullable', Rule::in(array_keys(ChildVisit::visitTypeOptions()))],
            'berat_badan_gram'  => ['nullable', 'integer', 'min:200', 'max:50000'],
            'panjang_badan_cm'  => ['nullable', 'numeric', 'min:20', 'max:200'],
            'lingkar_kepala_cm' => ['nullable', 'numeric', 'min:20', 'max:80'],
            'lingkar_lengan_cm' => ['nullable', 'numeric', 'min:5', 'max:50'],
            'suhu_celcius'      => ['nullable', 'numeric', 'min:30', 'max:45'],
            'status_gizi'       => ['nullable', Rule::in(array_keys(ChildVisit::statusGiziOptions()))],
            'stunting'          => ['nullable', 'boolean'],
            'wasting'           => ['nullable', 'boolean'],
            'perkembangan_status' => ['nullable', Rule::in(array_keys(ChildVisit::perkembanganOptions()))],
            'perkembangan_catatan'=> ['nullable', 'string', 'max:500'],
            'keluhan'           => ['nullable', 'string', 'max:500'],
            'diagnosis'         => ['nullable', 'string', 'max:500'],
            'tindakan'          => ['nullable', 'string', 'max:500'],
            'terapi'            => ['nullable', 'string', 'max:500'],
            'asi_eksklusif'     => ['nullable', 'boolean'],
            'pmt'               => ['nullable', 'string', 'max:500'],
            'tanggal_kembali'   => ['nullable', 'date'],
            'rujukan'           => ['nullable', 'boolean'],
            'rujukan_alasan'    => ['nullable', 'string', 'max:500'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'stunting'      => $this->boolean('stunting'),
            'wasting'       => $this->boolean('wasting'),
            'asi_eksklusif' => $this->boolean('asi_eksklusif'),
            'rujukan'       => $this->boolean('rujukan'),
        ]);
    }
}
