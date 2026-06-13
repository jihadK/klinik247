<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pnc\StorePostnatalVisitRequest;
use App\Models\Delivery;
use App\Models\PostnatalVisit;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PncController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX (list ibu nifas — group by delivery) ============================== */
    public function index(Request $request): View
    {
        // Ambil deliveries yang sudah selesai → ibu nifas
        $deliveries = Delivery::with(['patient', 'pregnancy'])
            ->whereIn('status', [Delivery::STATUS_SELESAI, Delivery::STATUS_KALA_IV, 'rujuk'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%' . $request->input('q') . '%';
                $q->whereHas('patient', fn ($pq) => $pq->where('name', 'ilike', $like)->orWhere('no_rm', 'ilike', $like));
            })
            ->orderByDesc('bayi_lahir_at')
            ->paginate(20)
            ->withQueryString();

        // Hitung KF count per delivery
        $kfCounts = PostnatalVisit::whereIn('delivery_id', $deliveries->pluck('id'))
            ->selectRaw('delivery_id, COUNT(*) as total')
            ->groupBy('delivery_id')
            ->pluck('total', 'delivery_id');

        return view('admin.pnc.index', [
            'deliveries' => $deliveries,
            'kfCounts'   => $kfCounts,
            'kfPeriods'  => PostnatalVisit::kfPeriods(),
        ]);
    }

    /* ============================== SHOW (detail ibu nifas + 4 KF) ============================== */
    public function show(Delivery $pnc): View
    {
        $pnc->load(['patient.payerType', 'pregnancy', 'servedBy']);
        $visits = PostnatalVisit::with('servedBy')
            ->where('delivery_id', $pnc->id)
            ->orderBy('kf_number')
            ->get()
            ->keyBy('kf_number');

        return view('admin.pnc.show', [
            'delivery'      => $pnc,
            'visits'        => $visits,
            'kfPeriods'     => PostnatalVisit::kfPeriods(),
            'nasehatItems'  => PostnatalVisit::nasehatItems(),
            'lokhiaOptions' => PostnatalVisit::lokhiaOptions(),
            'statusOptions' => PostnatalVisit::statusOptions(),
        ]);
    }

    /* ============================== STORE VISIT ============================== */
    public function storeVisit(StorePostnatalVisitRequest $request, Delivery $pnc): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['delivery_id'] = $pnc->id;
            $data['pregnancy_id'] = $pnc->pregnancy_id;
            $data['patient_id'] = $pnc->patient_id;
            $data['site_id'] = $pnc->site_id;
            $data['created_by'] = auth()->id();
            $data['served_by'] = auth()->id();

            PostnatalVisit::updateOrCreate(
                ['delivery_id' => $pnc->id, 'kf_number' => $data['kf_number']],
                $data
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('flash', Flash::err('Gagal: ' . $e->getMessage()));
        }
        return redirect()->route('admin.pnc.show', $pnc)
            ->with('flash', Flash::ok("KF {$data['kf_number']} tercatat."));
    }

    public function destroyVisit(PostnatalVisit $visit): RedirectResponse
    {
        $deliveryId = $visit->delivery_id;
        $visit->delete();
        return redirect()->route('admin.pnc.show', $deliveryId)
            ->with('flash', Flash::ok('Catatan KF dihapus.'));
    }
}
