<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kn\StoreNeonatalVisitRequest;
use App\Http\Requests\Kn\UpdateNeonateRequest;
use App\Models\Neonate;
use App\Models\NeonatalVisit;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KnController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX ============================== */
    public function index(Request $request): View
    {
        $neonates = Neonate::with(['patient'])
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // KN count per neonate
        $knCounts = NeonatalVisit::whereIn('neonate_id', $neonates->pluck('id'))
            ->selectRaw('neonate_id, COUNT(*) as total')
            ->groupBy('neonate_id')
            ->pluck('total', 'neonate_id');

        return view('admin.kn.index', [
            'neonates'  => $neonates,
            'knCounts'  => $knCounts,
            'knPeriods' => NeonatalVisit::knPeriods(),
            'statuses'  => Neonate::statuses(),
        ]);
    }

    /* ============================== SHOW (detail bayi + 3 KN) ============================== */
    public function show(Neonate $kn): View
    {
        $kn->load(['patient', 'delivery', 'pregnancy', 'visits.servedBy', 'createdBy']);
        $visits = $kn->visits->keyBy('kn_number');

        return view('admin.kn.show', [
            'neonate'              => $kn,
            'visits'               => $visits,
            'knPeriods'            => NeonatalVisit::knPeriods(),
            'taliPusatOptions'     => NeonatalVisit::taliPusatOptions(),
            'menyusuOptions'       => NeonatalVisit::menyusuOptions(),
            'tandaBahayaItems'     => NeonatalVisit::tandaBahayaItems(),
            'statuses'             => Neonate::statuses(),
        ]);
    }

    /* ============================== EDIT NEONATE HEADER ============================== */
    public function edit(Neonate $kn): View
    {
        return view('admin.kn.edit', [
            'neonate'  => $kn,
            'statuses' => Neonate::statuses(),
        ]);
    }

    public function update(UpdateNeonateRequest $request, Neonate $kn): RedirectResponse
    {
        try {
            $kn->update($request->validated());
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal: ' . $e->getMessage()));
        }
        return redirect()->route('admin.kn.show', $kn)
            ->with('flash', Flash::ok('Data bayi diperbarui.'));
    }

    /* ============================== STORE/UPDATE KN VISIT ============================== */
    public function storeVisit(StoreNeonatalVisitRequest $request, Neonate $kn): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['neonate_id'] = $kn->id;
            $data['patient_id'] = $kn->patient_id;
            $data['site_id']    = $kn->site_id;
            $data['created_by'] = auth()->id();
            $data['served_by']  = auth()->id();

            NeonatalVisit::updateOrCreate(
                ['neonate_id' => $kn->id, 'kn_number' => $data['kn_number']],
                $data
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal: ' . $e->getMessage()));
        }
        return redirect()->route('admin.kn.show', $kn)
            ->with('flash', Flash::ok("KN {$data['kn_number']} tercatat."));
    }

    public function destroyVisit(NeonatalVisit $visit): RedirectResponse
    {
        $neonateId = $visit->neonate_id;
        $visit->delete();
        return redirect()->route('admin.kn.show', $neonateId)
            ->with('flash', Flash::ok('Catatan KN dihapus.'));
    }
}
