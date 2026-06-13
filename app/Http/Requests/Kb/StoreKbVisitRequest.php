<?php

namespace App\Http\Requests\Kb;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKbVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('kb.visit') ?? false;
    }

    public function rules(): array
    {
        return [
            'acceptor_id'      => ['required', 'integer', Rule::exists('tbr_kb_acceptors', 'id')],
            'patient_visit_id' => ['nullable', 'integer'],
            'visit_date'       => ['required', 'date'],
            'haid_tanggal'     => ['nullable', 'date'],
            'berat_badan'      => ['nullable', 'numeric', 'min:0', 'max:300'],
            'tekanan_darah'    => ['nullable', 'string', 'max:20'],
            'keluhan'          => ['nullable', 'string', 'max:500'],
            'efek_samping'     => ['nullable', 'string', 'max:500'],
            'komplikasi'       => ['nullable', 'string', 'max:500'],
            'tindakan'         => ['nullable', 'string', 'max:500'],
            'tanggal_kembali'  => ['nullable', 'date', 'after:visit_date'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
