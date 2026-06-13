<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends BaseModel
{
    protected $table = 'tbm_districts';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'regency_code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_code', 'code');
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class, 'district_code', 'code');
    }
}
