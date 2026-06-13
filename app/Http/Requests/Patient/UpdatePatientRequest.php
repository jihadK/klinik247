<?php

namespace App\Http\Requests\Patient;

class UpdatePatientRequest extends StorePatientRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('patients.update') ?? false;
    }
}
