<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    protected $table = 'tbm_roles';

    /** Roles adalah master global — tidak ikut multi-tenant scope */
    protected bool $isSiteScoped = false;

    protected $fillable = ['name', 'description', 'is_super'];

    protected $casts = ['is_super' => 'boolean'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'tbm_role_permissions',
            'role_id',
            'permission_id'
        )->withPivot('granted_at');
    }
}
