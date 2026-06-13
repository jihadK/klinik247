@extends('admin.layouts.app')
@section('title', 'Rekam Medis — '.$patient->name)
@section('page_title', 'Rekam Medis: '.$patient->name)

@section('content')

{{-- ===== HEADER IDENTITAS PASIEN ===== --}}
<div class="card mb-5 border border-2 border-primary">
    <div class="card-body" style="background: linear-gradient(135deg,#dbeafe 0%,#eff6ff 100%);">
        <div class="row g-4 align-items-center">
            <div class="col-md-1 text-center">
                <span class="symbol symbol-80px">
                    @if($patient->photo_url)
                        <img src="{{ asset('storage/'.$patient->photo_url) }}" class="rounded" alt="">
                    @else
                        <span class="symbol-label bg-primary text-white fs-1 fw-bolder">{{ mb_substr($patient->name, 0, 1) }}</span>
                    @endif
                </span>
            </div>
            <div class="col-md-6">
                <h2 class="mb-1">{{ $patient->name }}</h2>
                <div class="d-flex flex-wrap gap-2 fs-7 mb-2">
                    <span class="badge badge-primary">{{ $patient->no_rm }}</span>
                    <span class="badge badge-light-{{ $patient->gender === 'L' ? 'primary' : 'danger' }}">{{ $patient->gender_label }}</span>
                    <span class="text-muted">{{ $patient->age }}</span>
                    @if($patient->payerType)<span class="badge badge-light-info">{{ $patient->payerType->name }}</span>@endif
                </div>
                <div class="fs-8 text-muted">
                    {{ $patient->birth_place }}, {{ optional($patient->birth_date)->isoFormat('D MMM YYYY') }}
                    @if($patient->nik) · NIK: {{ $patient->nik }}@endif
                    @if($patient->no_bpjs) · BPJS: {{ $patient->no_bpjs }}@endif
                    @if($patient->phone) · 📞 {{ $patient->phone }}@endif
                </div>
                @if($patient->village || $patient->district)
                    <div class="fs-8 text-muted mt-1">📍 {{ \Illuminate\Support\Str::limit($patient->full_address, 100) }}</div>
                @endif
            </div>
            <div class="col-md-5">
                <div class="row g-2 text-center">
                    <div class="col-3"><div class="p-2 bg-white rounded shadow-sm">
                        <div class="fs-2 fw-bolder text-primary">{{ $stats['total_visits'] }}</div>
                        <div class="fs-9 text-muted">Kunjungan</div>
                    </div></div>
                    @if($stats['total_pregnancies'] > 0)
                        <div class="col-3"><div class="p-2 bg-white rounded shadow-sm">
                            <div class="fs-2 fw-bolder text-success">{{ $stats['total_pregnancies'] }}</div>
                            <div class="fs-9 text-muted">Kehamilan</div>
                        </div></div>
                    @endif
                    @if($stats['total_deliveries'] > 0)
                        <div class="col-3"><div class="p-2 bg-white rounded shadow-sm">
                            <div class="fs-2 fw-bolder text-warning">{{ $stats['total_deliveries'] }}</div>
                            <div class="fs-9 text-muted">Persalinan</div>
                        </div></div>
                    @endif
                    @if($stats['total_kb'] > 0)
                        <div class="col-3"><div class="p-2 bg-white rounded shadow-sm">
                            <div class="fs-2 fw-bolder text-info">{{ $stats['total_kb'] }}</div>
                            <div class="fs-9 text-muted">KB</div>
                        </div></div>
                    @endif
                    @if($stats['total_babies'] > 0)
                        <div class="col-3"><div class="p-2 bg-white rounded shadow-sm">
                            <div class="fs-2 fw-bolder text-info">{{ $stats['total_babies'] }}</div>
                            <div class="fs-9 text-muted">Bayi</div>
                        </div></div>
                    @endif
                    @if($stats['total_imm'] > 0)
                        <div class="col-3"><div class="p-2 bg-white rounded shadow-sm">
                            <div class="fs-2 fw-bolder text-success">{{ $stats['total_imm'] }}</div>
                            <div class="fs-9 text-muted">Imunisasi</div>
                        </div></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ALERGI & RIWAYAT PENYAKIT (highlight) --}}
        @if($patient->allergies || $patient->chronic_diseases || $patient->medical_history)
            <div class="separator separator-dashed my-3"></div>
            <div class="row g-2">
                @if($patient->allergies)
                    <div class="col-md-4">
                        <div class="p-2 bg-light-danger rounded">
                            <div class="text-muted fs-9 fw-bold text-uppercase">⚠ ALERGI</div>
                            <div class="fs-7 fw-bold text-danger">{{ $patient->allergies }}</div>
                        </div>
                    </div>
                @endif
                @if($patient->chronic_diseases)
                    <div class="col-md-4">
                        <div class="p-2 bg-light-warning rounded">
                            <div class="text-muted fs-9 fw-bold text-uppercase">⚠ PENYAKIT KRONIS</div>
                            <div class="fs-7 fw-bold">{{ $patient->chronic_diseases }}</div>
                        </div>
                    </div>
                @endif
                @if($patient->medical_history)
                    <div class="col-md-4">
                        <div class="p-2 bg-light-info rounded">
                            <div class="text-muted fs-9 fw-bold text-uppercase">📋 RIWAYAT PENYAKIT</div>
                            <div class="fs-7">{{ $patient->medical_history }}</div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="d-flex justify-content-end mt-3 gap-2">
            <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-sm btn-light-primary">
                <i class="ki-outline ki-user fs-3"></i> Profil Lengkap Pasien
            </a>
            <a href="{{ route('admin.patients.kartu', $patient) }}" target="_blank" class="btn btn-sm btn-light-info">
                <i class="ki-outline ki-printer fs-3"></i> Cetak Kartu Pasien
            </a>
            <a href="{{ route('admin.rm.index') }}" class="btn btn-sm btn-light">← Cari Pasien Lain</a>
        </div>
    </div>
</div>

{{-- ===== TABS ===== --}}
<ul class="nav nav-tabs nav-line-tabs fs-6 mb-5">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_timeline">📅 Timeline Kronologis</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_visits">🏥 Kunjungan ({{ $visits->count() }})</a></li>
    @if($pregnancies->count() || $deliveries->count())
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_maternal">🤰 Maternal ({{ $pregnancies->count() + $deliveries->count() }})</a></li>
    @endif
    @if($kbAcceptors->count())
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_kb">💗 KB ({{ $kbAcceptors->count() }})</a></li>
    @endif
    @if($neonates->count())
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_baby">👶 Bayi/Anak ({{ $neonates->count() }})</a></li>
    @endif
    @if($immRecords->count())
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_imm">💉 Imunisasi ({{ $immRecords->count() }})</a></li>
    @endif
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_docs">📝 Surat & Dokumen</a></li>
</ul>

<div class="tab-content">

    {{-- ===== TAB 1: TIMELINE KRONOLOGIS ===== --}}
    <div class="tab-pane fade show active" id="tab_timeline">
        <div class="card">
            <div class="card-header"><h3 class="card-title">📅 Timeline Kronologis Semua Pelayanan ({{ $timeline->count() }} event)</h3></div>
            <div class="card-body">
                @if($timeline->isEmpty())
                    <div class="text-center text-muted py-10">Belum ada riwayat pelayanan tercatat.</div>
                @else
                    <div class="timeline">
                        @foreach($timeline as $t)
                            <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                                <div class="text-end" style="min-width:110px;">
                                    <div class="fw-bold fs-7">{{ $t['date']->isoFormat('D MMM YY') }}</div>
                                    <div class="text-muted fs-9">{{ $t['date']->isoFormat('HH:mm') }}</div>
                                </div>
                                <div class="text-center" style="width:50px;">
                                    <span class="symbol symbol-40px">
                                        <span class="symbol-label bg-light-{{ $t['color'] }}">
                                            <i class="ki-outline {{ $t['icon'] }} fs-2 text-{{ $t['color'] }}"></i>
                                        </span>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold">{{ $t['title'] }}</div>
                                            <div class="fs-8 text-muted">{{ $t['detail'] }}</div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            @if(isset($t['badge']))
                                                <span class="badge badge-light-{{ $t['color'] }} fs-9">{{ $t['badge'] }}</span>
                                            @endif
                                            @if($t['url'])
                                                <a href="{{ $t['url'] }}" class="btn btn-sm btn-icon btn-light-primary" title="Detail">
                                                    <i class="ki-outline ki-arrow-right fs-3"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== TAB 2: KUNJUNGAN ===== --}}
    <div class="tab-pane fade" id="tab_visits">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered fs-7">
                        <thead><tr class="text-muted bg-light">
                            <th>Tgl/Jam</th><th>No. Register</th><th>Kategori</th><th>Pembiayaan</th>
                            <th>Keluhan</th><th>Status</th><th></th>
                        </tr></thead>
                        <tbody>
                            @forelse($visits as $v)
                                <tr>
                                    <td>{{ $v->visit_time?->isoFormat('D MMM YY HH:mm') }}</td>
                                    <td class="font-monospace">{{ $v->no_register }}</td>
                                    <td><span class="badge badge-light-{{ $v->category_color }}">{{ $v->category_label }}</span></td>
                                    <td>{{ optional($v->payerType)->name ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($v->chief_complaint, 60) ?: '-' }}</td>
                                    <td><span class="badge badge-light-{{ $v->status_color }}">{{ $v->status_label }}</span></td>
                                    <td><a href="{{ route('admin.visits.show', $v) }}" class="btn btn-sm btn-icon btn-light-info"><i class="ki-outline ki-eye fs-3"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada kunjungan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TAB 3: MATERNAL ===== --}}
    @if($pregnancies->count() || $deliveries->count())
    <div class="tab-pane fade" id="tab_maternal">
        @if($pregnancies->count())
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-title">🤰 Riwayat Kehamilan ({{ $pregnancies->count() }})</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered fs-7">
                            <thead><tr class="text-muted bg-light">
                                <th>No. Kartu</th><th>GPA</th><th>HPHT</th><th>HPL</th><th>Status</th><th></th>
                            </tr></thead>
                            <tbody>
                                @foreach($pregnancies as $p)
                                    <tr>
                                        <td class="font-monospace">{{ $p->no_kartu_hamil }}</td>
                                        <td><span class="badge badge-light-info">{{ $p->gpa_label }}</span></td>
                                        <td>{{ optional($p->hpht)->isoFormat('D MMM YY') ?? '-' }}</td>
                                        <td>{{ optional($p->hpl)->isoFormat('D MMM YY') ?? '-' }}</td>
                                        <td><span class="badge badge-light-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                                        <td><a href="{{ route('admin.anc.show', $p) }}" class="btn btn-sm btn-icon btn-light-info"><i class="ki-outline ki-eye fs-3"></i></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($deliveries->count())
            <div class="card mb-4">
                <div class="card-header"><h4 class="card-title">🩺 Riwayat Persalinan ({{ $deliveries->count() }})</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered fs-7">
                            <thead><tr class="text-muted bg-light">
                                <th>No. Persalinan</th><th>Tgl Lahir</th><th>Bayi</th><th>APGAR</th><th>Status</th><th></th>
                            </tr></thead>
                            <tbody>
                                @foreach($deliveries as $d)
                                    <tr>
                                        <td class="font-monospace">{{ $d->no_persalinan }}</td>
                                        <td>{{ optional($d->bayi_lahir_at)->isoFormat('D MMM YY HH:mm') ?? '-' }}</td>
                                        <td>{{ $d->bayi_jenis_kelamin === 'L' ? '♂' : ($d->bayi_jenis_kelamin === 'P' ? '♀' : '-') }} {{ $d->bayi_bb_gram ?? '-' }} gr</td>
                                        <td>{{ $d->bayi_apgar_1 ?? '-' }}/{{ $d->bayi_apgar_5 ?? '-' }}</td>
                                        <td><span class="badge badge-light-{{ $d->status_color }}">{{ $d->status_label }}</span></td>
                                        <td><a href="{{ route('admin.inc.show', $d) }}" class="btn btn-sm btn-icon btn-light-info"><i class="ki-outline ki-eye fs-3"></i></a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ===== TAB 4: KB ===== --}}
    @if($kbAcceptors->count())
    <div class="tab-pane fade" id="tab_kb">
        <div class="card">
            <div class="card-header"><h4 class="card-title">💗 Riwayat KB ({{ $kbAcceptors->count() }} akseptor)</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered fs-7">
                        <thead><tr class="text-muted bg-light">
                            <th>No. Kartu KB</th><th>Alat</th><th>Tgl Dilayani</th><th>Tgl Dilepas</th><th>Status</th><th></th>
                        </tr></thead>
                        <tbody>
                            @foreach($kbAcceptors as $a)
                                <tr>
                                    <td class="font-monospace">{{ $a->no_kartu_kb }}</td>
                                    <td><span class="badge badge-light-info">{{ $a->kontrasepsi?->name }}</span></td>
                                    <td>{{ optional($a->tanggal_dilayani)->isoFormat('D MMM YY') }}</td>
                                    <td>{{ optional($a->tanggal_dilepas)->isoFormat('D MMM YY') ?? '-' }}</td>
                                    <td><span class="badge badge-light-{{ $a->status_color }}">{{ $a->status_label }}</span></td>
                                    <td><a href="{{ route('admin.kb.show', $a) }}" class="btn btn-sm btn-icon btn-light-info"><i class="ki-outline ki-eye fs-3"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== TAB 5: BAYI ===== --}}
    @if($neonates->count())
    <div class="tab-pane fade" id="tab_baby">
        <div class="card">
            <div class="card-header"><h4 class="card-title">👶 Bayi/Anak yang Dilahirkan ({{ $neonates->count() }})</h4></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($neonates as $n)
                        <div class="col-md-6">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="symbol symbol-50px">
                                            <span class="symbol-label bg-light-{{ $n->jenis_kelamin === 'L' ? 'primary' : 'danger' }} text-{{ $n->jenis_kelamin === 'L' ? 'primary' : 'danger' }} fs-1">
                                                {{ $n->jenis_kelamin === 'L' ? '♂' : '♀' }}
                                            </span>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $n->nama_bayi }}</div>
                                            <div class="fs-8 text-muted font-monospace">{{ $n->no_kartu_bayi }}</div>
                                            <div class="fs-8 mt-1">
                                                Lahir: {{ optional($n->tanggal_lahir)->isoFormat('D MMM YY') }}<br>
                                                BB: {{ $n->bb_lahir_gram ?? '-' }} gr · PB: {{ $n->pb_lahir_cm ?? '-' }} cm<br>
                                                Umur sekarang: <b>{{ $n->umur_label }}</b>
                                            </div>
                                            <span class="badge badge-light-{{ $n->status_color }} fs-9 mt-1">{{ $n->status_label }}</span>
                                        </div>
                                        <a href="{{ route('admin.child.show', $n) }}" class="btn btn-sm btn-icon btn-light-info"><i class="ki-outline ki-arrow-right fs-3"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== TAB 6: IMUNISASI ===== --}}
    @if($immRecords->count())
    <div class="tab-pane fade" id="tab_imm">
        <div class="card">
            <div class="card-header"><h4 class="card-title">💉 Riwayat Imunisasi Anak ({{ $immRecords->count() }} dose)</h4></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered fs-7">
                        <thead><tr class="text-muted bg-light">
                            <th>Tgl</th><th>Bayi/Anak</th><th>Jenis</th><th>Dose</th><th>Batch</th><th>Tempat</th>
                        </tr></thead>
                        <tbody>
                            @foreach($immRecords as $i)
                                <tr>
                                    <td>{{ $i->given_date?->isoFormat('D MMM YY') }}</td>
                                    <td>{{ $i->neonate?->nama_bayi ?? '-' }}</td>
                                    <td><span class="badge badge-light-info">{{ $i->immunizationType?->name }}</span></td>
                                    <td>Dose {{ $i->dose_number }}</td>
                                    <td class="font-monospace fs-9">{{ $i->no_batch ?? '-' }}</td>
                                    <td>{{ $i->tempat ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== TAB 7: SURAT & DOKUMEN ===== --}}
    <div class="tab-pane fade" id="tab_docs">
        <div class="card">
            <div class="card-header"><h4 class="card-title">📝 Surat & Dokumen Pendukung</h4></div>
            <div class="card-body">
                <div class="row g-3">
                    @php $rujukans = $deliveries->where('status', 'rujuk'); @endphp
                    @forelse($rujukans as $d)
                        <div class="col-md-6">
                            <div class="card border border-danger">
                                <div class="card-body">
                                    <h5 class="text-danger">📋 Surat Rujukan</h5>
                                    <div class="fs-8 text-muted">{{ $d->no_persalinan }}</div>
                                    <div class="fs-7 mt-2">
                                        <b>Tujuan:</b> {{ $d->rujukan_ke ?? '-' }}<br>
                                        <b>Tgl:</b> {{ optional($d->masuk_at)->isoFormat('D MMM YYYY') }}<br>
                                        <b>Alasan:</b> {{ \Illuminate\Support\Str::limit($d->rujukan_alasan, 80) }}
                                    </div>
                                    <a href="{{ route('admin.inc.surat-rujukan', $d) }}" target="_blank" class="btn btn-sm btn-danger mt-2 w-100">
                                        <i class="ki-outline ki-printer fs-3"></i> Cetak Surat Rujukan
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5">Belum ada surat rujukan untuk pasien ini.</div>
                    @endforelse
                </div>

                <div class="separator separator-dashed my-4"></div>
                <div class="alert alert-light fs-8">
                    <i class="ki-outline ki-information-5 fs-2 me-2"></i>
                    <b>Catatan:</b> Modul tambahan untuk <b>Hasil Lab</b>, <b>Radiologi</b>, <b>Resep Obat</b>, dan <b>Upload Dokumen</b> akan ditambahkan di Phase berikutnya (Apotik + Kasir + Lab).
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')<x-sweet-flash />@endpush
