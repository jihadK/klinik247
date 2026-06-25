<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientVisit\StorePatientVisitRequest;
use App\Http\Requests\PatientVisit\UpdatePatientVisitRequest;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PayerType;
use App\Support\DocNumber;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientVisitController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX ============================== */
    public function index(Request $request): View
    {
        // Default filter: hari ini
        $dateFrom = $request->input('date_from', now()->toDateString());
        $dateTo   = $request->input('date_to',   $dateFrom);

        $visits = PatientVisit::with(['patient', 'payerType', 'createdBy', 'servedBy'])
            ->search($request->input('q'))
            ->ofCategory($request->input('category'))
            ->ofStatus($request->input('status'))
            ->whereBetween('visit_date', [$dateFrom, $dateTo])
            ->orderByDesc('visit_time')
            ->paginate(20)
            ->withQueryString();

        // Stats hari ini per kategori
        $todayStats = PatientVisit::today()
            ->selectRaw('category, status, COUNT(*) AS total')
            ->groupBy('category', 'status')
            ->get()
            ->groupBy('category');

        $categories = PatientVisit::categories();
        $statuses   = PatientVisit::statuses();

        return view('admin.visits.index', compact(
            'visits', 'categories', 'statuses', 'todayStats', 'dateFrom', 'dateTo'
        ));
    }

    /* ============================== CREATE ============================== */
    public function create(Request $request): View
    {
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->input('patient_id'));
        }

        return view('admin.visits.create', [
            'visit'      => new PatientVisit([
                'visit_date' => today()->format('Y-m-d'),
                'category'   => $request->input('category'),
            ]),
            'patient'    => $patient,
            'categories' => PatientVisit::categories(),
            'visitTypes' => PatientVisit::visitTypes(),
            'payerTypes' => PayerType::active()->get(),
        ]);
    }

    /* ============================== STORE ============================== */
    public function store(StorePatientVisitRequest $request): RedirectResponse
    {
        $user   = $request->user();
        $siteId = $user->site_id ?? Patient::find($request->input('patient_id'))?->site_id;

        if (! $siteId) {
            return back()->withInput()
                ->with('flash', Flash::err('Site tidak teridentifikasi.'));
        }

        try {
            DB::beginTransaction();

            // Pasien harus dari site yang sama (kecuali super admin)
            $patient = Patient::withoutGlobalScope('site')
                ->where('id', $request->input('patient_id'))
                ->where('site_id', $siteId)
                ->first();

            if (! $patient) {
                throw new \DomainException('Pasien tidak ditemukan atau bukan dari klinik ini.');
            }

            $data = $request->validated();
            $data['site_id']     = $siteId;
            $data['patient_id']  = $patient->id;
            $data['no_register'] = DocNumber::next($siteId, 'REG', $data['category']);
            $data['queue_number']= $this->nextQueueNumber($siteId, $data['category'], $data['visit_date']);
            $data['created_by']  = $user->id;
            $data['visit_time']  = now();

            // Default payer dari pasien kalau tidak di-set
            if (empty($data['payer_type_id'])) {
                $data['payer_type_id'] = $patient->payer_type_id;
            }

            $visit = PatientVisit::create($data);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()
                ->with('flash', Flash::err('Gagal daftar kunjungan: ' . $e->getMessage()));
        }

        return redirect()->route('admin.visits.show', $visit)
            ->with('flash', Flash::ok(
                "Kunjungan <b>{$visit->no_register}</b> terdaftar — Antrian #{$visit->queue_number} ({$visit->category_label})"
            ));
    }

    /* ============================== SHOW ============================== */
    public function show(PatientVisit $visit): View
    {
        $visit->load(['patient.payerType', 'patient.village', 'payerType', 'createdBy', 'servedBy', 'site']);
        return view('admin.visits.show', compact('visit'));
    }

    /* ============================== EDIT ============================== */
    public function edit(PatientVisit $visit): View
    {
        $visit->load('patient');
        return view('admin.visits.edit', [
            'visit'      => $visit,
            'patient'    => $visit->patient,
            'categories' => PatientVisit::categories(),
            'visitTypes' => PatientVisit::visitTypes(),
            'statuses'   => PatientVisit::statuses(),
            'payerTypes' => PayerType::active()->get(),
        ]);
    }

    /* ============================== UPDATE ============================== */
    public function update(UpdatePatientVisitRequest $request, PatientVisit $visit): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Tracking transition status otomatis (timestamp)
            if (isset($data['status']) && $data['status'] !== $visit->status) {
                if ($data['status'] === PatientVisit::STATUS_IN_SERVICE && ! $visit->served_at) {
                    $data['served_at'] = now();
                    $data['served_by'] = auth()->id();
                }
                if ($data['status'] === PatientVisit::STATUS_DONE && ! $visit->completed_at) {
                    $data['completed_at'] = now();
                }
            }

            $visit->update($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()
                ->with('flash', Flash::err('Gagal update kunjungan: ' . $e->getMessage()));
        }

        return redirect()->route('admin.visits.show', $visit)
            ->with('flash', Flash::ok('Kunjungan diperbarui.'));
    }

    /* ============================== DESTROY (cancel) ============================== */
    public function destroy(Request $request, PatientVisit $visit): RedirectResponse
    {
        if (! auth()->user()?->hasPermission('visits.delete')) {
            return back()->with('flash', Flash::err('Anda tidak punya akses cancel kunjungan.'));
        }

        try {
            $visit->update([
                'status'        => PatientVisit::STATUS_CANCELLED,
                'cancel_reason' => $request->input('reason', 'Dibatalkan oleh admin'),
            ]);
            $visit->delete(); // soft delete
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash', Flash::err('Gagal cancel: ' . $e->getMessage()));
        }

        return redirect()->route('admin.visits.index')
            ->with('flash', Flash::ok("Kunjungan <b>{$visit->no_register}</b> dibatalkan."));
    }

    /* ============================== QUICK STATUS UPDATE ============================== */
    public function setStatus(Request $request, PatientVisit $visit)
    {
        $status = $request->input('status');
        $valid  = array_keys(PatientVisit::statuses());
        if (! in_array($status, $valid, true)) {
            return $this->fail('Status tidak valid.');
        }

        $data = ['status' => $status];
        if ($status === PatientVisit::STATUS_IN_SERVICE && ! $visit->served_at) {
            $data['served_at'] = now();
            $data['served_by'] = auth()->id();
        }
        if ($status === PatientVisit::STATUS_DONE && ! $visit->completed_at) {
            $data['completed_at'] = now();
        }

        $visit->update($data);

        return $this->ok([
            'status'       => $visit->fresh()->status,
            'status_label' => $visit->fresh()->status_label,
            'status_color' => $visit->fresh()->status_color,
        ], 'Status kunjungan diperbarui.');
    }

    /* ============================== AJAX CHECK HISTORY ============================== */
    /** Cek riwayat kunjungan pasien per kategori — untuk auto-set visit_type ke "kontrol" */
    public function ajaxCheckHistory(Request $request)
    {
        $patientId = (int) $request->input('patient_id');
        $category  = $request->input('category');
        if (! $patientId || ! in_array($category, ['A','I','K','R'], true)) {
            return $this->ok(null);
        }

        $query = PatientVisit::withTrashed()
            ->where('patient_id', $patientId)
            ->where('category', $category);

        $count = (clone $query)->count();
        $last  = (clone $query)->orderByDesc('visit_time')->first(['no_register','visit_date','status','chief_complaint']);

        return $this->ok([
            'count'              => $count,
            'has_history'        => $count > 0,
            'suggested_type'     => $count > 0 ? 'kontrol' : 'baru',
            'last_no_register'   => $last?->no_register,
            'last_visit_date'    => $last?->visit_date?->isoFormat('D MMM YYYY'),
            'last_status'        => $last?->status,
            'last_complaint'     => $last?->chief_complaint,
        ]);
    }

    /* ============================== AJAX SEARCH PASIEN ============================== */
    public function searchPatient(Request $request)
    {
        $term = $request->input('q');
        if (! $term || strlen($term) < 2) return $this->ok([]);

        $rows = Patient::search($term)
            ->with('payerType')
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'no_rm', 'nik', 'name', 'birth_date', 'gender', 'phone', 'address', 'payer_type_id']);

        return $this->ok($rows->map(fn ($p) => [
            'id'       => $p->id,
            'no_rm'    => $p->no_rm,
            'nik'      => $p->nik,
            'name'     => $p->name,
            'gender'   => $p->gender,
            'age'      => $p->age,
            'phone'    => $p->phone,
            'address'  => $p->address,
            'payer'    => optional($p->payerType)->name,
        ]));
    }

    /* ============================== HELPERS ============================== */
    protected function nextQueueNumber(int $siteId, string $category, string $date): int
    {
        return ((int) PatientVisit::withTrashed()
            ->where('site_id', $siteId)
            ->where('category', $category)
            ->whereDate('visit_date', $date)
            ->max('queue_number')) + 1;
    }
}
