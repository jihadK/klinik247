<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Religion extends BaseModel
{
    protected $table = 'tbm_religions';

    public $timestamps = false;

    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
