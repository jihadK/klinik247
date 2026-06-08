<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Super admin (site_id NULL) bisa lihat semua sites
        // Admin biasa cuma lihat data sitenya sendiri
        $isSuper = $user->isSuperAdmin();

        $stats = [
            'patients' => (int) DB::table('tbm_patients')
                ->whereNull('deleted_date')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $user->site_id))
                ->count(),
            'doctors' => (int) DB::table('tbm_doctors')
                ->whereNull('deleted_date')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $user->site_id))
                ->count(),
            'services' => (int) DB::table('tbm_services')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $user->site_id))
                ->count(),
            'medicines' => (int) DB::table('tbm_medicines')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $user->site_id))
                ->count(),
        ];

        // Super admin: list semua site untuk overview
        $sites = $isSuper ? Site::active()->get() : collect();

        // Site sekarang (untuk admin biasa)
        $currentSite = $user->site_id ? Site::find($user->site_id) : null;

        // Recent login attempts
        $recentLogins = DB::table('tbh_login_attempts')
            ->select('username', 'ip_address', 'success', 'failure_reason', 'attempted_at', 'site_id')
            ->when(! $isSuper, fn ($q) => $q->where('site_id', $user->site_id))
            ->orderByDesc('attempted_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'sites', 'currentSite', 'recentLogins', 'isSuper'));
    }
}
