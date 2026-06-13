<?php

namespace App\Http\Requests\Inc;

use App\Models\DeliverySoap;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSoapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('inc.soap') ?? false;
    }

    public function rules(): array
    {
        return [
            'observed_at'   => ['required', 'date'],
            'kala'          => ['nullable', Rule::in(array_keys(DeliverySoap::kalaOptions()))],
            'subjective'    => ['nullable', 'string', 'max:1000'],

            'ttv_td'        => ['nullable', 'string', 'max:20'],
            'ttv_nadi'      => ['nullable', 'integer', 'min:30', 'max:250'],
            'ttv_suhu'      => ['nullable', 'numeric', 'min:30', 'max:45'],
            'ttv_rr'        => ['nullable', 'integer', 'min:5', 'max:60'],
            'djj'           => ['nullable', 'integer', 'min:60', 'max:220'],
            'his_per_10'    => ['nullable', 'integer', 'min:0', 'max:10'],
            'his_durasi'    => ['nullable', 'string', 'max:20'],
            'vt_pembukaan'  => ['nullable', 'numeric', 'min:0', 'max:10'],
            'vt_penurunan'  => ['nullable', 'string', 'max:30'],
            'ketuban'       => ['nullable', Rule::in(array_keys(DeliverySoap::ketubanOptions()))],
            'hb_gr_dl'      => ['nullable', 'numeric', 'min:0', 'max:30'],
            'alb'           => ['nullable', 'string', 'max:20'],

            'assessment'    => ['nullable', 'string', 'max:500'],
            'plan'          => ['nullable', 'string', 'max:500'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }
}
