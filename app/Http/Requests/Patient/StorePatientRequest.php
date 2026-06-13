<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('patients.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'cm_lama'        => ['nullable', 'string', 'max:50'],
            'nik'            => ['nullable', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'no_kk'          => ['nullable', 'string', 'max:20'],
            'nama_kk'        => ['nullable', 'string', 'max:150'],
            'no_bpjs'        => ['nullable', 'string', 'max:20'],

            'name'           => ['required', 'string', 'max:150'],
            'birth_place'    => ['nullable', 'string', 'max:100'],
            'birth_date'     => ['required', 'date', 'before_or_equal:today'],
            'gender'         => ['required', Rule::in(['L', 'P'])],

            'payer_type_id'  => ['nullable', 'integer', Rule::exists('tbm_payer_types', 'id')],
            'education_id'   => ['nullable', 'integer', Rule::exists('tbm_education_levels', 'id')],
            'religion_id'    => ['nullable', 'integer', Rule::exists('tbm_religions', 'id')],
            'occupation'     => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', Rule::in(['belum_menikah', 'menikah', 'cerai_hidup', 'cerai_mati'])],
            'blood_type'     => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

            'province_code'  => ['nullable', 'string', 'size:2',  Rule::exists('tbm_provinces', 'code')],
            'regency_code'   => ['nullable', 'string', 'size:4',  Rule::exists('tbm_regencies', 'code')],
            'district_code'  => ['nullable', 'string', 'size:7',  Rule::exists('tbm_districts', 'code')],
            'village_code'   => ['nullable', 'string', 'size:10', Rule::exists('tbm_villages', 'code')],
            'address'        => ['nullable', 'string'],
            'rt_rw'          => ['nullable', 'string', 'max:20'],
            'postal_code'    => ['nullable', 'string', 'max:10'],
            'wilayah_type'   => ['nullable', Rule::in(['dalam_wilayah', 'luar_wilayah'])],

            'phone'          => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:100'],

            'allergies'        => ['nullable', 'string'],
            'chronic_diseases' => ['nullable', 'string'],
            'medical_history'  => ['nullable', 'string'],

            'emergency_contact'  => ['nullable', 'string', 'max:150'],
            'emergency_phone'    => ['nullable', 'string', 'max:20'],
            'emergency_relation' => ['nullable', 'string', 'max:50'],

            'photo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active'  => ['nullable', 'boolean'],
            'notes'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.size'           => 'NIK harus 16 digit.',
            'nik.regex'          => 'NIK harus 16 digit angka.',
            'name.required'      => 'Nama pasien wajib diisi.',
            'birth_date.required'=> 'Tanggal lahir wajib diisi.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'gender.required'    => 'Jenis kelamin wajib dipilih.',
            'photo.image'        => 'File harus berupa gambar.',
            'photo.mimes'        => 'Format foto: JPG/PNG/WebP.',
            'photo.max'          => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'name'      => trim((string) $this->input('name')),
            'nik'       => $this->input('nik') ? preg_replace('/\D/', '', (string) $this->input('nik')) : null,
            'no_kk'     => $this->input('no_kk') ? preg_replace('/\D/', '', (string) $this->input('no_kk')) : null,
            'no_bpjs'   => $this->input('no_bpjs') ? preg_replace('/\D/', '', (string) $this->input('no_bpjs')) : null,
        ]);
    }
}
