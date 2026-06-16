<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientVisit;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user    = auth()->user();
        $isSuper = $user->isSuperAdmin();
        $siteId  = $user->site_id;

        $stats = [
            'patients'  => (int) DB::table('tbm_patients')
                ->whereNull('deleted_date')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $siteId))
                ->count(),
            'doctors'   => (int) DB::table('tbm_doctors')
                ->whereNull('deleted_date')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $siteId))
                ->count(),
            'services'  => (int) DB::table('tbm_services')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $siteId))
                ->count(),
            'medicines' => (int) DB::table('tbm_medicines')
                ->when(! $isSuper, fn ($q) => $q->where('site_id', $siteId))
                ->count(),
        ];

        $queue          = $this->todayQueue($isSuper, $siteId);
        $upcomingVisits = $this->upcomingVisits($isSuper, $siteId);

        return view('admin.dashboard', compact(
            'stats', 'queue', 'upcomingVisits', 'isSuper'
        ));
    }

    /**
     * Ringkasan antrian kunjungan pasien hari ini + daftar pasien aktif.
     */
    private function todayQueue(bool $isSuper, ?int $siteId): array
    {
        $today = today();

        $base = DB::table('tbr_patient_visits')
            ->whereDate('visit_date', $today)
            ->whereNull('tbr_patient_visits.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('tbr_patient_visits.site_id', $siteId));

        // Hitung per status
        $countsByStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = PatientVisit::statuses();
        $byStatus = [];
        foreach ($statuses as $key => $meta) {
            $byStatus[$key] = [
                'label' => $meta['label'],
                'color' => $meta['color'],
                'total' => (int) ($countsByStatus[$key] ?? 0),
            ];
        }

        // Hitung per kategori
        $countsByCategory = (clone $base)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = PatientVisit::categories();
        $byCategory = [];
        foreach ($categories as $key => $meta) {
            $byCategory[$key] = [
                'label' => $meta['label'],
                'color' => $meta['color'],
                'icon'  => $meta['icon'],
                'total' => (int) ($countsByCategory[$key] ?? 0),
            ];
        }

        // List 8 pasien aktif (waiting / in_service), urut queue_number
        $activeList = DB::table('tbr_patient_visits')
            ->join('tbm_patients as pt', 'tbr_patient_visits.patient_id', '=', 'pt.id')
            ->whereDate('tbr_patient_visits.visit_date', $today)
            ->whereNull('tbr_patient_visits.deleted_date')
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('tbr_patient_visits.site_id', $siteId))
            ->whereIn('tbr_patient_visits.status', [PatientVisit::STATUS_WAITING, PatientVisit::STATUS_IN_SERVICE])
            ->orderBy('tbr_patient_visits.queue_number')
            ->orderBy('tbr_patient_visits.visit_time')
            ->select(
                'tbr_patient_visits.id',
                'tbr_patient_visits.no_register',
                'tbr_patient_visits.queue_number',
                'tbr_patient_visits.category',
                'tbr_patient_visits.status',
                'tbr_patient_visits.chief_complaint',
                'tbr_patient_visits.visit_time',
                'pt.name as patient_name',
                'pt.no_rm',
                'pt.phone'
            )
            ->limit(8)
            ->get()
            ->map(function ($r) use ($categories, $statuses) {
                $r->category_label = $categories[$r->category]['label'] ?? '—';
                $r->category_color = $categories[$r->category]['color'] ?? 'secondary';
                $r->category_icon  = $categories[$r->category]['icon']  ?? 'ki-user';
                $r->status_label   = $statuses[$r->status]['label']   ?? $r->status;
                $r->status_color   = $statuses[$r->status]['color']   ?? 'secondary';
                return $r;
            });

        return [
            'total'        => (int) (clone $base)->count(),
            'by_status'    => $byStatus,
            'by_category'  => $byCategory,
            'active_list'  => $activeList,
        ];
    }

    /**
     * Pasien yang dijadwalkan kunjungan ulang dalam 3 hari ke depan.
     */
    private function upcomingVisits(bool $isSuper, ?int $siteId): \Illuminate\Support\Collection
    {
        $from = today()->toDateString();
        $to   = today()->addDays(3)->toDateString();
        $rows = collect();

        $safe = function (callable $cb) {
            try { return $cb(); } catch (\Throwable $e) { return collect(); }
        };

        $rows = $rows->concat($safe(fn () => DB::table('tbr_anc_visits as v')
            ->join('tbh_pregnancies as p', 'v.pregnancy_id', '=', 'p.id')
            ->join('tbm_patients as pt', 'p.patient_id', '=', 'pt.id')
            ->whereBetween('v.tanggal_kembali', [$from, $to])
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('v.site_id', $siteId))
            ->select(
                'pt.id as patient_id', 'pt.name', 'pt.no_rm', 'pt.phone',
                'v.tanggal_kembali as scheduled_date',
                DB::raw("'ANC' as type"),
                DB::raw("'Pemeriksaan Kehamilan' as type_label"),
                DB::raw("'info' as color")
            )->get()
        ));

        $rows = $rows->concat($safe(fn () => DB::table('tbr_kb_visits as v')
            ->join('tbr_kb_acceptors as a', 'v.acceptor_id', '=', 'a.id')
            ->join('tbm_patients as pt', 'a.patient_id', '=', 'pt.id')
            ->whereBetween('v.tanggal_kembali', [$from, $to])
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('v.site_id', $siteId))
            ->select(
                'pt.id as patient_id', 'pt.name', 'pt.no_rm', 'pt.phone',
                'v.tanggal_kembali as scheduled_date',
                DB::raw("'KB' as type"),
                DB::raw("'Kontrol KB' as type_label"),
                DB::raw("'warning' as color")
            )->get()
        ));

        $rows = $rows->concat($safe(fn () => DB::table('tbr_kb_acceptors as a')
            ->join('tbm_patients as pt', 'a.patient_id', '=', 'pt.id')
            ->whereBetween('a.tanggal_pesan_kontrol', [$from, $to])
            ->whereNull('a.tanggal_dilepas')
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('a.site_id', $siteId))
            ->select(
                'pt.id as patient_id', 'pt.name', 'pt.no_rm', 'pt.phone',
                'a.tanggal_pesan_kontrol as scheduled_date',
                DB::raw("'KB' as type"),
                DB::raw("'Kontrol KB' as type_label"),
                DB::raw("'warning' as color")
            )->get()
        ));

        $rows = $rows->concat($safe(fn () => DB::table('tbr_postnatal_visits as v')
            ->join('tbm_patients as pt', 'v.patient_id', '=', 'pt.id')
            ->whereBetween('v.tanggal_kembali', [$from, $to])
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('v.site_id', $siteId))
            ->select(
                'pt.id as patient_id', 'pt.name', 'pt.no_rm', 'pt.phone',
                'v.tanggal_kembali as scheduled_date',
                DB::raw("'PNC' as type"),
                DB::raw("'Pemeriksaan Nifas' as type_label"),
                DB::raw("'success' as color")
            )->get()
        ));

        $rows = $rows->concat($safe(fn () => DB::table('tbr_neonatal_visits as v')
            ->join('tbm_patients as pt', 'v.patient_id', '=', 'pt.id')
            ->whereBetween('v.tanggal_kembali', [$from, $to])
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('v.site_id', $siteId))
            ->select(
                'pt.id as patient_id', 'pt.name', 'pt.no_rm', 'pt.phone',
                'v.tanggal_kembali as scheduled_date',
                DB::raw("'KN' as type"),
                DB::raw("'Pemeriksaan Neonatus' as type_label"),
                DB::raw("'primary' as color")
            )->get()
        ));

        $rows = $rows->concat($safe(fn () => DB::table('tbr_child_visits as v')
            ->join('tbm_patients as pt', 'v.patient_id', '=', 'pt.id')
            ->whereBetween('v.tanggal_kembali', [$from, $to])
            ->whereNull('pt.deleted_date')
            ->when(! $isSuper, fn ($q) => $q->where('v.site_id', $siteId))
            ->select(
                'pt.id as patient_id', 'pt.name', 'pt.no_rm', 'pt.phone',
                'v.tanggal_kembali as scheduled_date',
                DB::raw("'Anak' as type"),
                DB::raw("'Imunisasi / Tumbuh Kembang' as type_label"),
                DB::raw("'danger' as color")
            )->get()
        ));

        return $rows
            ->sortBy('scheduled_date')
            ->values()
            ->map(function ($r) {
                $d = Carbon::parse($r->scheduled_date);
                $daysLeft = (int) today()->diffInDays($d, false);
                $r->days_left  = $daysLeft;
                $r->day_label  = $daysLeft === 0 ? 'Hari ini'
                                : ($daysLeft === 1 ? 'Besok'
                                : ($daysLeft === 2 ? 'Lusa' : $daysLeft.' hari lagi'));
                $r->date_human = $d->isoFormat('dddd, D MMM YYYY');
                return $r;
            });
    }
}
