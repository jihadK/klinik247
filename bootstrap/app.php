<?php

use App\Support\ResponseCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deteksi apakah exception berasal dari koneksi DB yang putus
 * (server down, route to host, refused connection, timeout, dll).
 */
function isDbConnectionLost(\Throwable $e): bool
{
    if (! $e instanceof QueryException && ! $e instanceof \PDOException) {
        // Walk through chained previous exceptions to find PDO/connection error
        $prev = $e->getPrevious();
        while ($prev) {
            if ($prev instanceof \PDOException) return isPdoConnectionError($prev);
            $prev = $prev->getPrevious();
        }
        return false;
    }
    return isPdoConnectionError($e);
}

function isPdoConnectionError(\Throwable $e): bool
{
    $msg = strtolower($e->getMessage());
    $code = $e->getCode();
    // SQLSTATE: 08001/08006/08003/08004/08007/57P03 → connection problems
    $connSqlStates = ['08001', '08003', '08004', '08006', '08007', '57P03', 'HY000'];
    foreach ($connSqlStates as $state) {
        if (str_contains($msg, "sqlstate[$state]") || str_contains($msg, strtolower("[$state]"))) return true;
    }
    $needles = [
        'no route to host',
        'could not connect',
        'connection refused',
        'connection timed out',
        'name or service not known',
        'could not translate host name',
        'server has gone away',
        'lost connection',
        'broken pipe',
        'network is unreachable',
        'connection reset by peer',
    ];
    foreach ($needles as $n) {
        if (str_contains($msg, $n)) return true;
    }
    return false;
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Append ke web group → set site context setelah session start
        $middleware->web(append: [
            \App\Http\Middleware\SetCurrentSite::class,
        ]);

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Override default redirect saat unauthenticated → admin login
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ===== DB Connection Lost Handler (web + JSON) =====
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! isDbConnectionLost($e)) return null;

            if ($request->expectsJson()) {
                return response()->json([
                    'resCode' => ResponseCode::SERVER_ERROR,
                    'resMsg'  => 'Koneksi ke database terputus. Coba beberapa saat lagi.',
                    'data'    => ['retry' => true],
                ], 503);
            }
            return response()->view('errors.db-connection-lost', [
                'retry_url' => $request->fullUrl(),
            ], 503);
        });

        // Global JSON exception handler — format {resCode, resMsg, data}
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->expectsJson()) return null;

            if ($e instanceof ValidationException) {
                return response()->json([
                    'resCode' => ResponseCode::VALIDATION_ERROR,
                    'resMsg'  => 'Validasi gagal',
                    'data'    => ['errors' => $e->errors()],
                ], 422);
            }
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'resCode' => ResponseCode::UNAUTHENTICATED,
                    'resMsg'  => 'Belum login atau session habis',
                ], 401);
            }
            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'resCode' => ResponseCode::FORBIDDEN,
                    'resMsg'  => $e->getMessage() ?: 'Anda tidak punya akses',
                ], 403);
            }
            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'resCode' => ResponseCode::NOT_FOUND,
                    'resMsg'  => 'Data tidak ditemukan',
                ], 404);
            }
            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'resCode' => ResponseCode::SERVER_ERROR,
                    'resMsg'  => $e->getMessage() ?: 'HTTP error',
                ], $e->getStatusCode());
            }
            return response()->json([
                'resCode' => ResponseCode::SERVER_ERROR,
                'resMsg'  => config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan internal',
            ], 500);
        });
    })->create();
