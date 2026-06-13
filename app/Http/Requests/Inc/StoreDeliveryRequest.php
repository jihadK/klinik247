<?php

namespace App\Http\Requests\Inc;

use App\Models\Delivery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('inc.create') ?? false;
    }

    public function rules(): array
    {
        $boolFields = array_keys(Delivery::penapisanItems());
        $rules = [
            'pregnancy_id'     => ['required', 'integer', Rule::exists('tbr_pregnancies', 'id')],
            'patient_visit_id' => ['nullable', 'integer'],
            'visit_date'       => ['required', 'date'],
            'masuk_at'         => ['nullable', 'date'],

            'penapisan_keputusan' => ['nullable', Rule::in(array_keys(Delivery::keputusanPenapisanOptions()))],

            // Masuk PMB
            'masuk_ttv_td'       => ['nullable', 'string', 'max:20'],
            'masuk_ttv_nadi'     => ['nullable', 'integer', 'min:30', 'max:250'],
            'masuk_ttv_suhu'     => ['nullable', 'numeric', 'min:30', 'max:45'],
            'masuk_ttv_rr'       => ['nullable', 'integer', 'min:5', 'max:60'],
            'masuk_djj'          => ['nullable', 'integer', 'min:60', 'max:220'],
            'masuk_his_per_10'   => ['nullable', 'integer', 'min:0', 'max:10'],
            'masuk_vt_pembukaan' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'masuk_ketuban'      => ['nullable', Rule::in(array_keys(Delivery::ketubanOptions()))],
            'masuk_keluhan'      => ['nullable', 'string', 'max:1000'],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        foreach ($boolFields as $b) {
            $rules[$b] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function prepareForValidation(): void
    {
        $boolFields = array_keys(Delivery::penapisanItems());
        $data = [];
        foreach ($boolFields as $b) {
            $data[$b] = $this->boolean($b);
        }
        // Auto-default visit_date dari today kalau kosong (safety net)
        if (! $this->filled('visit_date')) {
            $data['visit_date'] = today()->format('Y-m-d');
        }
        // Auto-default masuk_at = now kalau kosong
        if (! $this->filled('masuk_at')) {
            $data['masuk_at'] = now()->format('Y-m-d H:i:s');
        }
        $this->merge($data);
    }
}
