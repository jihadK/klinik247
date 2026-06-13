<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Models\District;
use App\Models\EducationLevel;
use App\Models\Patient;
use App\Models\PayerType;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Religion;
use App\Models\Village;
use App\Support\DocNumber;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PatientController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX ============================== */
    public function index(Request $request)
    {
        $patients = Patient::with(['payerType', 'village'])
            ->search($request->input('q'))
            ->when($request->filled('gender'),     fn ($q) => $q->where('gender', $request->input('gender')))
            ->when($request->filled('payer'),      fn ($q) => $q->where('payer_type_id', $request->input('payer')))
            ->when($request->filled('wilayah'),    fn ($q) => $q->where('wilayah_type', $request->input('wilayah')))
            ->when($request->boolean('inactive'),
                   fn ($q) => $q->where('is_active', false),
                   fn ($q) => $q->where('is_active', true))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $payerTypes = PayerType::active()->get();

        if ($request->wantsJson()) {
            return $this->ok($patients);
        }

        return view('admin.patients.index', compact('patients', 'payerTypes'));
    }

    /* ============================== CREATE ============================== */
    public function create(): View
    {
        $patient = new Patient(['is_active' => true]);
        return view('admin.patients.create', array_merge(
            ['patient' => $patient],
            $this->lookups()
        ));
    }

    /* ============================== STORE ============================== */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $user   = $request->user();
        $siteId = $user->site_id ?? $this->resolveSiteIdFromRequest($request);

        if (! $siteId) {
            return back()->withInput()
                ->with('flash', Flash::err('Site tidak teridentifikasi untuk super admin. Pilih klinik terlebih dahulu.'));
        }

        try {
            $data = $request->validated();
            $data['site_id']    = $siteId;
            $data['created_by'] = $user->id;

            DB::beginTransaction();

            // 1. Generate no_rm otomatis
            $data['no_rm'] = DocNumber::next($siteId, 'RM');

            // 2. Upload photo (kalau ada)
            if ($request->hasFile('photo')) {
                $data['photo_url'] = $request->file('photo')->store("patients/{$siteId}", 'public');
            }

            // 3. Insert
            $patient = Patient::create($data);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()
                ->with('flash', Flash::err('Gagal simpan pasien: ' . $e->getMessage()));
        }

        return redirect()->route('admin.patients.show', $patient)
            ->with('flash', Flash::ok("Pasien <b>{$patient->name}</b> berhasil terdaftar dengan No. RM <b>{$patient->no_rm}</b>"));
    }

    /* ============================== SHOW ============================== */
    public function show(Patient $patient): View
    {
        $patient->load([
            'site', 'payerType', 'education', 'religion',
            'province', 'regency', 'district', 'village', 'createdBy'
        ]);
        return view('admin.patients.show', compact('patient'));
    }

    /* ============================== EDIT ============================== */
    public function edit(Patient $patient): View
    {
        return view('admin.patients.edit', array_merge(
            ['patient' => $patient],
            $this->lookups()
        ));
    }

    /* ============================== UPDATE ============================== */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Upload photo baru kalau ada
            if ($request->hasFile('photo')) {
                if ($patient->photo_url && Storage::disk('public')->exists($patient->photo_url)) {
                    Storage::disk('public')->delete($patient->photo_url);
                }
                $data['photo_url'] = $request->file('photo')->store("patients/{$patient->site_id}", 'public');
            }

            $patient->update($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()
                ->with('flash', Flash::err('Gagal update pasien: ' . $e->getMessage()));
        }

        return redirect()->route('admin.patients.show', $patient)
            ->with('flash', Flash::ok("Data pasien <b>{$patient->name}</b> berhasil diperbarui."));
    }

    /* ============================== DESTROY ============================== */
    public function destroy(Patient $patient): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('patients.delete')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses menghapus pasien.'));
        }

        try {
            $patient->delete();   // soft delete via SoftDeletes trait
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash', Flash::err('Gagal hapus pasien: ' . $e->getMessage()));
        }

        return redirect()->route('admin.patients.index')
            ->with('flash', Flash::ok("Pasien <b>{$patient->name}</b> berhasil dihapus."));
    }

    /* ============================== CETAK KARTU ============================== */
    public function kartu(Patient $patient): View
    {
        $patient->load(['site', 'payerType', 'village', 'district', 'regency', 'province']);
        return view('admin.patients.kartu', compact('patient'));
    }

    /* ============================== AJAX Suggest KK (prefix search) ============================== */
    /** Saran No. KK berdasarkan prefix yang sudah diketik (min 7 digit) */
    public function ajaxSuggestKk(Request $request)
    {
        $prefix = preg_replace('/\D/', '', (string) $request->input('q'));
        if (strlen($prefix) < 7) return $this->ok([]);

        $rows = DB::table('tbm_patients')
            ->selectRaw('no_kk, MAX(nama_kk) AS nama_kk, COUNT(*) AS member_count, MAX(name) AS sample_name')
            ->where('no_kk', 'like', $prefix . '%')
            ->whereNotNull('no_kk')
            ->whereNull('deleted_date')
            // Multi-tenant: filter site_id manual (DB::table tidak pakai global scope)
            ->when(auth()->user()?->site_id, fn ($q) => $q->where('site_id', auth()->user()->site_id))
            ->groupBy('no_kk')
            ->orderBy('no_kk')
            ->limit(8)
            ->get();

        return $this->ok($rows);
    }

    /* ============================== AJAX Lookup KK ============================== */
    /** Cari pasien-pasien existing dengan no_kk sama → ambil nama KK & alamat keluarga */
    public function ajaxLookupKk(Request $request)
    {
        $noKk = preg_replace('/\D/', '', (string) $request->input('no_kk'));
        if (strlen($noKk) < 8) return $this->ok(null);

        // Ambil pasien terbaru dengan no_kk match (sudah otomatis ter-filter site via BaseModel scope)
        $latest = Patient::where('no_kk', $noKk)
            ->whereNotNull('no_kk')
            ->orderByDesc('id')
            ->first(['id', 'name', 'nama_kk', 'no_kk', 'province_code', 'regency_code', 'district_code', 'village_code', 'address', 'rt_rw', 'postal_code', 'wilayah_type']);

        if (! $latest) return $this->ok(null);

        $count = Patient::where('no_kk', $noKk)->count();

        return $this->ok([
            'found'         => true,
            'count'         => $count,
            'sample_name'   => $latest->name,
            'nama_kk'       => $latest->nama_kk,
            'province_code' => $latest->province_code,
            'regency_code'  => $latest->regency_code,
            'district_code' => $latest->district_code,
            'village_code'  => $latest->village_code,
            'address'       => $latest->address,
            'rt_rw'         => $latest->rt_rw,
            'postal_code'   => $latest->postal_code,
            'wilayah_type'  => $latest->wilayah_type,
        ]);
    }

    /* ============================== AJAX (wilayah cascade) ============================== */
    public function ajaxRegencies(Request $request)
    {
        $provinceCode = $request->input('province_code');
        if (! $provinceCode) return $this->ok([]);
        $rows = Regency::where('province_code', $provinceCode)->orderBy('name')->get(['code', 'name']);
        return $this->ok($rows);
    }

    public function ajaxDistricts(Request $request)
    {
        $regencyCode = $request->input('regency_code');
        if (! $regencyCode) return $this->ok([]);
        $rows = District::where('regency_code', $regencyCode)->orderBy('name')->get(['code', 'name']);
        return $this->ok($rows);
    }

    public function ajaxVillages(Request $request)
    {
        $districtCode = $request->input('district_code');
        if (! $districtCode) return $this->ok([]);
        $rows = Village::where('district_code', $districtCode)->orderBy('name')->get(['code', 'name', 'postal_code']);
        return $this->ok($rows);
    }

    /* ============================== HELPERS ============================== */
    protected function lookups(): array
    {
        return [
            'payerTypes'  => PayerType::active()->get(),
            'educations'  => EducationLevel::active()->get(),
            'religions'   => Religion::active()->get(),
            'provinces'   => Province::orderBy('name')->get(),
            'regencies'   => Regency::orderBy('name')->get(),
            'districts'   => District::orderBy('name')->get(),
            'villages'    => Village::orderBy('name')->get(),
        ];
    }

    protected function resolveSiteIdFromRequest(Request $request): ?int
    {
        // Super admin harus pilih site eksplisit via input (untuk Phase nanti)
        return $request->input('site_id');
    }
}
