<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Child\StoreChildVisitRequest;
use App\Http\Requests\Child\StoreImmunizationRequest;
use App\Models\ChildVisit;
use App\Models\ImmunizationRecord;
use App\Models\ImmunizationType;
use App\Models\Neonate;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildController extends Controller
{
    use ApiResponse;

    /* ============================== INDEX ============================== */
    public function index(Request $request): View
    {
        $children = Neonate::with(['patient'])
            ->search($request->input('q'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Imunisasi count per neonate
        $immCounts = ImmunizationRecord::whereIn('neonate_id', $children->pluck('id'))
            ->selectRaw('neonate_id, COUNT(*) as total')
            ->groupBy('neonate_id')
            ->pluck('total', 'neonate_id');

        // Visits count
        $visitCounts = ChildVisit::whereIn('neonate_id', $children->pluck('id'))
            ->selectRaw('neonate_id, COUNT(*) as total')
            ->groupBy('neonate_id')
            ->pluck('total', 'neonate_id');

        return view('admin.child.index', [
            'children'    => $children,
            'immCounts'   => $immCounts,
            'visitCounts' => $visitCounts,
        ]);
    }

    /* ============================== SHOW ============================== */
    public function show(Neonate $child): View
    {
        $child->load(['patient', 'delivery', 'immunizations.immunizationType', 'immunizations.givenBy', 'childVisits.servedBy']);

        // Build matrix imunisasi (type × dose)
        $allTypes = ImmunizationType::active()->orderBy('sort_order')->get();
        $matrix = [];
        foreach ($allTypes as $type) {
            $matrix[$type->id] = [
                'type'  => $type,
                'doses' => [],
            ];
            for ($d = 1; $d <= $type->max_dose; $d++) {
                $matrix[$type->id]['doses'][$d] = $child->immunizations
                    ->where('immunization_type_id', $type->id)
                    ->where('dose_number', $d)
                    ->first();
            }
        }

        return view('admin.child.show', [
            'child'               => $child,
            'matrix'              => $matrix,
            'visitTypeOptions'    => ChildVisit::visitTypeOptions(),
            'statusGiziOptions'   => ChildVisit::statusGiziOptions(),
            'perkembanganOptions' => ChildVisit::perkembanganOptions(),
        ]);
    }

    /* ============================== STORE IMUNISASI ============================== */
    public function storeImmunization(StoreImmunizationRequest $request, Neonate $child): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['neonate_id'] = $child->id;
            $data['patient_id'] = $child->patient_id;
            $data['site_id']    = $child->site_id;
            $data['given_by']   = auth()->id();
            $data['created_by'] = auth()->id();

            ImmunizationRecord::updateOrCreate(
                ['neonate_id' => $child->id, 'immunization_type_id' => $data['immunization_type_id'], 'dose_number' => $data['dose_number']],
                $data
            );
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash', Flash::err('Gagal: ' . $e->getMessage()));
        }
        return redirect()->route('admin.child.show', $child)
            ->with('flash', Flash::ok('Pemberian imunisasi tercatat.'));
    }

    public function destroyImmunization(ImmunizationRecord $record): RedirectResponse
    {
        $neonateId = $record->neonate_id;
        $record->delete();
        return redirect()->route('admin.child.show', $neonateId)
            ->with('flash', Flash::ok('Record imunisasi dihapus.'));
    }

    /* ============================== STORE KUNJUNGAN ANAK ============================== */
    public function storeVisit(StoreChildVisitRequest $request, Neonate $child): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['neonate_id'] = $child->id;
            $data['patient_id'] = $child->patient_id;
            $data['site_id']    = $child->site_id;
            $data['created_by'] = auth()->id();
            $data['served_by']  = auth()->id();

            // Auto-calc umur saat visit
            if ($child->tanggal_lahir) {
                $visitDate = \Carbon\Carbon::parse($data['visit_date']);
                $data['umur_hari'] = (int) $child->tanggal_lahir->diffInDays($visitDate);
                $data['umur_label'] = $this->buildUmurLabel($data['umur_hari']);
            }

            ChildVisit::create($data);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('flash', Flash::err('Gagal: ' . $e->getMessage()));
        }
        return redirect()->route('admin.child.show', $child)
            ->with('flash', Flash::ok('Kunjungan anak tercatat.'));
    }

    public function destroyVisit(ChildVisit $visit): RedirectResponse
    {
        $neonateId = $visit->neonate_id;
        $visit->delete();
        return redirect()->route('admin.child.show', $neonateId)
            ->with('flash', Flash::ok('Kunjungan dihapus.'));
    }

    protected function buildUmurLabel(int $days): string
    {
        if ($days < 30)  return $days . ' hari';
        if ($days < 365) {
            $months = (int) floor($days / 30);
            return $months . ' bulan' . ($days % 30 > 0 ? ' ' . ($days % 30) . ' hari' : '');
        }
        $years = (int) floor($days / 365);
        $remDays = $days - ($years * 365);
        $months = (int) floor($remDays / 30);
        return $years . ' tahun' . ($months > 0 ? ' ' . $months . ' bulan' : '');
    }
}
