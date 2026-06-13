<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('sites.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:150'],
            'address'             => ['nullable', 'string', 'max:255'],
            'city'                => ['nullable', 'string', 'max:100'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'email'               => ['nullable', 'email', 'max:100'],
            'logo'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:1024'],
            'kop_image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'letterhead_subtitle' => ['nullable', 'string', 'max:200'],
            'letterhead_director' => ['nullable', 'string', 'max:150'],
            'letterhead_sipb'     => ['nullable', 'string', 'max:50'],
            'letterhead_city'     => ['nullable', 'string', 'max:100'],
            'is_active'           => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active', true)]);
    }
}
