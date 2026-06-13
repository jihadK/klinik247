<?php

namespace App\Http\Requests\Anc;

use App\Models\Pregnancy;
use Illuminate\Validation\Rule;

class UpdatePregnancyRequest extends StorePregnancyRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('anc.update') ?? false;
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status'           => ['nullable', Rule::in(array_keys(Pregnancy::statuses()))],
            'tanggal_partus'   => ['nullable', 'date'],
            'tanggal_abortus'  => ['nullable', 'date'],
            'tanggal_selesai'  => ['nullable', 'date'],
            'keterangan_akhir' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
