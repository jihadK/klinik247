<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ChildVisit;
use App\Models\Delivery;
use App\Models\ImmunizationRecord;
use App\Models\KbAcceptor;
use App\Models\KbVisit;
use App\Models\Neonate;
use App\Models\NeonatalVisit;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PostnatalVisit;
use App\Models\Pregnancy;
use App\Models\AncVisit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    use ApiResponse;

    /* ============================== AJAX SUGGESTION ============================== */
    public function ajaxSuggest(Request $request)
    {
        $term = trim($request->input('q', ''));
        if (mb_strlen($term) < 2) return $this->ok([]);

        $like = '%' . $term . '%';
        $rows = Patient::where(function ($q) use ($like) {
                $q->where('no_rm', 'ilike', $like)
                  ->orWhere('nik', 'ilike', $like)
                  ->orWhere('no_bpjs', 'ilike', $like)
                  ->orWhere('no_kk', 'ilike', $like)
                  ->orWhere('name', 'ilike', $like)
                  ->orWhere('phone', 'ilike', $like);
            })
            ->orderBy('name')
            ->limit(12)
            ->get(['id', 'no_rm', 'nik', 'no_bpjs', 'name', 'birth_date', 'gender', 'phone']);

        return $this->ok($rows->map(fn ($p) => [
            'id'      => $p->id,
            'no_rm'   => $p->no_rm,
            'nik'     => $p->nik,
            'no_bpjs' => $p->no_bpjs,
            'name'    => $p->name,
            'gender'  => $p->gender,
            'age'     => $p->age,
            'phone'   => $p->phone,
            'url'     => route('admin.rm.show', $p),
        ]));
    }

    /* ============================== INDEX (search) ============================== */
    public function index(Request $request): View
    {
        $patients = collect();

        if ($request->filled('q') || $request->filled('date')) {
            $q = $request->input('q');

            $query = Patient::with(['payerType', 'village']);

            if ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('no_rm', 'ilike', $like)
                       ->orWhere('nik', 'ilike', $like)
                       ->orWhere('no_bpjs', 'ilike', $like)
                       ->orWhere('no_kk', 'ilike', $like)
                       ->orWhere('name', 'ilike', $like)
                       ->orWhere('phone', 'ilike', $like);
                });
            }

            if ($request->filled('date')) {
                $date = $request->input('date');
                // Search patients who had visit on this date
                $query->whereHas('createdBy', function () {}, '>=', 0); // no-op base
                $patientIds = PatientVisit::whereDate('visit_date', $date)->pluck('patient_id')->unique();
                $query->whereIn('id', $patientIds);
            }

            $patients = $query->limit(50)->get();
        }

        return view('admin.medical_record.index', compact('patients'));
    }

    /* ============================== SHOW (rekam medis integrasi) ============================== */
    public function show(Patient $rm): View
    {
        $rm->load(['payerType', 'education', 'religion', 'province', 'regency', 'district', 'village']);

        // Helper: safe query — return empty collection kalau table belum migrate
        $safe = function (callable $cb) {
            try { return $cb(); } catch (\Throwable $e) { return collect(); }
        };

        // ===== Aggregate data dari semua modul (graceful fallback per modul) =====
        $visits      = $safe(fn () => PatientVisit::with('payerType')->where('patient_id', $rm->id)->orderByDesc('visit_time')->get());
        $pregnancies = $safe(fn () => Pregnancy::where('patient_id', $rm->id)->orderByDesc('hpht')->get());
        $deliveries  = $safe(fn () => Delivery::with('pregnancy')->where('patient_id', $rm->id)->orderByDesc('bayi_lahir_at')->get());
        $ancVisits   = $safe(fn () => AncVisit::whereHas('pregnancy', fn ($q) => $q->where('patient_id', $rm->id))->orderByDesc('visit_date')->get());
        $pncVisits   = $safe(fn () => PostnatalVisit::where('patient_id', $rm->id)->orderByDesc('visit_date')->get());
        $kbAcceptors = $safe(fn () => KbAcceptor::with('kontrasepsi')->where('patient_id', $rm->id)->orderByDesc('tanggal_dilayani')->get());
        $kbVisits    = $safe(fn () => KbVisit::whereHas('acceptor', fn ($q) => $q->where('patient_id', $rm->id))->orderByDesc('visit_date')->get());

        // Bayi (jika pasien adalah ibu)
        $neonates    = $safe(fn () => Neonate::with('delivery')->where('patient_id', $rm->id)->orderByDesc('tanggal_lahir')->get());
        $knVisits    = $safe(fn () => NeonatalVisit::with('neonate')->where('patient_id', $rm->id)->orderByDesc('visit_date')->get());
        $immRecords  = $safe(fn () => ImmunizationRecord::with(['immunizationType', 'neonate'])->where('patient_id', $rm->id)->orderByDesc('given_date')->get());
        $childVisits = $safe(fn () => ChildVisit::with('neonate')->where('patient_id', $rm->id)->orderByDesc('visit_date')->get());

        // ===== Build Timeline Kronologis (semua event di-merge) =====
        $timeline = collect();

        foreach ($visits as $v) {
            $timeline->push([
                'date'  => $v->visit_time,
                'type'  => 'visit',
                'title' => 'Kunjungan ' . $v->category_label . ' (' . $v->no_register . ')',
                'detail'=> $v->chief_complaint ?: '-',
                'badge' => $v->status_label,
                'color' => $v->category_color,
                'icon'  => 'ki-calendar-tick',
                'url'   => route('admin.visits.show', $v),
            ]);
        }
        foreach ($pregnancies as $p) {
            $timeline->push([
                'date'  => $p->tanggal_k1,
                'type'  => 'pregnancy_start',
                'title' => 'Kehamilan ' . $p->no_kartu_hamil . ' (' . $p->gpa_label . ')',
                'detail'=> 'HPHT: ' . optional($p->hpht)->isoFormat('D MMM YY') . ' · HPL: ' . optional($p->hpl)->isoFormat('D MMM YY'),
                'badge' => $p->status_label,
                'color' => $p->status_color,
                'icon'  => 'ki-heart-circle',
                'url'   => route('admin.anc.show', $p),
            ]);
        }
        foreach ($ancVisits as $v) {
            $timeline->push([
                'date'  => $v->visit_date,
                'type'  => 'anc_visit',
                'title' => 'Kunjungan ANC',
                'detail'=> 'UK ' . ($v->uk_minggu ?? '-') . ' mg · TD ' . ($v->tekanan_darah ?? '-') . ' · ' . ($v->keluhan ?: ''),
                'color' => 'info',
                'icon'  => 'ki-heart',
                'url'   => null,
            ]);
        }
        foreach ($deliveries as $d) {
            $timeline->push([
                'date'  => $d->bayi_lahir_at ?? $d->masuk_at,
                'type'  => 'delivery',
                'title' => 'Persalinan ' . $d->no_persalinan,
                'detail'=> $d->bayi_jenis_kelamin ? 'Bayi ' . ($d->bayi_jenis_kelamin === 'L' ? 'Laki' : 'Perempuan') . ' · ' . ($d->bayi_bb_gram ?? '-') . ' gr · APGAR ' . ($d->bayi_apgar_1 ?? '-') . '/' . ($d->bayi_apgar_5 ?? '-') : 'Belum lahir',
                'badge' => $d->status_label,
                'color' => $d->status_color,
                'icon'  => 'ki-pulse',
                'url'   => route('admin.inc.show', $d),
            ]);
        }
        foreach ($pncVisits as $v) {
            $timeline->push([
                'date'  => $v->visit_date,
                'type'  => 'pnc',
                'title' => 'Kunjungan Nifas (KF ' . $v->kf_number . ')',
                'detail'=> 'Lokhia: ' . ($v->lokhia ?? '-') . ' · TFU: ' . ($v->tfu_cm ?? '-') . ' cm · TD: ' . ($v->ttv_td ?? '-'),
                'color' => 'warning',
                'icon'  => 'ki-heart',
                'url'   => route('admin.pnc.show', $v->delivery_id),
            ]);
        }
        foreach ($kbAcceptors as $a) {
            $timeline->push([
                'date'  => $a->tanggal_dilayani,
                'type'  => 'kb_start',
                'title' => 'Akseptor KB ' . $a->no_kartu_kb,
                'detail'=> 'Alat: ' . $a->kontrasepsi?->name,
                'badge' => $a->status_label,
                'color' => $a->status_color,
                'icon'  => 'ki-pulse',
                'url'   => route('admin.kb.show', $a),
            ]);
        }
        foreach ($kbVisits as $v) {
            $timeline->push([
                'date'  => $v->visit_date,
                'type'  => 'kb_visit',
                'title' => 'Kunjungan Ulang KB',
                'detail'=> 'BB: ' . ($v->berat_badan ?? '-') . ' · TD: ' . ($v->tekanan_darah ?? '-') . ' · ' . ($v->keluhan ?: ''),
                'color' => 'primary',
                'icon'  => 'ki-pulse',
                'url'   => null,
            ]);
        }
        foreach ($neonates as $n) {
            $timeline->push([
                'date'  => $n->tanggal_lahir,
                'type'  => 'baby_birth',
                'title' => 'Lahir Bayi ' . $n->nama_bayi . ' (' . $n->no_kartu_bayi . ')',
                'detail'=> ($n->jenis_kelamin === 'L' ? 'Laki' : 'Perempuan') . ' · ' . ($n->bb_lahir_gram ?? '-') . ' gr · ' . ($n->pb_lahir_cm ?? '-') . ' cm',
                'badge' => $n->status_label,
                'color' => $n->status_color,
                'icon'  => 'ki-tag',
                'url'   => route('admin.kn.show', $n),
            ]);
        }
        foreach ($knVisits as $v) {
            $timeline->push([
                'date'  => $v->visit_date,
                'type'  => 'kn',
                'title' => 'Kunjungan Neonatus (KN ' . $v->kn_number . ') - ' . $v->neonate?->nama_bayi,
                'detail'=> 'BB: ' . ($v->berat_badan_gram ?? '-') . ' gr · Suhu: ' . ($v->suhu_celcius ?? '-') . '°C · Tali pusat: ' . ($v->tali_pusat ?? '-'),
                'color' => 'info',
                'icon'  => 'ki-baby',
                'url'   => route('admin.kn.show', $v->neonate_id),
            ]);
        }
        foreach ($immRecords as $i) {
            $timeline->push([
                'date'  => $i->given_date,
                'type'  => 'immunization',
                'title' => 'Imunisasi ' . $i->immunizationType?->name . ' Dose ' . $i->dose_number . ' - ' . $i->neonate?->nama_bayi,
                'detail'=> 'Batch: ' . ($i->no_batch ?? '-') . ' · Tempat: ' . ($i->tempat ?? '-'),
                'color' => 'success',
                'icon'  => 'ki-syringe',
                'url'   => $i->neonate_id ? route('admin.child.show', $i->neonate_id) : null,
            ]);
        }
        foreach ($childVisits as $v) {
            $timeline->push([
                'date'  => $v->visit_date,
                'type'  => 'child_visit',
                'title' => 'Kunjungan Anak - ' . $v->neonate?->nama_bayi,
                'detail'=> 'BB: ' . ($v->berat_badan_gram ?? '-') . ' gr · ' . ($v->status_gizi ?? '-') . ' · ' . ($v->keluhan ?: ''),
                'color' => 'success',
                'icon'  => 'ki-tag',
                'url'   => $v->neonate_id ? route('admin.child.show', $v->neonate_id) : null,
            ]);
        }

        // Sort kronologis: terbaru di atas
        $timeline = $timeline->filter(fn ($t) => $t['date'])
                            ->sortByDesc(fn ($t) => $t['date']->timestamp ?? 0)
                            ->values();

        // Stats summary
        $stats = [
            'total_visits'      => $visits->count(),
            'total_pregnancies' => $pregnancies->count(),
            'total_deliveries'  => $deliveries->count(),
            'total_kb'          => $kbAcceptors->count(),
            'total_babies'      => $neonates->count(),
            'total_imm'         => $immRecords->count(),
            'first_visit'       => $visits->last()?->visit_time ?? $pregnancies->last()?->tanggal_k1 ?? $rm->created_date,
            'last_visit'        => $timeline->first()['date'] ?? null,
        ];

        return view('admin.medical_record.show', [
            'patient'     => $rm,
            'timeline'    => $timeline,
            'visits'      => $visits,
            'pregnancies' => $pregnancies,
            'deliveries'  => $deliveries,
            'ancVisits'   => $ancVisits,
            'pncVisits'   => $pncVisits,
            'kbAcceptors' => $kbAcceptors,
            'kbVisits'    => $kbVisits,
            'neonates'    => $neonates,
            'knVisits'    => $knVisits,
            'immRecords'  => $immRecords,
            'childVisits' => $childVisits,
            'stats'       => $stats,
        ]);
    }
}
