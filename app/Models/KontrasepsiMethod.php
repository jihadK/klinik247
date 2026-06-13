<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class KontrasepsiMethod extends BaseModel
{
    protected $table = 'tbm_kontrasepsi_methods';

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'name', 'method_type', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
