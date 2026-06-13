<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends BaseModel
{
    protected $table = 'tbm_provinces';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class, 'province_code', 'code');
    }
}
