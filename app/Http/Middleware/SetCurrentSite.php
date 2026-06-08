<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk set current site context (multi-tenant).
 *
 * Strategi (urut prioritas):
 *   1. Kalau user login → ambil dari user.site_id
 *   2. (Future) subdomain detection
 *
 * Hasil di-bind ke container: app('current_site_id')
 * Bisa null kalau super admin (akses lintas site).
 */
class SetCurrentSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $siteId = null;

        // Strategi 1: ambil dari user login (paling reliable)
        if ($user = $request->user()) {
            $siteId = $user->site_id;  // bisa null untuk super admin
        }

        // Bind ke container — dipakai BaseModel global scope
        if ($request->user()) {
            // hanya bind kalau ada konteks user (login)
            app()->instance('current_site_id', $siteId);
        }

        return $next($request);
    }
}
