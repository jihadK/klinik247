<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends BaseModel
{
    use SoftDeletes;

    protected $table = 'tbm_sites';
    const DELETED_AT = 'deleted_date';

    /** Sites adalah master GLOBAL — tidak ikut multi-tenant scope */
    protected bool $isSiteScoped = false;

    protected $fillable = [
        'code', 'name', 'slug', 'address', 'city', 'phone', 'email',
        'logo_url', 'timezone', 'settings', 'subscription_until', 'is_active',
    ];

    protected $casts = [
        'settings'           => 'array',
        'subscription_until' => 'date',
        'is_active'          => 'boolean',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
