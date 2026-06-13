<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmunizationRecord extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_immunization_records';
    const DELETED_AT = 'deleted_date';

    protected $fillable = [
        'site_id', 'neonate_id', 'patient_id',
        'immunization_type_id', 'dose_number',
        'given_date', 'given_at', 'given_by',
        'no_batch', 'tempat', 'catatan', 'side_effects',
        'next_due_date',
        'created_by',
    ];

    protected $casts = [
        'given_date'    => 'date',
        'given_at'      => 'datetime',
        'next_due_date' => 'date',
    ];

    public function site(): BelongsTo    { return $this->belongsTo(Site::class, 'site_id'); }
    public function neonate(): BelongsTo { return $this->belongsTo(Neonate::class, 'neonate_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function immunizationType(): BelongsTo { return $this->belongsTo(ImmunizationType::class, 'immunization_type_id'); }
    public function givenBy(): BelongsTo { return $this->belongsTo(User::class, 'given_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
