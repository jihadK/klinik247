<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KbVisit extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbr_kb_visits';
    const DELETED_AT = 'deleted_date';

    protected $fillable = [
        'site_id', 'acceptor_id', 'patient_visit_id',
        'visit_date', 'haid_tanggal', 'berat_badan', 'tekanan_darah',
        'keluhan', 'efek_samping', 'komplikasi', 'tindakan',
        'tanggal_kembali', 'notes',
        'served_by', 'created_by',
    ];

    protected $casts = [
        'visit_date'      => 'date',
        'haid_tanggal'    => 'date',
        'tanggal_kembali' => 'date',
        'berat_badan'     => 'decimal:1',
    ];

    public function acceptor(): BelongsTo { return $this->belongsTo(KbAcceptor::class, 'acceptor_id'); }
    public function patientVisit(): BelongsTo { return $this->belongsTo(PatientVisit::class, 'patient_visit_id'); }
    public function servedBy(): BelongsTo { return $this->belongsTo(User::class, 'served_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeOrderRecent(Builder $q): Builder
    {
        return $q->orderByDesc('visit_date');
    }
}
