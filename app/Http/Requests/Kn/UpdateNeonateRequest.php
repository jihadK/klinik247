<?php

namespace App\Http\Requests\Kn;

use App\Models\Neonate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNeonateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('kn.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_bayi'        => ['required', 'string', 'max:150'],
            'jenis_kelamin'    => ['nullable', Rule::in(['L','P'])],
            'tanggal_lahir'    => ['nullable', 'date'],
            'jam_lahir'        => ['nullable'],
            'bb_lahir_gram'    => ['nullable', 'integer', 'min:200', 'max:8000'],
            'pb_lahir_cm'      => ['nullable', 'numeric', 'min:20', 'max:80'],
            'apgar_1'          => ['nullable', 'integer', 'min:0', 'max:10'],
            'apgar_5'          => ['nullable', 'integer', 'min:0', 'max:10'],
            'imd_dilakukan'    => ['nullable', 'boolean'],
            'vit_k1_diberi'    => ['nullable', 'boolean'],
            'vit_k1_at'        => ['nullable', 'date'],
            'salep_mata'       => ['nullable', 'boolean'],
            'hb0_diberi'       => ['nullable', 'boolean'],
            'hb0_at'           => ['nullable', 'date'],
            'hb0_batch'        => ['nullable', 'string', 'max:50'],
            'status'           => ['nullable', Rule::in(array_keys(Neonate::statuses()))],
            'keterangan_akhir' => ['nullable', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'imd_dilakukan' => $this->boolean('imd_dilakukan'),
            'vit_k1_diberi' => $this->boolean('vit_k1_diberi'),
            'salep_mata'    => $this->boolean('salep_mata'),
            'hb0_diberi'    => $this->boolean('hb0_diberi'),
        ]);
    }
}
