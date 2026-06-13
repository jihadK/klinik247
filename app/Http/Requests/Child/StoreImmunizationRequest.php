<?php

namespace App\Http\Requests\Child;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImmunizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('immunization.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'immunization_type_id' => ['required', 'integer', Rule::exists('tbm_immunization_types', 'id')],
            'dose_number'  => ['required', 'integer', 'min:1', 'max:5'],
            'given_date'   => ['required', 'date'],
            'given_at'     => ['nullable', 'date'],
            'no_batch'     => ['nullable', 'string', 'max:50'],
            'tempat'       => ['nullable', 'string', 'max:100'],
            'catatan'      => ['nullable', 'string', 'max:500'],
            'side_effects' => ['nullable', 'string', 'max:500'],
            'next_due_date'=> ['nullable', 'date'],
        ];
    }
}
