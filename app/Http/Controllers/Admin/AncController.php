<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Anc\StoreAncVisitRequest;
use App\Http\Requests\Anc\StorePregnancyRequest;
use App\Http\Requests\Anc\UpdatePregnancyRequest;
use App\Models\AncVisit;
use App\Models\EducationLevel;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Pregnancy;
use App\Models\PregnancyHistory;
use App\Support\DocNumber;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AncController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX ============================== */
    public function index(Request $request): View
    {
        $pregnancies = Pregnancy::with(['patient'])
            ->withMax('ancVisits as next_visit_date', 'tanggal_kembali')
            ->search($request->input('q'))
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('trimester'), function ($q) use ($request) {
                // Hitung HPHT range untuk trimester
                $today = today();
                $tri = (int) $request->input('trimester');
                $minWeeks = $tri === 1 ? 0   : ($tri === 2 ? 13 : 28);
                $maxWeeks = $tri === 1 ? 13  : ($tri === 2 ? 28 : 45);
                $hphtMin = $today->copy()->subWeeks($maxWeeks);
                $hphtMax = $today->copy()->subWeeks($minWeeks);
                $q->whereBetween('hpht', [$hphtMin, $hphtMax]);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.anc.index', [
            'pregnancies' => $pregnancies,
            'statuses'    => Pregnancy::statuses(),
        ]);
    }

    /* ============================== CREATE ============================== */
    public function create(Request $request): View|RedirectResponse
    {
        $patient = null;
        $visit   = null;

        if ($request->filled('visit_id')) {
            $visit = PatientVisit::with('patient')->find($request->input('visit_id'));
            $patient = $visit?->patient;
        } elseif ($request->filled('patient_id')) {
            $patient = Patient::find($request->input('patient_id'));
        }

        // Cek pasien sudah punya kehamilan aktif?
        $existing = $patient ? Pregnancy::where('patient_id', $patient->id)->aktif()->first() : null;
        if ($existing) {
            return redirect()->route('admin.anc.show', $existing)
                ->with('flash', Flash::info(
                    "Pasien <b>{$patient->name}</b> sudah punya kehamilan aktif (<b>{$existing->no_kartu_hamil}</b>, {$existing->gpa_label}). Silakan catat kunjungan ANC."
                ));
        }

        // Validate patient is female
        if ($patient && $patient->gender !== 'P') {
            return redirect()->back()
                ->with('flash', Flash::err("Pasien <b>{$patient->name}</b> bukan perempuan, tidak bisa daftar kehamilan."));
        }

        return view('admin.anc.create', [
            'pregnancy'   => new Pregnancy([
                'gravida'    => 1, 'partus' => 0, 'abortus' => 0, 'hamil_ke' => 1,
                'tanggal_k1' => today()->format('Y-m-d'),
            ]),
            'patient'     => $patient,
            'visit'       => $visit,
            'educations'  => EducationLevel::active()->get(),
            'caraLahirOptions'      => PregnancyHistory::caraLahirOptions(),
            'kondisiAnakOptions'    => PregnancyHistory::kondisiAnakOptions(),
            'tempatBersalinOptions' => PregnancyHistory::tempatBersalinOptions(),
            'penolongOptions'       => PregnancyHistory::penolongOptions(),
        ]);
    }

    /* ============================== STORE ============================== */
    public function store(StorePregnancyRequest $request): RedirectResponse
    {
        $user   = $request->user();
        $siteId = $user->site_id ?? Patient::find($request->input('patient_id'))?->site_id;

        if (! $siteId) {
            return back()->withInput()->with('flash', Flash::err('Site tidak teridentifikasi.'));
        }

        try {
            DB::beginTransaction();

            $patient = Patient::withoutGlobalScope('site')
                ->where('id', $request->input('patient_id'))
                ->where('site_id', $siteId)
                ->firstOrFail();

            $data = $request->validated();
            $histories = $data['histories'] ?? [];
            // K1 visit data (akan dipakai untuk auto-create anc_visit #1)
            $k1VisitData = [
                'tempat_periksa'    => $data['tempat_periksa_k1']    ?? 'klinik',
                'tindakan'          => $data['tindakan_k1']          ?? null,
                'penatalaksanaan'   => $data['penatalaksanaan_k1']   ?? null,
                'tanggal_kembali'   => $data['tanggal_kontrol_k1']   ?? null,
                'status_tt'         => $data['status_tt_k1']         ?? null,
                'pemberian_tt'      => ! empty($data['pemberian_tt_k1']),
                'tfu_cm'            => $data['tfu_k1']               ?? null,
                'djj_per_menit'     => $data['djj_k1']               ?? null,
                'letak_janin'       => $data['letak_janin_k1']       ?? null,
                'map'               => $data['map_k1']               ?? null,
            ];
            unset(
                $data['histories'],
                $data['tindakan_k1'], $data['penatalaksanaan_k1'], $data['tanggal_kontrol_k1'],
                $data['tempat_periksa_k1'], $data['status_tt_k1'], $data['pemberian_tt_k1'],
                $data['tfu_k1'], $data['djj_k1'], $data['letak_janin_k1'], $data['map_k1']
            );

            $data['site_id']        = $siteId;
            $data['patient_id']     = $patient->id;
            $data['no_kartu_hamil'] = DocNumber::next($siteId, 'MH');
            $data['created_by']     = $user->id;
            $data['served_by']      = $user->id;
            $data['status']         = Pregnancy::STATUS_AKTIF;

            // Auto-calc HPL kalau HPHT diisi & HPL kosong
            if (! empty($data['hpht']) && empty($data['hpl'])) {
                $data['hpl'] = Carbon::parse($data['hpht'])->addDays(280)->format('Y-m-d');
            }

            $pregnancy = Pregnancy::create($data);

            // Save riwayat anak sebelumnya
            foreach ($histories as $h) {
                if (empty($h['hamil_ke'])) continue;
                $h['site_id']      = $siteId;
                $h['pregnancy_id'] = $pregnancy->id;
                PregnancyHistory::create($h);
            }

            // ===== Auto-create ANC visit #1 (K1 = kunjungan pertama secara klinis) =====
            $ukAtK1 = null;
            if ($pregnancy->hpht && $pregnancy->tanggal_k1) {
                $ukAtK1 = round($pregnancy->hpht->diffInDays($pregnancy->tanggal_k1) / 7, 1);
            }

            AncVisit::create([
                'site_id'          => $siteId,
                'pregnancy_id'     => $pregnancy->id,
                'patient_visit_id' => $pregnancy->patient_visit_id,
                'visit_date'       => $pregnancy->tanggal_k1,
                'tempat_periksa'   => $k1VisitData['tempat_periksa'],
                'uk_minggu'        => $ukAtK1,
                'berat_badan_kg'   => $pregnancy->berat_badan_awal,
                'tekanan_darah'    => $pregnancy->vital_sign_td,
                'keluhan'          => $pregnancy->keluhan_awal,
                'terapi'           => $k1VisitData['tindakan'],
                'penatalaksanaan'  => $k1VisitData['penatalaksanaan'],
                'tanggal_kembali'  => $k1VisitData['tanggal_kembali'],
                'status_tt'        => $k1VisitData['status_tt'],
                'pemberian_tt'     => $k1VisitData['pemberian_tt'],
                'tfu_cm'           => $k1VisitData['tfu_cm'],
                'djj_per_menit'    => $k1VisitData['djj_per_menit'],
                'letak_janin'      => $k1VisitData['letak_janin'],
                'map'              => $k1VisitData['map'],
                'notes'            => 'K1 — Kunjungan Pertama (auto dari pendaftaran)',
                'created_by'       => $user->id,
                'served_by'        => $user->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal simpan: ' . $e->getMessage()));
        }

        return redirect()->route('admin.anc.show', $pregnancy)
            ->with('flash', Flash::ok("Kehamilan <b>{$pregnancy->no_kartu_hamil}</b> berhasil terdaftar ({$pregnancy->gpa_label})."));
    }

    /* ============================== SHOW ============================== */
    public function show(Pregnancy $anc): View
    {
        $anc->load([
            'site', 'patient.payerType', 'patient.village', 'suamiEducation',
            'createdBy', 'servedBy', 'histories', 'ancVisits.servedBy',
        ]);
        return view('admin.anc.show', ['pregnancy' => $anc]);
    }

    /* ============================== EDIT ============================== */
    public function edit(Pregnancy $anc): View|RedirectResponse
    {
        if ($anc->status !== Pregnancy::STATUS_AKTIF) {
            return redirect()->route('admin.anc.show', $anc)
                ->with('flash', Flash::warning(
                    "Kehamilan <b>{$anc->no_kartu_hamil}</b> berstatus <b>{$anc->status_label}</b> dan tidak bisa diedit."
                ));
        }

        $anc->load(['patient', 'histories']);
        return view('admin.anc.edit', [
            'pregnancy'   => $anc,
            'patient'     => $anc->patient,
            'visit'       => null,
            'educations'  => EducationLevel::active()->get(),
            'statuses'    => Pregnancy::statuses(),
            'caraLahirOptions'      => PregnancyHistory::caraLahirOptions(),
            'kondisiAnakOptions'    => PregnancyHistory::kondisiAnakOptions(),
            'tempatBersalinOptions' => PregnancyHistory::tempatBersalinOptions(),
            'penolongOptions'       => PregnancyHistory::penolongOptions(),
        ]);
    }

    /* ============================== UPDATE ============================== */
    public function update(UpdatePregnancyRequest $request, Pregnancy $anc): RedirectResponse
    {
        if ($anc->status !== Pregnancy::STATUS_AKTIF) {
            return redirect()->route('admin.anc.show', $anc)
                ->with('flash', Flash::err("Status sudah {$anc->status_label}, tidak bisa update."));
        }

        try {
            DB::beginTransaction();
            $data = $request->validated();
            $histories = $data['histories'] ?? [];
            unset($data['histories']);

            // Re-calc HPL kalau HPHT berubah
            if (! empty($data['hpht']) && empty($data['hpl'])) {
                $data['hpl'] = Carbon::parse($data['hpht'])->addDays(280)->format('Y-m-d');
            }

            $anc->update($data);

            // Replace histories
            $anc->histories()->delete();
            foreach ($histories as $h) {
                if (empty($h['hamil_ke'])) continue;
                $h['site_id']      = $anc->site_id;
                $h['pregnancy_id'] = $anc->id;
                PregnancyHistory::create($h);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal update: ' . $e->getMessage()));
        }

        return redirect()->route('admin.anc.show', $anc)
            ->with('flash', Flash::ok('Data kehamilan diperbarui.'));
    }

    /* ============================== DESTROY ============================== */
    public function destroy(Pregnancy $anc): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('anc.delete')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses.'));
        }
        $anc->delete();
        return redirect()->route('admin.anc.index')->with('flash', Flash::ok('Kehamilan dihapus.'));
    }

    /* ============================== ANC VISIT (Kunjungan Kontrol) ============================== */
    public function storeVisit(StoreAncVisitRequest $request, Pregnancy $anc): RedirectResponse
    {
        if ($anc->status !== Pregnancy::STATUS_AKTIF) {
            return back()->with('flash', Flash::err('Kehamilan sudah tidak aktif.'));
        }

        try {
            $data = $request->validated();
            $data['pregnancy_id'] = $anc->id;
            $data['site_id']      = $anc->site_id;
            $data['created_by']   = auth()->id();
            $data['served_by']    = auth()->id();

            // Auto-calc UK kalau HPHT ada & uk_minggu kosong
            if (empty($data['uk_minggu']) && $anc->hpht && ! empty($data['visit_date'])) {
                $days = Carbon::parse($anc->hpht)->diffInDays(Carbon::parse($data['visit_date']));
                $data['uk_minggu'] = round($days / 7, 1);
            }

            AncVisit::create($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal simpan kunjungan: ' . $e->getMessage()));
        }

        return redirect()->route('admin.anc.show', $anc)
            ->with('flash', Flash::ok('Kunjungan ANC tercatat.'));
    }

    public function destroyVisit(AncVisit $visit): RedirectResponse
    {
        $pid = $visit->pregnancy_id;
        $visit->delete();
        return redirect()->route('admin.anc.show', $pid)
            ->with('flash', Flash::ok('Catatan kunjungan dihapus.'));
    }

    /* ============================== CETAK KARTU ============================== */
    public function kartu(Pregnancy $anc): View
    {
        $anc->load(['site', 'patient', 'suamiEducation', 'histories', 'ancVisits' => fn ($q) => $q->orderBy('visit_date')]);
        return view('admin.anc.kartu', ['pregnancy' => $anc]);
    }

    /* ============================== AJAX Calc HPL & IMT ============================== */
    public function ajaxCalcHpl(Request $request)
    {
        $hpht = $request->input('hpht');
        if (! $hpht) return $this->ok(null);
        $hphtDate = Carbon::parse($hpht);
        $hpl = $hphtDate->copy()->addDays(280)->format('Y-m-d');
        // Carbon 3: diffInDays signed by default → arah HPHT→now agar positif saat HPHT di masa lalu (normal)
        $ukNow = round($hphtDate->diffInDays(now()) / 7, 1);
        return $this->ok(['hpl' => $hpl, 'uk_sekarang' => $ukNow]);
    }

    public function ajaxCalcImt(Request $request)
    {
        $tb = (float) $request->input('tinggi_badan_cm');
        $bb = (float) $request->input('berat_badan');
        if ($tb < 100 || $bb < 30) return $this->ok(null);
        $tbM = $tb / 100;
        $imt = round($bb / ($tbM * $tbM), 1);
        $cat = match (true) {
            $imt < 18.5  => ['cat' => 'Underweight', 'recom' => '13–18 kg'],
            $imt < 25    => ['cat' => 'Normal',      'recom' => '11.5–16 kg'],
            $imt < 30    => ['cat' => 'Overweight',  'recom' => '7–11.5 kg'],
            default      => ['cat' => 'Obese',       'recom' => '5–9 kg'],
        };
        return $this->ok(['imt' => $imt, 'category' => $cat['cat'], 'recom' => $cat['recom']]);
    }
}
