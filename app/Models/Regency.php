<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends BaseModel
{
    protected $table = 'tbm_regencies';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'province_code', 'name', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'regency_code', 'code');
    }
}
