<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * BaseModel — extend ini untuk semua tabel yang punya kolom created_date/updated_date.
 *
 * Multi-tenant: kalau tabel punya kolom `site_id`, otomatis di-filter
 * berdasarkan current_site_id yang di-set di SetCurrentSite middleware.
 *
 * Untuk skip filter (mis. super admin lihat semua site):
 *   Model::withoutGlobalScope('site')->get();
 */
abstract class BaseModel extends Model
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    /**
     * Set true di model child kalau tabel TIDAK punya kolom site_id
     * (mis. master global seperti specialties, permissions, sites).
     */
    protected bool $isSiteScoped = true;

    protected static function booted(): void
    {
        // ===== Global scope: auto-filter by site_id =====
        static::addGlobalScope('site', function (Builder $builder) {
            $model = $builder->getModel();

            // Skip kalau model di-flag tidak punya site_id
            if (! $model->isSiteScoped) return;

            // Skip kalau current site belum di-set (mis. saat boot/migration)
            if (! app()->bound('current_site_id')) return;

            $siteId = app('current_site_id');

            // Skip kalau super admin (site_id = null = semua site)
            if ($siteId === null) return;

            $table = $model->getTable();
            $builder->where($table . '.site_id', $siteId);
        });

        // ===== Auto-set site_id saat create =====
        static::creating(function ($model) {
            if (! $model->isSiteScoped) return;
            if (! empty($model->site_id)) return; // sudah di-set manual
            if (! app()->bound('current_site_id')) return;

            $siteId = app('current_site_id');
            if ($siteId !== null) {
                $model->site_id = $siteId;
            }
        });
    }

    /**
     * Helper untuk query lintas-site (super admin only).
     */
    public function scopeAllSites(Builder $q): Builder
    {
        return $q->withoutGlobalScope('site');
    }
}
