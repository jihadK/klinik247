<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class PayerType extends BaseModel
{
    protected $table = 'tbm_payer_types';

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'name', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
