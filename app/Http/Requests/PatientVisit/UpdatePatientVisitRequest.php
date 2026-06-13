<?php

namespace App\Http\Requests\PatientVisit;

use App\Models\PatientVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('visits.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'visit_type'      => ['nullable', Rule::in(array_keys(PatientVisit::visitTypes()))],
            'payer_type_id'   => ['nullable', 'integer', Rule::exists('tbm_payer_types', 'id')],
            'chief_complaint' => ['nullable', 'string', 'max:1000'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'status'          => ['nullable', Rule::in(array_keys(PatientVisit::statuses()))],
            'cancel_reason'   => ['nullable', 'string', 'max:500'],
        ];
    }
}
