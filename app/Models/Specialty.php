<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Specialty extends BaseModel
{
    protected $table = 'tbm_specialties';

    public $timestamps = false;

    /** Specialties global — share antar klinik */
    protected bool $isSiteScoped = false;

    protected $fillable = ['code', 'name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}
