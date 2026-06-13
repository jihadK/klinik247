<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends BaseModel
{
    protected $table = 'tbm_villages';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'district_code', 'name', 'type', 'postal_code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_code', 'code');
    }
}
