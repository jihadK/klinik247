<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ImmunizationType extends BaseModel
{
    protected $table = 'tbm_immunization_types';

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'name', 'target_group', 'max_dose', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean', 'max_dose' => 'integer', 'sort_order' => 'integer'];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
