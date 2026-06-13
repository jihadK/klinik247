<?php

namespace App\Http\Requests\PatientVisit;

use App\Models\PatientVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('visits.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id'      => ['required', 'integer', Rule::exists('tbm_patients', 'id')],
            'category'        => ['required', Rule::in(array_keys(PatientVisit::categories()))],
            'visit_type'      => ['nullable', Rule::in(array_keys(PatientVisit::visitTypes()))],
            'visit_date'      => ['required', 'date'],
            'payer_type_id'   => ['nullable', 'integer', Rule::exists('tbm_payer_types', 'id')],
            'chief_complaint' => ['nullable', 'string', 'max:1000'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Pasien wajib dipilih.',
            'patient_id.exists'   => 'Pasien tidak ditemukan.',
            'category.required'   => 'Kategori kunjungan (Anak/Ibu/KB/Reproduksi) wajib dipilih.',
            'category.in'         => 'Kategori harus salah satu: A, I, K, atau R.',
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
        ];
    }
}
