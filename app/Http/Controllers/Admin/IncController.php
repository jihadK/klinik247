<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inc\StoreDeliveryRequest;
use App\Http\Requests\Inc\StoreSoapRequest;
use App\Http\Requests\Inc\UpdateDeliveryRequest;
use App\Models\Delivery;
use App\Models\DeliverySoap;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\Pregnancy;
use App\Support\DocNumber;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX ============================== */
    public function index(Request $request): View
    {
        $deliveries = Delivery::with(['patient', 'pregnancy'])
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inc.index', [
            'deliveries' => $deliveries,
            'statuses'   => Delivery::statuses(),
        ]);
    }

    /* ============================== CREATE ============================== */
    public function create(Request $request): View|RedirectResponse
    {
        $pregnancy = null;
        $visit     = null;
        $patient   = null;

        // 1. Resolve via pregnancy_id
        if ($request->filled('pregnancy_id')) {
            $pregnancy = Pregnancy::with('patient')->find($request->input('pregnancy_id'));
        }

        // 2. Resolve via visit_id
        if (! $pregnancy && $request->filled('visit_id')) {
            $visit = PatientVisit::with('patient')->find($request->input('visit_id'));
            if ($visit) {
                $pregnancy = Pregnancy::aktif()->where('patient_id', $visit->patient_id)->first();
                $patient   = $visit->patient;
            }
        }

        // 3. Resolve via patient_id (walk-in delivery — pasien datang langsung)
        if (! $pregnancy && $request->filled('patient_id')) {
            $patient = Patient::find($request->input('patient_id'));
            if (! $patient) {
                return redirect()->route('admin.inc.create')
                    ->with('flash', Flash::err('Pasien tidak ditemukan.'));
            }
            if ($patient->gender !== 'P') {
                return redirect()->route('admin.inc.create')
                    ->with('flash', Flash::err("Pasien <b>{$patient->name}</b> bukan perempuan, tidak bisa daftar persalinan."));
            }

            // Cek kehamilan aktif → kalau ada pakai itu
            $pregnancy = Pregnancy::aktif()->where('patient_id', $patient->id)->first();

            // Walk-in: belum ada catatan ANC → auto-create kehamilan minimal
            if (! $pregnancy) {
                $pregnancy = Pregnancy::create([
                    'site_id'         => $patient->site_id,
                    'patient_id'      => $patient->id,
                    'no_kartu_hamil'  => DocNumber::next($patient->site_id, 'MH'),
                    'gravida'         => 1,
                    'partus'          => 0,
                    'abortus'         => 0,
                    'hamil_ke'        => 1,
                    'tanggal_k1'      => today(),
                    'status'          => Pregnancy::STATUS_AKTIF,
                    'created_by'      => $request->user()->id,
                ]);
            }
        }

        // 4. Tidak ada referensi → tampilkan picker
        if (! $pregnancy) {
            return view('admin.inc.create', [
                'delivery'  => new Delivery(),
                'pregnancy' => null,
                'patient'   => null,
                'visit'     => null,
                'penapisan' => Delivery::penapisanItems(),
                'ketubanOptions'   => Delivery::ketubanOptions(),
                'keputusanOptions' => Delivery::keputusanPenapisanOptions(),
            ]);
        }

        if ($pregnancy->status !== Pregnancy::STATUS_AKTIF) {
            return redirect()->route('admin.anc.show', $pregnancy)
                ->with('flash', Flash::err('Kehamilan sudah tidak aktif ('. $pregnancy->status_label .').'));
        }

        // Cek apakah sudah ada delivery untuk kehamilan ini
        $existing = Delivery::where('pregnancy_id', $pregnancy->id)->first();
        if ($existing) {
            return redirect()->route('admin.inc.show', $existing)
                ->with('flash', Flash::info("Persalinan untuk kehamilan ini sudah ada: <b>{$existing->no_persalinan}</b>"));
        }

        return view('admin.inc.create', [
            'delivery'   => new Delivery([
                'visit_date' => today()->format('Y-m-d'),
                'masuk_at'   => now()->format('Y-m-d H:i'),
            ]),
            'pregnancy'  => $pregnancy,
            'patient'    => $pregnancy->patient,
            'visit'      => $visit,
            'penapisan'  => Delivery::penapisanItems(),
            'ketubanOptions'   => Delivery::ketubanOptions(),
            'keputusanOptions' => Delivery::keputusanPenapisanOptions(),
        ]);
    }

    /* ============================== STORE ============================== */
    public function store(StoreDeliveryRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            $pregnancy = Pregnancy::withoutGlobalScope('site')
                ->where('id', $request->input('pregnancy_id'))
                ->firstOrFail();

            $siteId = $pregnancy->site_id;

            $data = $request->validated();
            $data['site_id']       = $siteId;
            $data['patient_id']    = $pregnancy->patient_id;
            $data['no_persalinan'] = DocNumber::next($siteId, 'PS');
            $data['created_by']    = $user->id;
            $data['served_by']     = $user->id;
            $data['status']        = Delivery::STATUS_INPARTU;

            $delivery = new Delivery($data);
            $delivery->penapisan_skor = $delivery->calculatePenapisanSkor();

            // Auto-set keputusan kalau belum diisi (skor >= 1 → suggest rujuk)
            if (empty($delivery->penapisan_keputusan)) {
                $delivery->penapisan_keputusan = $delivery->penapisan_skor > 0
                    ? Delivery::keputusanPenapisanOptions()['rujuk'] && 'rujuk'
                    : 'lanjut';
            }

            // Set Kala I mulai = masuk_at
            $delivery->kala1_mulai_at = $delivery->masuk_at ?? now();

            $delivery->save();

            // Auto-create SOAP row #1 dari data masuk PMB
            DeliverySoap::create([
                'site_id'      => $siteId,
                'delivery_id'  => $delivery->id,
                'observed_at'  => $delivery->masuk_at,
                'kala'         => 'masuk',
                'subjective'   => $delivery->masuk_keluhan,
                'ttv_td'       => $delivery->masuk_ttv_td,
                'ttv_nadi'     => $delivery->masuk_ttv_nadi,
                'ttv_suhu'     => $delivery->masuk_ttv_suhu,
                'ttv_rr'       => $delivery->masuk_ttv_rr,
                'djj'          => $delivery->masuk_djj,
                'his_per_10'   => $delivery->masuk_his_per_10,
                'vt_pembukaan' => $delivery->masuk_vt_pembukaan,
                'ketuban'      => $delivery->masuk_ketuban,
                'assessment'   => 'INPARTU' . ($delivery->penapisan_skor > 0 ? ' (Penapisan: ' . $delivery->penapisan_skor . ' faktor risiko)' : ''),
                'plan'         => $delivery->penapisan_keputusan === 'rujuk' ? 'RUJUK ke RS dengan SpOG' : 'Pimpin persalinan',
                'created_by'   => $user->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal simpan: ' . $e->getMessage()));
        }

        return redirect()->route('admin.inc.show', $delivery)
            ->with('flash', Flash::ok("Persalinan <b>{$delivery->no_persalinan}</b> dimulai."));
    }

    /* ============================== SHOW ============================== */
    public function show(Delivery $inc): View
    {
        $inc->load([
            'site', 'patient.payerType', 'pregnancy', 'createdBy', 'servedBy',
            'soaps.createdBy',
        ]);

        return view('admin.inc.show', [
            'delivery'         => $inc,
            'penapisan'        => Delivery::penapisanItems(),
            'ketubanOptions'   => Delivery::ketubanOptions(),
            'laserasiOptions'  => Delivery::laserasiOptions(),
            'ibuKondisiOptions'  => Delivery::ibuKondisiOptions(),
            'bayiKondisiOptions' => Delivery::bayiKondisiOptions(),
            'kalaOptions'      => DeliverySoap::kalaOptions(),
            'statuses'         => Delivery::statuses(),
        ]);
    }

    /* ============================== EDIT ============================== */
    public function edit(Delivery $inc): View|RedirectResponse
    {
        if (in_array($inc->status, [Delivery::STATUS_SELESAI, Delivery::STATUS_RUJUK])) {
            return redirect()->route('admin.inc.show', $inc)
                ->with('flash', Flash::warning('Persalinan sudah ' . $inc->status_label . ' — data terkunci.'));
        }

        return view('admin.inc.edit', [
            'delivery'  => $inc,
            'pregnancy' => $inc->pregnancy,
            'visit'     => null,
            'penapisan' => Delivery::penapisanItems(),
            'ketubanOptions'   => Delivery::ketubanOptions(),
            'keputusanOptions' => Delivery::keputusanPenapisanOptions(),
            'laserasiOptions'  => Delivery::laserasiOptions(),
            'ibuKondisiOptions'  => Delivery::ibuKondisiOptions(),
            'bayiKondisiOptions' => Delivery::bayiKondisiOptions(),
            'statuses'         => Delivery::statuses(),
        ]);
    }

    /* ============================== UPDATE ============================== */
    public function update(UpdateDeliveryRequest $request, Delivery $inc): RedirectResponse
    {
        if (in_array($inc->status, [Delivery::STATUS_SELESAI, Delivery::STATUS_RUJUK])) {
            return redirect()->route('admin.inc.show', $inc)
                ->with('flash', Flash::err('Persalinan sudah ' . $inc->status_label . '.'));
        }

        try {
            DB::beginTransaction();
            $data = $request->validated();

            // Re-calc penapisan skor
            $tempDelivery = new Delivery($data);
            $data['penapisan_skor'] = $tempDelivery->calculatePenapisanSkor();

            // Auto-update pregnancy status saat selesai/rujuk
            if (($data['status'] ?? null) === Delivery::STATUS_SELESAI) {
                $inc->pregnancy?->update([
                    'status'         => Pregnancy::STATUS_PARTUS,
                    'tanggal_partus' => $data['bayi_lahir_at'] ?? now(),
                    'tanggal_selesai' => now(),
                    'keterangan_akhir' => 'Persalinan selesai — ' . ($inc->no_persalinan ?? ''),
                ]);
            } elseif (($data['status'] ?? null) === Delivery::STATUS_RUJUK) {
                $inc->pregnancy?->update([
                    'status'           => Pregnancy::STATUS_RUJUK,
                    'tanggal_selesai'  => now(),
                    'keterangan_akhir' => 'Dirujuk: ' . ($data['rujukan_alasan'] ?? '-'),
                ]);
            }

            // ===== Auto-create Neonate (header bayi) =====
            // Trigger: selama bayi_lahir_at terisi (bayi sudah lahir di mana saja)
            // Berlaku untuk status SELESAI maupun RUJUK (kalau bayi lahir dulu di PMB sebelum rujuk)
            $bayiLahirAt = $data['bayi_lahir_at'] ?? $inc->bayi_lahir_at;
            if ($bayiLahirAt) {
                $existingNeonate = \App\Models\Neonate::where('delivery_id', $inc->id)->first();
                if (! $existingNeonate) {
                    $lahirAt = \Carbon\Carbon::parse($bayiLahirAt);
                    \App\Models\Neonate::create([
                        'site_id'         => $inc->site_id,
                        'delivery_id'     => $inc->id,
                        'pregnancy_id'    => $inc->pregnancy_id,
                        'patient_id'      => $inc->patient_id,
                        'no_kartu_bayi'   => DocNumber::next($inc->site_id, 'BB'),
                        'nama_bayi'       => 'Bayi Ny. ' . ($inc->patient?->name ?? '-'),
                        'jenis_kelamin'   => $data['bayi_jenis_kelamin'] ?? $inc->bayi_jenis_kelamin,
                        'tanggal_lahir'   => $lahirAt->toDateString(),
                        'jam_lahir'       => $lahirAt->format('H:i:s'),
                        'bb_lahir_gram'   => $data['bayi_bb_gram'] ?? $inc->bayi_bb_gram,
                        'pb_lahir_cm'     => $data['bayi_pb_cm'] ?? $inc->bayi_pb_cm,
                        'apgar_1'         => $data['bayi_apgar_1'] ?? $inc->bayi_apgar_1,
                        'apgar_5'         => $data['bayi_apgar_5'] ?? $inc->bayi_apgar_5,
                        'imd_dilakukan'   => false,
                        'vit_k1_diberi'   => $data['bayi_injeksi_neo_k'] ?? $inc->bayi_injeksi_neo_k ?? false,
                        'vit_k1_at'       => $data['bayi_neo_k_at'] ?? $inc->bayi_neo_k_at,
                        'salep_mata'      => $data['bayi_salep_mata'] ?? $inc->bayi_salep_mata ?? false,
                        'hb0_diberi'      => $data['bayi_imunisasi_hb0'] ?? $inc->bayi_imunisasi_hb0 ?? false,
                        'hb0_at'          => $data['bayi_hb0_at'] ?? $inc->bayi_hb0_at,
                        'hb0_batch'       => $data['bayi_hb0_no_batch'] ?? $inc->bayi_hb0_no_batch,
                        'status'          => 'hidup_sehat',
                        'created_by'      => auth()->id(),
                    ]);
                }
            }

            $inc->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal update: ' . $e->getMessage()));
        }

        return redirect()->route('admin.inc.show', $inc)
            ->with('flash', Flash::ok('Data persalinan diperbarui.'));
    }

    /* ============================== DESTROY ============================== */
    public function destroy(Delivery $inc): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('inc.delete')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses.'));
        }
        $inc->delete();
        return redirect()->route('admin.inc.index')->with('flash', Flash::ok('Persalinan dihapus.'));
    }

    /* ============================== SOAP ============================== */
    public function storeSoap(StoreSoapRequest $request, Delivery $inc): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['delivery_id'] = $inc->id;
            $data['site_id']     = $inc->site_id;
            $data['created_by']  = auth()->id();
            DeliverySoap::create($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal simpan SOAP: ' . $e->getMessage()));
        }
        return redirect()->route('admin.inc.show', $inc)
            ->with('flash', Flash::ok('Observasi SOAP tercatat.'));
    }

    public function destroySoap(DeliverySoap $soap): RedirectResponse
    {
        $did = $soap->delivery_id;
        $soap->delete();
        return redirect()->route('admin.inc.show', $did)
            ->with('flash', Flash::ok('Catatan SOAP dihapus.'));
    }

    /* ============================== KARTU ============================== */
    public function kartu(Delivery $inc): View
    {
        $inc->load(['site', 'patient', 'pregnancy', 'soaps' => fn ($q) => $q->orderBy('observed_at')]);
        return view('admin.inc.kartu', ['delivery' => $inc]);
    }

    /* ============================== QUICK ACTIONS RUJUKAN ============================== */
    public function setRujukSiklus(Request $request, Delivery $inc): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('inc.update')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses.'));
        }

        $action = $request->input('action'); // dikirim, diterima_rs, ada_balasan, selesai, batal

        try {
            DB::beginTransaction();

            $data = ['rujuk_siklus_status' => $action];
            $msg = '';

            switch ($action) {
                case 'dikirim':
                    if (! $inc->rujuk_dikirim_at) $data['rujuk_dikirim_at'] = now();
                    $inc->update(['status' => Delivery::STATUS_RUJUK]);
                    $msg = "Persalinan ditandai <b>Dikirim ke RS</b> ({$inc->no_persalinan}).";
                    break;
                case 'diterima_rs':
                    if (! $inc->rujuk_diterima_at) $data['rujuk_diterima_at'] = now();
                    $msg = "Pasien ditandai <b>Diterima di RS</b>.";
                    break;
                case 'ada_balasan':
                    if (! $inc->rujuk_balik_diterima_at) $data['rujuk_balik_diterima_at'] = now();
                    $msg = "Surat balasan RS ditandai diterima. Lengkapi isi surat balik via Edit.";
                    break;
                case 'selesai':
                    // FINISH FULL — set delivery.status=rujuk + cycle=selesai + pregnancy auto-update
                    $inc->update([
                        'status'              => Delivery::STATUS_RUJUK,
                        'rujuk_siklus_status' => 'selesai',
                    ]);
                    if ($inc->pregnancy && $inc->pregnancy->status === Pregnancy::STATUS_AKTIF) {
                        $inc->pregnancy->update([
                            'status'           => Pregnancy::STATUS_RUJUK,
                            'tanggal_selesai'  => now(),
                            'keterangan_akhir' => 'Persalinan dirujuk ke ' . ($inc->rujukan_ke ?? 'RS') . ($inc->rujukan_alasan ? ' - ' . $inc->rujukan_alasan : ''),
                        ]);
                    }
                    DB::commit();
                    return redirect()->route('admin.inc.show', $inc)
                        ->with('flash', Flash::ok("✅ Rujukan <b>{$inc->no_persalinan}</b> ditandai SELESAI. Kehamilan auto-update ke status 'Dirujuk'."));
                case 'batal':
                    $msg = "Rujukan dibatalkan.";
                    break;
                default:
                    DB::rollBack();
                    return back()->with('flash', Flash::err('Action tidak valid.'));
            }

            $inc->update($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('flash', Flash::err('Gagal: ' . $e->getMessage()));
        }

        return back()->with('flash', Flash::ok($msg));
    }

    /* ============================== SURAT RUJUKAN ============================== */
    public function suratRujukan(Request $request, Delivery $inc): View
    {
        $inc->load(['site', 'patient.village', 'patient.district', 'patient.regency', 'pregnancy', 'servedBy']);

        // Override data dari form (POST) — kalau kosong, fallback ke data DB
        $override = $request->isMethod('post') ? $request->all() : [];

        return view('admin.inc.surat-rujukan', [
            'delivery' => $inc,
            'override' => $override,
        ]);
    }
}
