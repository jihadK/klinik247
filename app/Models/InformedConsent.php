<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformedConsent extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_informed_consents';
    const DELETED_AT = 'deleted_date';

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'site_id', 'patient_id',
        'document_type', 'document_id',
        'consent_text', 'signed_at', 'signed_by_name', 'signed_by_role',
        'witness_name', 'signature_url',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
}
