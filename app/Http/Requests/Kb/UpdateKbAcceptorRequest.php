<?php

namespace App\Http\Requests\Kb;

use App\Models\KbAcceptor;
use Illuminate\Validation\Rule;

class UpdateKbAcceptorRequest extends StoreKbAcceptorRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->hasPermission('kb.update') ?? false;
    }

    public function rules(): array
    {
        // Sama dengan store + tambah status & drop_reason untuk update
        return array_merge(parent::rules(), [
            'status'          => ['nullable', Rule::in(array_keys(KbAcceptor::statuses()))],
            'drop_reason'     => ['nullable', 'string', 'max:500'],
            'tanggal_dilepas' => ['nullable', 'date'],
        ]);
    }
}
