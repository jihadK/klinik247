<?php

namespace App\Http\Requests\Pnc;

use App\Models\PostnatalVisit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostnatalVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('pnc.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'delivery_id'   => ['required', 'integer', Rule::exists('tbr_deliveries', 'id')],
            'kf_number'     => ['required', 'integer', 'min:1', 'max:4'],
            'visit_date'    => ['required', 'date'],
            'visit_time'    => ['nullable', 'date'],

            'ttv_td'        => ['nullable', 'string', 'max:20'],
            'ttv_nadi'      => ['nullable', 'integer', 'min:30', 'max:250'],
            'ttv_suhu'      => ['nullable', 'numeric', 'min:30', 'max:45'],
            'ttv_rr'        => ['nullable', 'integer', 'min:5', 'max:60'],

            'kondisi_umum'  => ['nullable', Rule::in(['sehat','sakit','komplikasi'])],
            'lokhia'        => ['nullable', Rule::in(array_keys(PostnatalVisit::lokhiaOptions()))],
            'lokhia_jumlah' => ['nullable', Rule::in(['sedikit','sedang','banyak'])],
            'lokhia_bau'    => ['nullable', Rule::in(['normal','busuk'])],
            'jalan_lahir'   => ['nullable', Rule::in(['sehat','luka_basah','luka_kering','infeksi'])],
            'tanda_infeksi' => ['nullable', 'boolean'],
            'kontraksi'     => ['nullable', Rule::in(['kuat','lemah','atonia'])],
            'tfu_cm'        => ['nullable', 'numeric', 'min:0', 'max:30'],
            'payudara'      => ['nullable', Rule::in(['sehat','bengkak','lecet','infeksi','abses'])],
            'asi'           => ['nullable', Rule::in(['lancar','sedikit','tidak'])],
            'vit_a_dose'    => ['nullable', 'integer', 'min:0', 'max:2'],
            'eliminasi_bak' => ['nullable', Rule::in(['lancar','sulit','tidak'])],
            'eliminasi_bab' => ['nullable', Rule::in(['lancar','sulit','tidak'])],
            'keluhan'       => ['nullable', 'string', 'max:1000'],
            'komplikasi'    => ['nullable', 'string', 'max:500'],
            'tindakan'      => ['nullable', 'string', 'max:500'],
            'terapi'        => ['nullable', 'string', 'max:500'],

            'nasehat_diberikan' => ['nullable', 'array'],
            'nasehat_diberikan.*' => ['string'],

            'kb_dikonseling' => ['nullable', 'boolean'],
            'kb_rencana'    => ['nullable', 'string', 'max:50'],

            'tanggal_kembali' => ['nullable', 'date'],
            'rujukan'       => ['nullable', 'boolean'],
            'rujukan_alasan'=> ['nullable', 'string', 'max:500'],
            'status'        => ['nullable', Rule::in(['sehat','sakit','dirujuk'])],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'tanda_infeksi'  => $this->boolean('tanda_infeksi'),
            'kb_dikonseling' => $this->boolean('kb_dikonseling'),
            'rujukan'        => $this->boolean('rujukan'),
        ]);
    }
}
