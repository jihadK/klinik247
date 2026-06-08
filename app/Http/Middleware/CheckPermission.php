<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk cek permission user.
 *
 * Pakai di route:
 *   Route::middleware('permission:patients.view')->...
 *
 * Implementasi: panggil $user->hasPermission($name) yang akan eksekusi
 * PG function fn_user_has_permission(user_id, permission_name).
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json([
                    'resCode' => '03',
                    'resMsg'  => 'Belum login atau session habis',
                ], 401)
                : redirect()->route('admin.login');
        }

        if (! $user->hasPermission($permission)) {
            return $request->expectsJson()
                ? response()->json([
                    'resCode' => '04',
                    'resMsg'  => "Tidak punya akses: {$permission}",
                ], 403)
                : abort(403, "Anda tidak punya akses: {$permission}");
        }

        return $next($request);
    }
}
