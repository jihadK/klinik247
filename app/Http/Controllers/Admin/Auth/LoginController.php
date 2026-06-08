<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'site_id'  => ['nullable', 'integer'],   // opsional: kalau ada pilihan multi-site
        ]);

        $loginInput = $credentials['login'];
        $password   = $credentials['password'];
        $siteId     = $credentials['site_id'] ?? null;
        $ip         = $request->ip();
        $userAgent  = substr((string) $request->userAgent(), 0, 255);

        // Cari user — kalau username sama antara site & super admin, prioritas site dulu kalau dipilih
        $query = User::query()
            ->where(function ($q) use ($loginInput) {
                $q->where('username', $loginInput)
                  ->orWhere('email', $loginInput);
            });

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        // Kalau ada banyak match (mis. super admin + site admin pakai username sama), urutkan: site dulu
        $user = $query->orderByRaw('site_id IS NULL ASC')->first();

        if (! $user) {
            $this->logAttempt(null, null, $loginInput, $ip, $userAgent, false, 'user_not_found');
            throw ValidationException::withMessages(['login' => 'Username atau password salah.']);
        }

        if ($user->isLocked()) {
            $this->logAttempt($user->site_id, $user->id, $loginInput, $ip, $userAgent, false, 'account_locked');
            throw ValidationException::withMessages([
                'login' => 'Akun terkunci sampai ' . $user->locked_until->format('H:i, d M Y') . '.',
            ]);
        }

        if (! $user->isActive()) {
            $this->logAttempt($user->site_id, $user->id, $loginInput, $ip, $userAgent, false, 'account_inactive');
            throw ValidationException::withMessages(['login' => 'Akun tidak aktif.']);
        }

        if (! Hash::check($password, $user->password_hash)) {
            $user->increment('failed_login_attempts');
            if ($user->failed_login_attempts + 1 >= 5) {
                $user->update(['locked_until' => now()->addMinutes(30)]);
            }
            $this->logAttempt($user->site_id, $user->id, $loginInput, $ip, $userAgent, false, 'invalid_password');
            throw ValidationException::withMessages(['login' => 'Username atau password salah.']);
        }

        // Cek site_id user (kalau bukan super admin, site harus aktif)
        if ($user->site_id) {
            $siteActive = DB::table('tbm_sites')
                ->where('id', $user->site_id)
                ->where('is_active', true)
                ->whereNull('deleted_date')
                ->exists();
            if (! $siteActive) {
                $this->logAttempt($user->site_id, $user->id, $loginInput, $ip, $userAgent, false, 'site_inactive');
                throw ValidationException::withMessages([
                    'login' => 'Klinik tempat akun ini terdaftar sedang tidak aktif.',
                ]);
            }
        }

        // Sukses
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
        ]);
        $this->logAttempt($user->site_id, $user->id, $loginInput, $ip, $userAgent, true, null);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function logAttempt(?int $siteId, ?int $userId, string $username, ?string $ip, ?string $ua, bool $success, ?string $reason): void
    {
        DB::table('tbh_login_attempts')->insert([
            'site_id'        => $siteId,
            'user_id'        => $userId,
            'username'       => $username,
            'user_type'      => 'admin',
            'ip_address'     => $ip,
            'user_agent'     => $ua,
            'success'        => $success,
            'failure_reason' => $reason,
            'attempted_at'   => now(),
        ]);
    }
}
