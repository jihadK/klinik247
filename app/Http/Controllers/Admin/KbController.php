<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kb\StoreKbAcceptorRequest;
use App\Http\Requests\Kb\StoreKbVisitRequest;
use App\Http\Requests\Kb\UpdateKbAcceptorRequest;
use App\Models\EducationLevel;
use App\Models\KbAcceptor;
use App\Models\KbVisit;
use App\Models\KontrasepsiMethod;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Support\DocNumber;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KbController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX (list akseptor) ============================== */
    public function index(Request $request): View
    {
        $acceptors = KbAcceptor::with(['patient', 'kontrasepsi'])
            ->search($request->input('q'))
            ->when($request->filled('status'),      fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('kontrasepsi'), fn ($q) => $q->where('kontrasepsi_id', $request->input('kontrasepsi')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.kb.index', [
            'acceptors'   => $acceptors,
            'statuses'    => KbAcceptor::statuses(),
            'kontrasepsi' => KontrasepsiMethod::active()->get(),
        ]);
    }

    /* ============================== CREATE ACCEPTOR ============================== */
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

        // Check apakah pasien sudah punya akseptor aktif
        $existingAcceptor = $patient
            ? KbAcceptor::where('patient_id', $patient->id)->aktif()->first()
            : null;

        if ($existingAcceptor) {
            return redirect()->route('admin.kb.show', $existingAcceptor)
                ->with('flash', Flash::info(
                    "Pasien <b>{$patient->name}</b> sudah punya akseptor KB aktif (<b>{$existingAcceptor->no_kartu_kb}</b>). Silakan catat kunjungan ulang."
                ));
        }

        return view('admin.kb.create', [
            'acceptor'    => new KbAcceptor([
                'tanggal_dilayani' => today()->format('Y-m-d'),
            ]),
            'patient'     => $patient,
            'visit'       => $visit,
            'kontrasepsi' => KontrasepsiMethod::active()->get(),
            'educations'  => EducationLevel::active()->get(),
        ]);
    }

    /* ============================== STORE ACCEPTOR ============================== */
    public function store(StoreKbAcceptorRequest $request): RedirectResponse
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
            $data['site_id']     = $siteId;
            $data['patient_id']  = $patient->id;
            $data['no_kartu_kb'] = DocNumber::next($siteId, 'KB');
            $data['created_by']  = $user->id;
            $data['served_by']   = $user->id;
            $data['status']      = KbAcceptor::STATUS_AKTIF;

            // Auto-default informed consent (hidden dari form — assumed signed saat akseptor dicatat)
            $data['consent_signed']    = true;
            $data['consent_signed_at'] = now();
            $data['consent_witness']   = $data['consent_witness'] ?: $user->full_name;

            $acceptor = KbAcceptor::create($data);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal simpan akseptor: ' . $e->getMessage()));
        }

        return redirect()->route('admin.kb.show', $acceptor)
            ->with('flash', Flash::ok("Akseptor KB <b>{$acceptor->no_kartu_kb}</b> berhasil terdaftar."));
    }

    /* ============================== SHOW ============================== */
    public function show(KbAcceptor $kb): View
    {
        $kb->load(['site', 'patient.payerType', 'kontrasepsi', 'suamiEducation', 'createdBy', 'servedBy', 'visits.servedBy']);
        return view('admin.kb.show', ['acceptor' => $kb]);
    }

    /* ============================== EDIT ============================== */
    public function edit(KbAcceptor $kb): View|RedirectResponse
    {
        // Lock: acceptor non-aktif (ganti_metode/drop/selesai) tidak boleh diedit
        if ($kb->status !== KbAcceptor::STATUS_AKTIF) {
            return redirect()->route('admin.kb.show', $kb)
                ->with('flash', Flash::warning(
                    "Akseptor <b>{$kb->no_kartu_kb}</b> berstatus <b>{$kb->status_label}</b> dan tidak bisa diedit. Data terkunci untuk audit."
                ));
        }

        $kb->load(['patient', 'kontrasepsi']);
        return view('admin.kb.edit', [
            'acceptor'    => $kb,
            'patient'     => $kb->patient,
            'visit'       => null,
            'kontrasepsi' => KontrasepsiMethod::active()->get(),
            'educations'  => EducationLevel::active()->get(),
            'statuses'    => KbAcceptor::statuses(),
        ]);
    }

    /* ============================== UPDATE ============================== */
    public function update(UpdateKbAcceptorRequest $request, KbAcceptor $kb): RedirectResponse
    {
        // Defense: tolak update kalau status sudah non-aktif
        if ($kb->status !== KbAcceptor::STATUS_AKTIF) {
            return redirect()->route('admin.kb.show', $kb)
                ->with('flash', Flash::err(
                    "Tidak bisa update: akseptor sudah berstatus <b>{$kb->status_label}</b>."
                ));
        }

        try {
            $data = $request->validated();
            // Pertahankan default consent (hidden field tetap value 1)
            $data['consent_signed'] = true;
            if (! $kb->consent_signed_at) {
                $data['consent_signed_at'] = now();
            }
            if (empty($data['consent_witness'])) {
                $data['consent_witness'] = $kb->consent_witness ?: auth()->user()->full_name;
            }
            $kb->update($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal update: ' . $e->getMessage()));
        }

        return redirect()->route('admin.kb.show', $kb)
            ->with('flash', Flash::ok('Data akseptor diperbarui.'));
    }

    /* ============================== DESTROY ============================== */
    public function destroy(KbAcceptor $kb): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('kb.delete')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses.'));
        }
        $kb->delete();
        return redirect()->route('admin.kb.index')
            ->with('flash', Flash::ok('Akseptor KB dihapus.'));
    }

    /* ============================== KUNJUNGAN ULANG ============================== */
    public function storeVisit(StoreKbVisitRequest $request, KbAcceptor $kb): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['acceptor_id'] = $kb->id;
            $data['site_id']     = $kb->site_id;
            $data['created_by']  = auth()->id();
            $data['served_by']   = auth()->id();

            KbVisit::create($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal simpan kunjungan: ' . $e->getMessage()));
        }

        return redirect()->route('admin.kb.show', $kb)
            ->with('flash', Flash::ok('Kunjungan KB tercatat.'));
    }

    public function destroyVisit(KbVisit $visit): RedirectResponse
    {
        $acceptorId = $visit->acceptor_id;
        $visit->delete();
        return redirect()->route('admin.kb.show', $acceptorId)
            ->with('flash', Flash::ok('Catatan kunjungan dihapus.'));
    }

    /* ============================== GANTI ALAT (1-click workflow) ============================== */
    public function gantiAlat(Request $request, KbAcceptor $kb): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('kb.create')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses.'));
        }

        if ($kb->status !== KbAcceptor::STATUS_AKTIF) {
            return back()->with('flash', Flash::err('Hanya akseptor status AKTIF yang bisa ganti alat.'));
        }

        $validated = $request->validate([
            'new_kontrasepsi_id'         => ['required', 'integer', \Illuminate\Validation\Rule::exists('tbm_kontrasepsi_methods', 'id'), 'different:current_kontrasepsi_id'],
            'tanggal_dilepas'            => ['required', 'date'],
            'tanggal_dilayani_baru'      => ['required', 'date'],
            'tanggal_pesan_kontrol_baru' => ['nullable', 'date', 'after_or_equal:tanggal_dilayani_baru'],
            'alasan_ganti'               => ['required', 'string', 'max:500'],
        ], [
            'new_kontrasepsi_id.different'       => 'Alat baru harus berbeda dengan alat saat ini.',
            'tanggal_pesan_kontrol_baru.after_or_equal' => 'Tanggal kontrol harus setelah tanggal pemasangan alat baru.',
        ]);

        $user = $request->user();
        $newKontrasepsi = KontrasepsiMethod::find($validated['new_kontrasepsi_id']);
        $sameAsOld = $newKontrasepsi->id === $kb->kontrasepsi_id;

        try {
            DB::beginTransaction();

            // 1. Close acceptor lama
            $kb->update([
                'status'          => KbAcceptor::STATUS_GANTI_METODE,
                'tanggal_dilepas' => $validated['tanggal_dilepas'],
                'drop_reason'     => $validated['alasan_ganti'],
            ]);

            // 2. Ambil data pemeriksaan terakhir (untuk pre-fill vital sign)
            $lastVisit = $kb->visits()->orderByDesc('visit_date')->first();

            // 3. Build data acceptor baru — copy dari lama
            $newData = [
                'site_id'            => $kb->site_id,
                'patient_id'         => $kb->patient_id,
                'kontrasepsi_id'     => $newKontrasepsi->id,
                'previous_acceptor_id' => $kb->id,
                'no_kartu_kb'        => DocNumber::next($kb->site_id, 'KB'),
                'status'             => KbAcceptor::STATUS_AKTIF,
                'created_by'         => $user->id,
                'served_by'          => $user->id,

                // ===== Copy SUAMI (lengkap) =====
                'suami_name'         => $kb->suami_name,
                'suami_age'          => $kb->suami_age,
                'suami_education_id' => $kb->suami_education_id,
                'suami_kawin_ke'     => $kb->suami_kawin_ke,
                'suami_occupation'   => $kb->suami_occupation,
                'akseptor_kawin_ke'  => $kb->akseptor_kawin_ke,

                // ===== Copy STATUS PESERTA KB (jarang berubah) =====
                'jumlah_anak_hidup'            => $kb->jumlah_anak_hidup,
                'keinginan_punya_anak_lagi'    => $kb->keinginan_punya_anak_lagi,
                'kapan_ingin_anak_lagi'        => $kb->kapan_ingin_anak_lagi,
                'status_kehamilan_saat_ini'    => $kb->status_kehamilan_saat_ini,
                'riwayat_komplikasi_kehamilan' => $kb->riwayat_komplikasi_kehamilan,
                'sikap_pasangan_terhadap_kb'   => $kb->sikap_pasangan_terhadap_kb,
                'edukasi_hiv_aids_pms'         => $kb->edukasi_hiv_aids_pms,
                'metode_ganda_pakai_kondom'    => $kb->metode_ganda_pakai_kondom,

                // ===== Pemeriksaan Awal: ambil dari last visit kalau ada, else dari acceptor lama =====
                'tekanan_darah' => $lastVisit->tekanan_darah ?? $kb->tekanan_darah,
                'berat_badan'   => $lastVisit->berat_badan   ?? $kb->berat_badan,
                'haid_terakhir' => $lastVisit->haid_tanggal  ?? $kb->haid_terakhir,

                // ===== Riwayat fixed (likely sama) =====
                'kebiasaan_merokok'           => $kb->kebiasaan_merokok,
                'sedang_menyusui'             => $kb->sedang_menyusui,
                'tanggal_persalinan_terakhir' => $kb->tanggal_persalinan_terakhir,
                'sakit_kuning'                => $kb->sakit_kuning,
                'perdarahan_per_vaginam'      => $kb->perdarahan_per_vaginam,
                'tumor_payudara'              => $kb->tumor_payudara,
                'keluhan'                     => $kb->keluhan,
                'fluoralbus_gatal'            => $kb->fluoralbus_gatal,
                'fluoralbus_seperti_susu'     => $kb->fluoralbus_seperti_susu,
                'fluoralbus_busa'             => $kb->fluoralbus_busa,
                'fluoralbus_cair'             => $kb->fluoralbus_cair,

                // ===== IUD-specific: copy HANYA kalau alat baru sama-sama IUD =====
                'iud_tanda_radang'            => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_tanda_radang : false,
                'iud_tumor'                   => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_tumor : false,
                'iud_posisi_rahim'            => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_posisi_rahim : null,
                'iud_genetalia_varices'       => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_genetalia_varices : false,
                'iud_genetalia_jengger'       => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_genetalia_jengger : false,
                'iud_genetalia_condilo'       => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_genetalia_condilo : false,
                'iud_genetalia_bartholinitis' => ($newKontrasepsi->code === 'KTR-IUD') ? $kb->iud_genetalia_bartholinitis : false,

                // ===== Pelayanan baru =====
                'tanggal_dilayani'      => $validated['tanggal_dilayani_baru'],
                'tanggal_pesan_kontrol' => $validated['tanggal_pesan_kontrol_baru'] ?? null,

                // ===== Consent auto =====
                'consent_signed'    => true,
                'consent_signed_at' => now(),
                'consent_witness'   => $user->full_name,

                'notes' => "Ganti dari {$kb->no_kartu_kb} ({$kb->kontrasepsi?->name}). Alasan: {$validated['alasan_ganti']}",
            ];

            $newAcceptor = KbAcceptor::create($newData);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('flash', Flash::err('Gagal proses ganti alat: ' . $e->getMessage()));
        }

        return redirect()->route('admin.kb.show', $newAcceptor)
            ->with('flash', Flash::ok(
                "Ganti alat berhasil. Akseptor baru: <b>{$newAcceptor->no_kartu_kb}</b> ({$newKontrasepsi->name}). Silakan review & edit jika ada perubahan."
            ));
    }

    /* ============================== CETAK KARTU ============================== */
    public function kartu(KbAcceptor $kb): View
    {
        $kb->load(['site', 'patient', 'kontrasepsi', 'visits' => fn ($q) => $q->orderBy('visit_date')]);
        return view('admin.kb.kartu', ['acceptor' => $kb]);
    }
}
