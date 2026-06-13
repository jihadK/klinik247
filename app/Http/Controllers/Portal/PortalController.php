<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AncVisit;
use App\Models\ChildVisit;
use App\Models\Delivery;
use App\Models\ImmunizationRecord;
use App\Models\KbAcceptor;
use App\Models\Neonate;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Pregnancy;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class PortalController extends Controller
{
    /** GET /portal — landing page dengan form pencarian */
    public function index(): View
    {
        $sites = Site::active()->get(['id', 'name', 'code']);
        return view('portal.landing', compact('sites'));
    }

    /** POST /portal/search — verifikasi 2-factor + tampilkan hasil */
    public function search(Request $request): View|RedirectResponse
    {
        // Rate limit per IP: 5 attempts / 15 menit
        $key = 'portal-search:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $sec = RateLimiter::availableIn($key);
            return back()
                ->withInput()
                ->with('flash_error', "Terlalu banyak percobaan. Tunggu " . ceil($sec / 60) . " menit lagi.");
        }

        $data = $request->validate([
            'identifier'    => ['required', 'string', 'min:3', 'max:50'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
        ], [
            'identifier.required'    => 'No. RM atau NIK wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi untuk verifikasi.',
        ]);

        $identifier = trim($data['identifier']);
        $tglLahir   = $data['tanggal_lahir'];

        // Cari pasien dengan 2-factor: (No. RM ATAU NIK) DAN tanggal_lahir cocok
        $patient = Patient::withoutGlobalScope('site')
            ->where(function ($q) use ($identifier) {
                $q->where('no_rm', $identifier)
                  ->orWhere('nik', $identifier);
            })
            ->where('birth_date', $tglLahir)
            ->whereNull('deleted_date')
            ->first();

        if (! $patient) {
            RateLimiter::hit($key, 900); // 15 menit
            return back()
                ->withInput()
                ->with('flash_error', 'Data tidak ditemukan. Pastikan No. RM/NIK dan tanggal lahir benar.');
        }

        // Sukses — clear rate limit, stash patient ke session
        RateLimiter::clear($key);
        Session::put('portal_patient_id', $patient->id);
        Session::put('portal_verified_at', now()->toDateTimeString());

        return redirect()->route('portal.result');
    }

    /** GET /portal/result — tampilkan rekam medis (post-verification) */
    public function result(Request $request): View|RedirectResponse
    {
        $pid = Session::get('portal_patient_id');
        if (! $pid) {
            return redirect()->route('portal.index')
                ->with('flash_error', 'Sesi habis. Silakan cari ulang.');
        }

        // Auto-expire session 30 menit
        $verifiedAt = Session::get('portal_verified_at');
        if ($verifiedAt && now()->diffInMinutes($verifiedAt) > 30) {
            Session::forget(['portal_patient_id', 'portal_verified_at']);
            return redirect()->route('portal.index')
                ->with('flash_error', 'Sesi 30 menit habis. Silakan verifikasi ulang.');
        }

        $patient = Patient::withoutGlobalScope('site')
            ->with(['site', 'payerType', 'village', 'district', 'regency'])
            ->find($pid);

        if (! $patient) {
            return redirect()->route('portal.index');
        }

        // Helper: graceful fallback per modul (kalau table belum migrate)
        $safe = fn (callable $cb) => (function () use ($cb) {
            try { return $cb(); } catch (\Throwable $e) { return collect(); }
        })();

        // Aggregate
        $visits      = $safe(fn () => PatientVisit::withoutGlobalScope('site')->where('patient_id', $patient->id)->orderByDesc('visit_time')->get());
        $pregnancies = $safe(fn () => Pregnancy::withoutGlobalScope('site')->where('patient_id', $patient->id)->orderByDesc('hpht')->get());
        $deliveries  = $safe(fn () => Delivery::withoutGlobalScope('site')->where('patient_id', $patient->id)->orderByDesc('bayi_lahir_at')->get());
        $kbAcceptors = $safe(fn () => KbAcceptor::withoutGlobalScope('site')->with('kontrasepsi')->where('patient_id', $patient->id)->get());
        $neonates    = $safe(fn () => Neonate::withoutGlobalScope('site')->with('delivery')->where('patient_id', $patient->id)->get());
        $immRecords  = $safe(fn () => ImmunizationRecord::withoutGlobalScope('site')->with(['immunizationType', 'neonate'])->where('patient_id', $patient->id)->orderByDesc('given_date')->get());

        // Timeline kronologis (terbaru → terlama)
        $timeline = collect();
        foreach ($visits as $v) {
            $timeline->push([
                'date'  => $v->visit_time,
                'title' => 'Kunjungan ' . $v->category_label,
                'meta'  => $v->no_register,
                'tag'   => $v->status_label,
                'tag_color' => $v->category_color === 'danger' ? 'error' : 'secondary',
                'sections' => array_filter([
                    'Keluhan' => $v->chief_complaint,
                    'Pembiayaan' => optional($v->payerType)->name,
                ]),
            ]);
        }
        foreach ($pregnancies as $p) {
            $timeline->push([
                'date'  => $p->tanggal_k1,
                'title' => 'Kehamilan',
                'meta'  => $p->no_kartu_hamil,
                'tag'   => 'GPA: ' . $p->gpa_label,
                'tag_color' => 'primary',
                'sections' => array_filter([
                    'HPHT' => optional($p->hpht)->isoFormat('D MMM YYYY'),
                    'HPL'  => optional($p->hpl)->isoFormat('D MMM YYYY'),
                    'Status' => $p->status_label,
                ]),
            ]);
        }
        foreach ($deliveries as $d) {
            $timeline->push([
                'date'  => $d->bayi_lahir_at ?? $d->masuk_at,
                'title' => 'Persalinan',
                'meta'  => $d->no_persalinan,
                'tag'   => $d->status_label,
                'tag_color' => $d->status === 'rujuk' ? 'error' : 'secondary',
                'sections' => array_filter([
                    'Jenis Kelamin Bayi' => $d->bayi_jenis_kelamin === 'L' ? 'Laki-laki' : ($d->bayi_jenis_kelamin === 'P' ? 'Perempuan' : null),
                    'BB Lahir' => $d->bayi_bb_gram ? $d->bayi_bb_gram . ' gram' : null,
                    'APGAR'    => $d->bayi_apgar_1 ? $d->bayi_apgar_1 . '/' . $d->bayi_apgar_5 : null,
                ]),
            ]);
        }
        foreach ($kbAcceptors as $a) {
            $timeline->push([
                'date'  => $a->tanggal_dilayani,
                'title' => 'Akseptor KB',
                'meta'  => $a->no_kartu_kb,
                'tag'   => $a->status_label,
                'tag_color' => 'primary',
                'sections' => array_filter([
                    'Alat Kontrasepsi' => optional($a->kontrasepsi)->name,
                    'Tgl Kontrol'      => optional($a->tanggal_pesan_kontrol)->isoFormat('D MMM YY'),
                ]),
            ]);
        }
        foreach ($neonates as $n) {
            $timeline->push([
                'date'  => $n->tanggal_lahir,
                'title' => 'Bayi Lahir: ' . $n->nama_bayi,
                'meta'  => $n->no_kartu_bayi,
                'tag'   => $n->status_label,
                'tag_color' => 'secondary',
                'sections' => array_filter([
                    'BB Lahir' => $n->bb_lahir_gram ? $n->bb_lahir_gram . ' gram' : null,
                    'PB Lahir' => $n->pb_lahir_cm ? $n->pb_lahir_cm . ' cm' : null,
                    'Jenis Kelamin' => $n->jenis_kelamin === 'L' ? 'Laki-laki' : ($n->jenis_kelamin === 'P' ? 'Perempuan' : null),
                ]),
            ]);
        }
        foreach ($immRecords as $i) {
            $timeline->push([
                'date'  => $i->given_date,
                'title' => 'Imunisasi ' . $i->immunizationType?->name . ' Dose ' . $i->dose_number,
                'meta'  => optional($i->neonate)->nama_bayi,
                'tag'   => 'Selesai',
                'tag_color' => 'secondary',
                'sections' => array_filter([
                    'Batch'  => $i->no_batch,
                    'Tempat' => $i->tempat,
                ]),
            ]);
        }

        $timeline = $timeline
            ->filter(fn ($t) => $t['date'])
            ->sortByDesc(fn ($t) => $t['date']->timestamp ?? 0)
            ->values();

        return view('portal.result', [
            'patient'   => $patient,
            'timeline'  => $timeline,
            'verified_at' => $verifiedAt,
        ]);
    }

    /** POST /portal/logout — clear session, kembali ke landing */
    public function logout(): RedirectResponse
    {
        Session::forget(['portal_patient_id', 'portal_verified_at']);
        return redirect()->route('portal.index')
            ->with('flash_success', 'Anda telah keluar dari portal.');
    }
}
