@extends('admin.layouts.app')
@section('title', 'Detail Kehamilan — '.$pregnancy->no_kartu_hamil)
@section('page_title', 'Detail Kehamilan (ANC)')

@section('content')

@if($pregnancy->status !== 'aktif')
    @php
        $statusInfo = [
            'partus'  => ['icon' => 'ki-baby',         'color' => 'primary', 'title' => 'Kehamilan Sudah Bersalin'],
            'abortus' => ['icon' => 'ki-cross-circle', 'color' => 'danger',  'title' => 'Kehamilan Abortus'],
            'rujuk'   => ['icon' => 'ki-arrow-right-square', 'color' => 'warning', 'title' => 'Pasien Dirujuk'],
            'lost'    => ['icon' => 'ki-question-2',   'color' => 'secondary','title' => 'Lost Follow-up'],
        ];
        $info = $statusInfo[$pregnancy->status] ?? ['icon' => 'ki-information-5', 'color' => 'secondary', 'title' => 'Status Tidak Aktif'];
    @endphp
    <div class="alert alert-{{ $info['color'] }} d-flex align-items-start gap-4 p-5 mb-5 border border-{{ $info['color'] }} border-2">
        <i class="ki-outline {{ $info['icon'] }} fs-3x text-{{ $info['color'] }}"></i>
        <div class="flex-grow-1">
            <h4 class="mb-2 text-{{ $info['color'] }}"><i class="ki-outline ki-lock fs-2 me-1"></i> {{ $info['title'] }} — Data Terkunci</h4>
            <div class="row g-3 mb-2">
                <div class="col-md-4"><div class="text-muted fs-8 fw-semibold text-uppercase">Tanggal Selesai</div><div class="fw-bold fs-6">{{ optional($pregnancy->tanggal_selesai)->isoFormat('D MMM YYYY') ?: '-' }}</div></div>
                <div class="col-md-8"><div class="text-muted fs-8 fw-semibold text-uppercase">Keterangan Akhir</div><div class="fw-bold fs-6">{{ $pregnancy->keterangan_akhir ?: '-' }}</div></div>
            </div>
            <div class="fs-8 text-muted mt-2"><i class="ki-outline ki-shield-tick fs-4 me-1"></i> Data terkunci. Edit & tambah ANC tidak diperbolehkan.</div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card mb-5">
            <div class="card-body text-center">
                <span class="badge badge-light-{{ $pregnancy->status_color }} fs-6 mb-3">{{ $pregnancy->status_label }}</span>
                <h3 class="mb-1">{{ $pregnancy->patient?->name }}</h3>
                <div class="text-muted fs-7">{{ $pregnancy->patient?->no_rm }} · {{ $pregnancy->patient?->age }}</div>
                <div class="fs-2x fw-bolder text-success mt-3 font-monospace">{{ $pregnancy->no_kartu_hamil }}</div>

                {{-- Highlight GPA + UK + Trimester --}}
                <div class="my-4 p-3 rounded text-white shadow-sm" style="background: linear-gradient(135deg, #50cd89 0%, #36b37e 100%);">
                    <div class="text-white-50 fs-8 fw-semibold text-uppercase">GPA</div>
                    <div class="fs-2 fw-bolder text-white mb-1">{{ $pregnancy->gpa_label }}</div>
                    @if($pregnancy->uk_sekarang)
                        <div class="text-white-75 fs-7">UK: <b>{{ $pregnancy->uk_sekarang }} minggu</b> · Trim <b>{{ $pregnancy->trimester }}</b></div>
                    @endif
                </div>

                <div class="text-muted fs-7">📅 Tanggal K1: <b class="text-dark">{{ optional($pregnancy->tanggal_k1)->isoFormat('D MMM YYYY') }}</b></div>
                @if($pregnancy->hpht)
                    <div class="text-muted fs-7 mt-1">🩸 HPHT: <b class="text-dark">{{ $pregnancy->hpht->isoFormat('D MMM YYYY') }}</b></div>
                @endif
                @if($pregnancy->hpl)
                    @php $hari = $pregnancy->hari_menuju_hpl; @endphp
                    <div class="text-muted fs-7 mt-1">
                        👶 HPL: <b class="text-dark">{{ $pregnancy->hpl->isoFormat('D MMM YYYY') }}</b>
                        @if($hari !== null)
                            @if($hari < 0)<span class="badge badge-light-danger fs-9">Lewat {{ abs($hari) }} hari</span>
                            @elseif($hari <= 14)<span class="badge badge-light-warning fs-9">{{ $hari }} hari lagi</span>
                            @else<span class="badge badge-light-success fs-9">{{ $hari }} hari lagi</span>
                            @endif
                        @endif
                    </div>
                @endif

                {{-- ===== HIGHLIGHT: Rencana Kunjungan Berikutnya ===== --}}
                @php
                    $nextVisit = $pregnancy->ancVisits->first()?->tanggal_kembali;
                @endphp
                @if($nextVisit && $pregnancy->status === 'aktif')
                    @php $daysToNext = (int) now()->startOfDay()->diffInDays($nextVisit->startOfDay()); @endphp
                    @php
                        $boxColor = $daysToNext < 0 ? 'danger' : ($daysToNext <= 3 ? 'warning' : 'info');
                        $boxIcon  = $daysToNext < 0 ? 'ki-alarm' : ($daysToNext <= 3 ? 'ki-time' : 'ki-calendar-tick');
                        $boxLabel = $daysToNext < 0
                            ? '⚠ TERLAMBAT ' . abs($daysToNext) . ' HARI'
                            : ($daysToNext === 0 ? 'HARI INI' : ($daysToNext === 1 ? 'BESOK' : $daysToNext . ' hari lagi'));
                    @endphp
                    <div class="mt-4 p-3 rounded text-white shadow-sm position-relative overflow-hidden"
                         style="background: linear-gradient(135deg, {{ $daysToNext < 0 ? '#dc2626' : ($daysToNext <= 3 ? '#f59e0b' : '#0ea5e9') }} 0%, {{ $daysToNext < 0 ? '#991b1b' : ($daysToNext <= 3 ? '#d97706' : '#0284c7') }} 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <i class="ki-outline {{ $boxIcon }} fs-3x text-white opacity-75"></i>
                            <div class="text-start flex-grow-1">
                                <div class="text-white-50 fs-8 fw-semibold text-uppercase ls-1">🗓 Kunjungan Berikutnya</div>
                                <div class="fs-4 fw-bolder text-white">{{ $nextVisit->isoFormat('dddd, D MMM YYYY') }}</div>
                                <div class="fs-7 fw-bold text-white">{{ $boxLabel }}</div>
                            </div>
                        </div>
                    </div>
                @elseif($pregnancy->status === 'aktif')
                    <div class="mt-4 p-3 rounded bg-light-warning border border-warning">
                        <div class="d-flex align-items-center gap-2 text-warning">
                            <i class="ki-outline ki-information-5 fs-2x"></i>
                            <div>
                                <div class="fw-bold fs-7">Belum Ada Jadwal Kunjungan</div>
                                <div class="fs-9">Tambah kunjungan ANC dengan mengisi <b>Tgl Kembali</b>.</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                    <a href="{{ route('admin.anc.kartu', $pregnancy) }}" target="_blank" class="btn btn-sm btn-light-info">
                        <i class="ki-outline ki-printer fs-3"></i> Cetak
                    </a>
                    @if(auth()->user()->hasPermission('anc.update') && $pregnancy->status === 'aktif')
                        <a href="{{ route('admin.anc.edit', $pregnancy) }}" class="btn btn-sm btn-light-warning">
                            <i class="ki-outline ki-pencil fs-3"></i> Edit
                        </a>
                    @endif
                </div>

                {{-- ===== Tombol Mulai Persalinan (kehamilan aktif + UK >= 36 mg) ===== --}}
                @if(auth()->user()->hasPermission('inc.create') && $pregnancy->status === 'aktif')
                    @php
                        $existingDelivery = \App\Models\Delivery::where('pregnancy_id', $pregnancy->id)->first();
                        $ukNow = $pregnancy->uk_sekarang;
                    @endphp
                    @if($existingDelivery)
                        <a href="{{ route('admin.inc.show', $existingDelivery) }}" class="btn btn-warning w-100 mt-3">
                            <i class="ki-outline ki-pulse fs-3"></i> Lihat Persalinan ({{ $existingDelivery->no_persalinan }})
                        </a>
                    @else
                        <a href="{{ route('admin.inc.create', ['pregnancy_id' => $pregnancy->id]) }}"
                           id="btn_mulai_persalinan"
                           data-uk="{{ $ukNow ?? '' }}"
                           class="btn btn-danger w-100 mt-3">
                            <i class="ki-outline ki-pulse fs-3"></i> Mulai Persalinan (INC)
                            @if($ukNow !== null && $ukNow >= 37)
                                <span class="badge badge-light ms-2 fs-9">Aterm</span>
                            @elseif($ukNow !== null && $ukNow >= 36)
                                <span class="badge badge-warning ms-2 fs-9">Awal</span>
                            @endif
                        </a>
                    @endif
                @endif
                <a href="{{ route('admin.anc.index') }}" class="btn btn-sm btn-light w-100 mt-2">← Kembali</a>
            </div>
        </div>

        @if($pregnancy->suami_nama)
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Identitas Suami</h3></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Nama</span><b>{{ $pregnancy->suami_nama }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Umur</span><b>{{ $pregnancy->suami_umur ?? '-' }} th</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Pendidikan</span><b>{{ optional($pregnancy->suamiEducation)->name ?? '-' }}</b></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Pekerjaan</span><b>{{ $pregnancy->suami_pekerjaan ?? '-' }}</b></div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- Pemeriksaan K1 --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">C. Pemeriksaan K1</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><div class="text-muted fs-7">TB</div><div class="fw-semibold">{{ $pregnancy->tinggi_badan_cm ?? '-' }} cm</div></div>
                    <div class="col-md-3"><div class="text-muted fs-7">BB Awal</div><div class="fw-semibold">{{ $pregnancy->berat_badan_awal ?? '-' }} kg</div></div>
                    <div class="col-md-3"><div class="text-muted fs-7">LILA</div><div class="fw-semibold">{{ $pregnancy->lila_cm ?? '-' }} cm @if($pregnancy->lila_cm && $pregnancy->lila_cm < 23.5)<span class="badge badge-light-danger fs-9">KEK</span>@endif</div></div>
                    <div class="col-md-3"><div class="text-muted fs-7">IMT</div><div class="fw-semibold">{{ $pregnancy->imt ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">TD K1</div><div class="fw-semibold">{{ $pregnancy->vital_sign_td ?? '-' }} mmHg</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Rekomendasi Kenaikan BB</div><div class="fw-semibold">{{ $pregnancy->recom_kenaikan_bb ?? '-' }}</div></div>
                </div>
                @if($pregnancy->riwayat_alergi || $pregnancy->riwayat_penyakit || $pregnancy->keluhan_awal)
                    <div class="separator my-3"></div>
                    @if($pregnancy->riwayat_alergi)<div class="mb-2"><div class="text-muted fs-7">Riwayat Alergi</div><div>{{ $pregnancy->riwayat_alergi }}</div></div>@endif
                    @if($pregnancy->riwayat_penyakit)<div class="mb-2"><div class="text-muted fs-7">Riwayat Penyakit</div><div>{{ $pregnancy->riwayat_penyakit }}</div></div>@endif
                    @if($pregnancy->keluhan_awal)<div><div class="text-muted fs-7">Keluhan Awal</div><div>{{ $pregnancy->keluhan_awal }}</div></div>@endif
                @endif
            </div>
        </div>

        {{-- Riwayat Anak Sebelumnya --}}
        @if($pregnancy->histories->count())
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">B. Riwayat Anak Sebelumnya</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gs-0 gy-2 fs-7">
                        <thead><tr class="fw-bold text-muted bg-light">
                            <th>Ke</th><th>Tahun</th><th>JK</th><th>Cara Lahir</th><th>BB</th><th>PB</th><th>Tempat</th><th>Penolong</th><th>Kondisi</th><th>Komplikasi</th>
                        </tr></thead>
                        <tbody>
                            @foreach($pregnancy->histories as $h)
                                <tr>
                                    <td><b>{{ $h->hamil_ke }}</b></td>
                                    <td>{{ $h->tahun ?? '-' }}</td>
                                    <td>{{ $h->jenis_kelamin ?? '-' }}</td>
                                    <td>{{ \App\Models\PregnancyHistory::caraLahirOptions()[$h->cara_lahir] ?? '-' }}</td>
                                    <td>{{ $h->bb_lahir_gram ? $h->bb_lahir_gram . ' g' : '-' }}</td>
                                    <td>{{ $h->pb_lahir_cm ? $h->pb_lahir_cm . ' cm' : '-' }}</td>
                                    <td>{{ \App\Models\PregnancyHistory::tempatBersalinOptions()[$h->tempat_bersalin] ?? '-' }}</td>
                                    <td>{{ \App\Models\PregnancyHistory::penolongOptions()[$h->penolong] ?? '-' }}</td>
                                    <td>{{ \App\Models\PregnancyHistory::kondisiAnakOptions()[$h->kondisi_anak] ?? '-' }}</td>
                                    <td>{{ $h->komplikasi ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- D. Kunjungan ANC --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">D. Kunjungan Kontrol Kehamilan (ANC)</h3>
                @if(auth()->user()->hasPermission('anc.visit') && $pregnancy->status === 'aktif')
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_anc_visit">
                            <i class="ki-outline ki-plus fs-3"></i> Tambah ANC
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive border rounded" style="max-height: 480px; overflow-y: auto; overflow-x: auto;">
                    <table class="table table-row-bordered align-middle gs-0 gy-3 fs-7 mb-0" style="min-width: 1280px;">
                        <thead class="position-sticky top-0 bg-light-secondary" style="z-index: 1;">
                            <tr class="fw-bold text-muted">
                                <th class="ps-3" style="min-width:110px; white-space:nowrap;">Aksi</th>
                                <th style="min-width:90px; white-space:nowrap;">Tgl</th>
                                <th style="min-width:120px; white-space:nowrap;">Tempat</th>
                                <th style="min-width:70px; white-space:nowrap;">UK</th>
                                <th style="min-width:80px; white-space:nowrap;">TFU</th>
                                <th style="min-width:100px; white-space:nowrap;">Letak</th>
                                <th style="min-width:90px; white-space:nowrap;">DJJ</th>
                                <th style="min-width:100px; white-space:nowrap;">BB / TD</th>
                                <th style="min-width:90px; white-space:nowrap;">TT</th>
                                <th style="min-width:180px; white-space:nowrap;">Keluhan</th>
                                <th style="min-width:180px; white-space:nowrap;">Tindakan</th>
                                <th class="pe-3" style="min-width:110px; white-space:nowrap;">Tgl Kembali</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pregnancy->ancVisits as $v)
                                <tr>
                                    {{-- ===== AKSI di kolom PERTAMA ===== --}}
                                    <td class="ps-3">
                                        <button type="button" class="btn btn-sm btn-icon btn-light-info btn-show-anc"
                                                data-bs-toggle="modal" data-bs-target="#modal_anc_detail"
                                                data-no="{{ $loop->iteration }}"
                                                data-data="{{ json_encode($v) }}"
                                                title="Detail">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </button>
                                        @if(auth()->user()->hasPermission('anc.visit') && $pregnancy->status === 'aktif')
                                            <form action="{{ route('admin.anc.visit.destroy', $v) }}" method="POST" class="d-inline form-del-anc">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-icon btn-light-danger" title="Hapus"><i class="ki-outline ki-trash fs-3"></i></button>
                                            </form>
                                        @endif
                                    </td>

                                    <td class="fw-semibold">{{ $v->visit_date?->isoFormat('D MMM YY') }}</td>
                                    <td class="text-muted fs-8">{{ \App\Models\AncVisit::tempatPeriksaOptions()[$v->tempat_periksa] ?? $v->tempat_periksa ?? '-' }}</td>
                                    <td>{{ $v->uk_minggu ? $v->uk_minggu . ' mg' : '-' }}</td>
                                    <td>{{ $v->tfu_cm ? $v->tfu_cm . ' cm' : '-' }}</td>
                                    <td>{{ \App\Models\AncVisit::letakOptions()[$v->letak_janin] ?? '-' }}</td>
                                    <td>{{ $v->djj_per_menit ? $v->djj_per_menit . ' /mnt' : '-' }}</td>
                                    <td>
                                        @if($v->berat_badan_kg)<div>{{ $v->berat_badan_kg }} kg</div>@endif
                                        @if($v->tekanan_darah)<div class="text-muted fs-8">{{ $v->tekanan_darah }}</div>@endif
                                    </td>
                                    <td>
                                        @if($v->status_tt)<span class="badge badge-light-info">{{ $v->status_tt }}</span>@endif
                                        @if($v->pemberian_tt)<div class="text-success fs-9">✓ Diberi</div>@endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($v->keluhan, 40) ?: '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($v->penatalaksanaan ?: $v->terapi, 40) ?: '-' }}</td>
                                    <td class="pe-3 fw-semibold">{{ optional($v->tanggal_kembali)->isoFormat('D MMM YY') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted py-5">Belum ada kunjungan ANC.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="form-text fs-9 mt-2">💡 Scroll horizontal/vertical kalau data banyak. Klik <i class="ki-outline ki-eye fs-4 text-info"></i> untuk lihat detail.</div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Detail ANC Visit (read-only) --}}
<div class="modal fade" id="modal_anc_detail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <h3><i class="ki-outline ki-clipboard fs-2 me-2 text-info"></i> Kunjungan ANC #<span id="ad_no">-</span></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="ad_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah ANC --}}
@php
    // Pre-compute defaults berdasar context
    $lastAnc = $pregnancy->ancVisits->first();        // latest visit (already sorted desc)
    $suggestedTt = \App\Models\AncVisit::suggestNextTt($lastAnc?->status_tt, (bool) $lastAnc?->pemberian_tt);
    $hphtIso = optional($pregnancy->hpht)->format('Y-m-d');

    // BB tracking: dari last visit kalau ada, fallback ke BB Awal K1
    $bbPrev = $lastAnc?->berat_badan_kg ?? $pregnancy->berat_badan_awal;
    $bbAwal = $pregnancy->berat_badan_awal;
    $bbDelta = ($bbPrev && $bbAwal) ? round((float)$bbPrev - (float)$bbAwal, 1) : null;
@endphp
<div class="modal fade" id="modal_anc_visit" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('admin.anc.visit.store', $pregnancy) }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="pregnancy_id" value="{{ $pregnancy->id }}">
            <input type="hidden" id="anc_hpht" value="{{ $hphtIso }}">

            <div class="modal-header">
                <h3>Tambah Kunjungan ANC — Kehamilan {{ $pregnancy->no_kartu_hamil }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- ============ HASIL PEMERIKSAAN SEBELUMNYA (top reference panel) ============ --}}
                <div class="card mb-3 border border-2 border-info">
                    <div class="card-header py-2 px-3 border-0 bg-light-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-info">
                                <i class="ki-outline ki-document-down fs-3 me-1"></i>
                                Hasil Pemeriksaan Sebelumnya — sebagai pembanding
                            </div>
                            <span class="badge badge-info fs-9">
                                @if($lastAnc)
                                    📅 ANC ke-{{ $pregnancy->ancVisits->count() }} · {{ $lastAnc->visit_date?->isoFormat('D MMM YYYY') }}
                                @else
                                    📋 Data K1 ({{ optional($pregnancy->tanggal_k1)->isoFormat('D MMM YYYY') }})
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="card-body py-2 px-3" style="max-height: 200px; overflow-y: auto;">
                        <div class="row g-2 fs-8">
                            {{-- Vital Sign --}}
                            @if($lastAnc?->berat_badan_kg || $bbAwal)
                                <div class="col-md-3">
                                    <div class="p-2 bg-light-success rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">BB Sebelumnya</div>
                                        <div class="fw-bold">{{ $bbPrev ?? '-' }} kg
                                            @if($bbDelta !== null && $bbDelta !== 0.0)
                                                <span class="badge badge-light-{{ $bbDelta > 0 ? 'success' : 'danger' }} fs-9 ms-1">{{ $bbDelta > 0 ? '+' : '' }}{{ $bbDelta }} kg dari awal</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($lastAnc?->tekanan_darah || $pregnancy->vital_sign_td)
                                <div class="col-md-3">
                                    <div class="p-2 bg-light-warning rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">TD Sebelumnya</div>
                                        <div class="fw-bold">{{ $lastAnc?->tekanan_darah ?? $pregnancy->vital_sign_td ?? '-' }} mmHg</div>
                                    </div>
                                </div>
                            @endif

                            @if($lastAnc?->tfu_cm)
                                <div class="col-md-3">
                                    <div class="p-2 bg-light-primary rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">TFU Sebelumnya</div>
                                        <div class="fw-bold">{{ $lastAnc->tfu_cm }} cm @if($lastAnc->uk_minggu)<span class="text-muted fs-9">@ UK {{ $lastAnc->uk_minggu }} mg</span>@endif</div>
                                    </div>
                                </div>
                            @endif

                            @if($lastAnc?->djj_per_menit)
                                <div class="col-md-3">
                                    <div class="p-2 bg-light-danger rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">DJJ Sebelumnya</div>
                                        <div class="fw-bold">{{ $lastAnc->djj_per_menit }} /menit</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Recom Kenaikan BB --}}
                            @if($pregnancy->recom_kenaikan_bb || $pregnancy->imt)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light-info rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Rekomendasi Kenaikan BB</div>
                                        <div class="fw-bold">{{ $pregnancy->recom_kenaikan_bb ?? '-' }}
                                            @if($pregnancy->imt)<span class="text-muted fs-9">· IMT awal: {{ $pregnancy->imt }}</span>@endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($lastAnc?->letak_janin)
                                <div class="col-md-3">
                                    <div class="p-2 bg-light-secondary rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Letak Janin</div>
                                        <div class="fw-bold">{{ \App\Models\AncVisit::letakOptions()[$lastAnc->letak_janin] ?? '-' }}</div>
                                    </div>
                                </div>
                            @endif

                            @if($lastAnc?->status_tt)
                                <div class="col-md-3">
                                    <div class="p-2 bg-light-info rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Status TT Terakhir</div>
                                        <div class="fw-bold">{{ $lastAnc->status_tt }}{{ $lastAnc->pemberian_tt ? ' (diberi)' : '' }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Riwayat Alergi (dari K1) --}}
                            @if($pregnancy->riwayat_alergi)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light-danger rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">⚠ Riwayat Alergi</div>
                                        <div class="fw-bold text-danger">{{ $pregnancy->riwayat_alergi }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Riwayat Penyakit (dari K1) --}}
                            @if($pregnancy->riwayat_penyakit)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light-warning rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">⚠ Riwayat Penyakit</div>
                                        <div class="fw-bold">{{ $pregnancy->riwayat_penyakit }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Keluhan sebelumnya --}}
                            @if($lastAnc?->keluhan || $pregnancy->keluhan_awal)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Keluhan Sebelumnya</div>
                                        <div>{{ $lastAnc?->keluhan ?? $pregnancy->keluhan_awal }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Terapi sebelumnya --}}
                            @if($lastAnc?->terapi)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Terapi Sebelumnya</div>
                                        <div>{{ $lastAnc->terapi }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Penatalaksanaan sebelumnya --}}
                            @if($lastAnc?->penatalaksanaan)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Penatalaksanaan</div>
                                        <div>{{ $lastAnc->penatalaksanaan }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Hasil Lab sebelumnya --}}
                            @if($lastAnc?->hasil_lab)
                                <div class="col-md-6">
                                    <div class="p-2 bg-light rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">Hasil Lab</div>
                                        <div>{{ $lastAnc->hasil_lab }}</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan sebelumnya --}}
                            @if($lastAnc?->notes)
                                <div class="col-md-12">
                                    <div class="p-2 bg-light rounded">
                                        <div class="text-muted text-uppercase fw-bold fs-9">📝 Catatan Sebelumnya</div>
                                        <div>{{ $lastAnc->notes }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-3"><label class="form-label fs-7 required">Tanggal</label>
                        <input type="date" name="visit_date" id="anc_visit_date" value="{{ today()->format('Y-m-d') }}" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">UK (minggu)</label>
                        <input type="number" step="0.1" name="uk_minggu" id="anc_uk" class="form-control form-control-solid" placeholder="auto dari HPHT">
                        <div class="form-text fs-9" id="anc_uk_info">Otomatis dihitung dari HPHT</div>
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Tempat Periksa</label>
                        <select name="tempat_periksa" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="0">
                            @foreach(\App\Models\AncVisit::tempatPeriksaOptions() as $k => $v)
                                <option value="{{ $k }}" @selected($k === 'klinik')>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Tgl Kembali</label>
                        <input type="date" name="tanggal_kembali" id="anc_tgl_kembali" class="form-control form-control-solid">
                        <div class="form-text fs-9" id="anc_kembali_info">Auto sesuai trimester</div>
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>
                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">🩺 Pemeriksaan Obstetri</div>
                <div class="row g-3 mb-2">
                    <div class="col-md-4"><label class="form-label fs-7">TFU (cm)</label>
                        <input type="number" step="0.1" name="tfu_cm" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-4"><label class="form-label fs-7">DJJ /menit</label>
                        <input type="number" name="djj_per_menit" min="60" max="220" class="form-control form-control-solid" placeholder="120-160 normal">
                    </div>
                    <div class="col-md-4"><label class="form-label fs-7">Letak Janin</label>
                        <select name="letak_janin" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                            <option></option>
                            @foreach(\App\Models\AncVisit::letakOptions() as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>
                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">💉 Vital Sign Ibu</div>
                <div class="row g-3 mb-2">
                    <div class="col-md-3"><label class="form-label fs-7">BB (kg)</label>
                        <input type="number" step="0.1" name="berat_badan_kg" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-4"><label class="form-label fs-7">Tekanan Darah</label>
                        <div class="input-group">
                            <input type="number" id="anc_td_s" class="form-control form-control-solid text-center" placeholder="Sistol">
                            <span class="input-group-text">/</span>
                            <input type="number" id="anc_td_d" class="form-control form-control-solid text-center" placeholder="Diastol">
                            <span class="input-group-text">mmHg</span>
                        </div>
                        <input type="hidden" name="tekanan_darah" id="anc_td">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Status TT</label>
                        <select name="status_tt" id="anc_status_tt" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                            <option></option>
                            @foreach(\App\Models\AncVisit::statusTtOptions() as $tt)
                                <option value="{{ $tt }}" @selected($suggestedTt === $tt)>{{ $tt }}</option>
                            @endforeach
                        </select>
                        @if($lastAnc?->status_tt)
                            <div class="form-text fs-9">Last: <b>{{ $lastAnc->status_tt }}</b>{{ $lastAnc->pemberian_tt ? ' (diberi)' : '' }} → suggest <b>{{ $suggestedTt }}</b></div>
                        @endif
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check form-switch form-check-custom pb-2">
                            <input class="form-check-input" type="checkbox" name="pemberian_tt" value="1" id="pemberian_tt">
                            <label class="form-check-label fw-semibold ms-2 fs-7" for="pemberian_tt">TT Diberi</label>
                        </div>
                    </div>
                </div>

                {{-- ===== MAP section ===== --}}
                <div class="card border border-2 mt-3" id="map_section" style="background: linear-gradient(135deg,#f0fdf4 0%,#eff6ff 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div class="text-muted fs-8 fw-bold text-uppercase">📐 MAP (Mean Arterial Pressure)</div>
                                <div class="fs-9 text-muted">Auto-calc: <code>(Sistol + 2 × Diastol) / 3</code></div>
                            </div>
                            <div class="text-end">
                                <input type="number" step="0.1" name="map" id="anc_map" class="form-control form-control-solid text-center fw-bolder fs-2"
                                       style="width:120px; height:60px;" placeholder="—">
                                <div class="fs-9 text-muted mt-1">mmHg</div>
                            </div>
                        </div>
                        <div id="map_info" class="mt-2"></div>
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>
                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">📝 Klinis</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fs-7">Keluhan</label>
                        <textarea name="keluhan" rows="2" class="form-control form-control-solid"></textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Hasil Lab</label>
                        <textarea name="hasil_lab" rows="2" class="form-control form-control-solid" placeholder="Hb, urine, dll"></textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Terapi/Obat</label>
                        <textarea name="terapi" rows="2" class="form-control form-control-solid" placeholder="Fe, multivitamin, dll"></textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Penatalaksanaan</label>
                        <textarea name="penatalaksanaan" rows="2" class="form-control form-control-solid" placeholder="Konseling, edukasi, rujukan..."></textarea>
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7">Catatan</label>
                        <input type="text" name="notes" class="form-control form-control-solid">
                    </div>
                </div>

                {{-- ============ RINGKASAN & REKOMENDASI (real-time) ============ --}}
                <div class="card mt-4 border border-2" style="background: linear-gradient(135deg,#f0fdf4 0%,#fefce8 100%); border-color:#22c55e !important;">
                    <div class="card-header py-2 px-3 border-0 d-flex justify-content-between align-items-center" style="background:transparent;">
                        <div class="fw-bold text-success">
                            <i class="ki-outline ki-information-5 fs-2 me-1"></i>
                            Ringkasan & Rekomendasi (Pemeriksaan Saat Ini)
                        </div>
                        <span class="badge badge-light-success fs-9" id="ancSummaryBadge">0 item</span>
                    </div>
                    <div class="card-body py-2 px-3" style="max-height: 240px; overflow-y: auto;">
                        <div id="ancSummary">
                            <div class="text-muted fs-8 text-center py-3">
                                <i class="ki-outline ki-information fs-2x text-muted opacity-50"></i>
                                <div class="mt-1">Isi data pemeriksaan — ringkasan akan muncul otomatis di sini.</div>
                            </div>
                        </div>
                    </div>
                    <style>
                        #ancSummary::-webkit-scrollbar { width: 6px; }
                        #ancSummary::-webkit-scrollbar-track { background: rgba(0,0,0,.03); border-radius: 3px; }
                        #ancSummary::-webkit-scrollbar-thumb { background: rgba(34,197,94,.4); border-radius: 3px; }
                    </style>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ki-outline ki-check fs-3"></i> Simpan ANC</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<x-sweet-flash />
<x-sweet-helpers />
<script>
$(function() {
    // ===== Auto-calc UK (minggu) berdasar HPHT + visit_date =====
    function calcAncUk() {
        const hpht = $('#anc_hpht').val();
        const visitDate = $('#anc_visit_date').val();
        if (! hpht || ! visitDate) return;
        const h = new Date(hpht + 'T00:00:00');
        const v = new Date(visitDate + 'T00:00:00');
        const days = Math.round((v - h) / 86400000);
        const uk = Math.round(days / 7 * 10) / 10;
        $('#anc_uk').val(uk);
        const tri = uk < 13 ? 'I' : uk < 28 ? 'II' : 'III';
        $('#anc_uk_info').html('UK: <b>' + uk + ' minggu</b> · Trimester <b>' + tri + '</b>');
        calcAncTglKembali(uk);
    }

    // ===== Auto-calc Tgl Kembali berdasar trimester =====
    // Trim I-II (UK <28 mg): +28 hari, Trim III (28-35 mg): +14 hari, Late (>=36): +7 hari
    function calcAncTglKembali(uk) {
        // Hanya auto-fill kalau user belum isi manual
        if ($('#anc_tgl_kembali').val()) return;
        const visitDate = $('#anc_visit_date').val();
        if (! visitDate || uk === null || isNaN(uk)) return;
        let intervalDays = 28, label = 'Trim I-II → kontrol 4 minggu lagi';
        if (uk >= 36)      { intervalDays = 7;  label = 'Trim III akhir → kontrol 1 minggu lagi'; }
        else if (uk >= 28) { intervalDays = 14; label = 'Trim III → kontrol 2 minggu lagi'; }
        const d = new Date(visitDate + 'T00:00:00');
        d.setDate(d.getDate() + intervalDays);
        $('#anc_tgl_kembali').val(d.toISOString().slice(0, 10));
        $('#anc_kembali_info').html('🗓 ' + label);
    }

    // ===== Auto-calc MAP + warning preeklampsia =====
    function calcAncMap() {
        const s = parseInt($('#anc_td_s').val());
        const d = parseInt($('#anc_td_d').val());
        if (isNaN(s) || isNaN(d)) { $('#anc_map').val(''); $('#map_info').html(''); return; }
        const map = Math.round(((s + 2 * d) / 3) * 10) / 10;
        $('#anc_map').val(map);

        let cls, icon, msg;
        if (map < 90) {
            cls = 'success'; icon = 'ki-shield-tick';
            msg = '<b>Normal</b> — MAP < 90 mmHg adalah rentang aman untuk ibu hamil.';
        } else if (map < 105) {
            cls = 'warning'; icon = 'ki-shield-warning';
            msg = '<b>⚠ Curiga Preeklampsia</b> — MAP ' + map + ' mmHg ≥ 90. <b>Cek proteinuria urine</b>, edema, refleks. Kontrol lebih sering.';
        } else {
            cls = 'danger'; icon = 'ki-shield-cross';
            msg = '<b>🚨 Preeklampsia Berat</b> — MAP ' + map + ' mmHg. <b>RUJUK SEGERA</b> ke RS dengan SpOG. Persiapan MgSO4 sesuai protokol.';
        }
        $('#map_info').html(
            '<div class="alert alert-' + cls + ' d-flex align-items-start gap-2 mb-0 py-2 px-3 fs-7">' +
            '<i class="ki-outline ' + icon + ' fs-2 text-' + cls + '"></i>' +
            '<div>' + msg + '</div></div>'
        );
    }

    // Combine TD modal ANC + trigger MAP
    function combineAncTd() {
        const s = $('#anc_td_s').val(), d = $('#anc_td_d').val();
        $('#anc_td').val(s && d ? s + '/' + d : '');
        calcAncMap();
    }
    $('#anc_td_s, #anc_td_d').on('input', combineAncTd);
    $('#anc_visit_date').on('change', calcAncUk);

    // Init saat modal di-open (Bootstrap event)
    $('#modal_anc_visit').on('shown.bs.modal', function() {
        calcAncUk();
        evalAncSummary();
        // Trigger Select2 refresh untuk default values
        $(this).find('select[data-control=select2]').trigger('change.select2');
    });

    // ===========================================================================
    // 📋 RINGKASAN ANC — Real-time risk assessment berdasar input modal
    // ===========================================================================
    const ANC_BB_AWAL    = {{ $pregnancy->berat_badan_awal ?? 'null' }};
    const ANC_BB_PREV    = {{ $bbPrev ?? 'null' }};
    const ANC_RECOM_BB   = @json($pregnancy->recom_kenaikan_bb);
    const ANC_IMT_AWAL   = {{ $pregnancy->imt ?? 'null' }};
    const ANC_PREV_TFU   = {{ $lastAnc?->tfu_cm ?? 'null' }};
    const ANC_PREV_UK    = {{ $lastAnc?->uk_minggu ?? 'null' }};
    const ANC_RIWAYAT_ALERGI  = @json($pregnancy->riwayat_alergi);
    const ANC_RIWAYAT_SAKIT   = @json($pregnancy->riwayat_penyakit);

    function getNum(sel) { const v = parseFloat($(sel).val()); return isNaN(v) ? null : v; }

    function evalAncSummary() {
        const uk      = getNum('#anc_uk');
        const bb      = getNum('input[name=berat_badan_kg]');
        const sistol  = parseInt($('#anc_td_s').val());
        const diastol = parseInt($('#anc_td_d').val());
        const map     = getNum('#anc_map');
        const tfu     = getNum('input[name=tfu_cm]');
        const djj     = parseInt($('input[name=djj_per_menit]').val());
        const letak   = $('select[name=letak_janin]').val();
        const statusTt= $('#anc_status_tt').val();
        const ttDiberi= $('#pemberian_tt').is(':checked');

        const items = [];

        // ===== Stats Informatif =====
        if (uk) {
            const trim = uk < 13 ? 'I' : uk < 28 ? 'II' : 'III';
            items.push({type:'stat', color:'success', icon:'ki-heart-circle',
                title:`🤰 UK ${uk} minggu (Trimester ${trim})`,
                detail: trim === 'III' ? 'Persiapan persalinan — kontrol lebih sering' : 'Pertumbuhan janin aktif'});
        }

        // BB tracking
        if (bb !== null && ANC_BB_AWAL !== null) {
            const delta = Math.round((bb - ANC_BB_AWAL) * 10) / 10;
            const deltaPrev = ANC_BB_PREV !== null ? Math.round((bb - ANC_BB_PREV) * 10) / 10 : null;
            items.push({type:'stat', color: delta >= 0 ? 'info' : 'warning', icon:'ki-graph-up',
                title:`⚖ BB Sekarang: ${bb} kg`,
                detail:`Selisih dari BB awal: ${delta > 0 ? '+' : ''}${delta} kg`
                    + (deltaPrev !== null ? ` · Selisih dari kunjungan lalu: ${deltaPrev > 0 ? '+' : ''}${deltaPrev} kg` : '')
                    + (ANC_RECOM_BB ? ` · Target: ${ANC_RECOM_BB}` : '')});
        }

        // ===== Risk Rules =====

        // MAP analysis (preeklampsia)
        if (map !== null) {
            if (map >= 105) {
                items.push({type:'risk', sev:'danger', icon:'ki-shield-cross',
                    title:`🚨 Preeklampsia Berat — MAP ${map} mmHg`,
                    risk:'Risiko TINGGI: eklampsia, HELLP, abruptio plasenta, kematian ibu/janin.',
                    recom:'⛔ RUJUK SEGERA ke RS dengan SpOG + ICU. Persiapan MgSO4 sesuai protokol. Antihipertensi.'});
            } else if (map >= 90) {
                items.push({type:'risk', sev:'warning', icon:'ki-shield-warning',
                    title:`⚠ Curiga Preeklampsia — MAP ${map} mmHg`,
                    risk:'MAP ≥ 90 indikator awal preeklampsia. Risiko IUGR, prematur, hipertensi krisis.',
                    recom:'✅ Cek proteinuria urine dipstick, edema pretibial, refleks tendon. Diet rendah garam. Kontrol 1 minggu.'});
            }
        }

        // TD direct check (independent dari MAP)
        if (!isNaN(sistol) && !isNaN(diastol)) {
            if (sistol >= 160 || diastol >= 110) {
                items.push({type:'risk', sev:'danger', icon:'ki-heart-broken',
                    title:`🚨 Hipertensi Berat — TD ${sistol}/${diastol} mmHg`,
                    risk:'Krisis hipertensi: stroke, eklampsia, abruptio plasenta.',
                    recom:'⛔ RUJUK SEGERA. Persiapan nifedipine/labetalol per protokol IGD.'});
            } else if ((sistol >= 140 || diastol >= 90) && map === null) {
                items.push({type:'risk', sev:'warning', icon:'ki-heart-broken',
                    title:`⚠ Hipertensi — TD ${sistol}/${diastol} mmHg`,
                    risk:'Risiko preeklampsia, IUGR.',
                    recom:'✅ Cek proteinuria, edema, pusing. Tirah baring. Kontrol 1 minggu.'});
            }
        }

        // DJJ analysis
        if (!isNaN(djj)) {
            if (djj < 100) {
                items.push({type:'risk', sev:'danger', icon:'ki-pulse-stop',
                    title:`🚨 Bradikardia Janin — DJJ ${djj} /menit`,
                    risk:'BAHAYA: gawat janin, hipoksia, risiko kematian janin dalam rahim.',
                    recom:'⛔ Posisi miring kiri, O2, evaluasi ulang 5 menit. Kalau persisten → RUJUK SEGERA.'});
            } else if (djj < 120) {
                items.push({type:'risk', sev:'warning', icon:'ki-pulse',
                    title:`⚠ DJJ Rendah — ${djj} /menit`,
                    risk:'Bradikardia ringan, kemungkinan gawat janin.',
                    recom:'✅ Re-evaluasi setelah ibu istirahat. NST jika perlu. Kontrol ketat.'});
            } else if (djj > 180) {
                items.push({type:'risk', sev:'danger', icon:'ki-pulse',
                    title:`🚨 Takikardia Janin — DJJ ${djj} /menit`,
                    risk:'BAHAYA: gawat janin, infeksi maternal (korioamnionitis), demam.',
                    recom:'⛔ Cek suhu ibu, kemungkinan infeksi. NST/CTG. Rujuk jika persisten.'});
            } else if (djj > 160) {
                items.push({type:'risk', sev:'warning', icon:'ki-pulse',
                    title:`⚠ DJJ Tinggi — ${djj} /menit`,
                    risk:'Takikardia ringan, kemungkinan demam ibu / aktivitas janin tinggi.',
                    recom:'✅ Cek suhu ibu. Re-evaluasi setelah istirahat. NST jika persisten.'});
            } else {
                items.push({type:'stat', color:'success', icon:'ki-heart',
                    title:`💚 DJJ Normal — ${djj} /menit`,
                    detail:'Rentang normal 120-160 /menit. Janin dalam kondisi baik.'});
            }
        }

        // TFU growth vs UK (TFU dalam cm ≈ UK dalam minggu, range UK ± 2 cm)
        if (tfu !== null && uk !== null && uk >= 16 && uk <= 36) {
            const diff = tfu - uk;
            if (Math.abs(diff) > 3) {
                items.push({type:'risk', sev:'warning', icon:'ki-arrow-up-down',
                    title:`⚠ TFU Tidak Sesuai UK — TFU ${tfu} cm vs UK ${uk} mg (selisih ${diff > 0 ? '+' : ''}${diff.toFixed(1)} cm)`,
                    risk: diff > 3
                        ? 'Janin lebih besar dari usia kehamilan: makrosomia, DM gestasional, taksiran HPHT salah.'
                        : 'Janin lebih kecil: IUGR (Intrauterine Growth Restriction), oligohidramnion.',
                    recom:'✅ USG taksiran berat janin + cek air ketuban. Skrining gula darah jika TFU > UK. Konseling nutrisi.'});
            }
        }

        // Letak janin warning di trimester akhir
        if (letak && uk !== null && uk >= 36) {
            if (letak === 'bokong') {
                items.push({type:'risk', sev:'warning', icon:'ki-baby',
                    title:`⚠ Letak Sungsang @UK ${uk} mg`,
                    risk:'Persalinan sungsang berisiko: tali pusat menumbung, asfiksia bayi.',
                    recom:'✅ Pertimbangkan ECV (External Cephalic Version) atau SC elektif. Rujuk SpOG untuk evaluasi.'});
            } else if (letak === 'lintang') {
                items.push({type:'risk', sev:'danger', icon:'ki-baby',
                    title:`🚨 Letak Lintang @UK ${uk} mg`,
                    risk:'Letak lintang TIDAK BISA persalinan spontan: ruptur uteri, kematian janin.',
                    recom:'⛔ SC ELEKTIF wajib. Rujuk SpOG segera, jadwal operasi sebelum UK 38 mg.'});
            }
        }

        // BB increase too fast (>2kg/minggu antar visit)
        if (bb !== null && ANC_BB_PREV !== null && ANC_PREV_UK !== null && uk !== null) {
            const weeksDiff = uk - ANC_PREV_UK;
            if (weeksDiff > 0) {
                const ratePerWeek = (bb - ANC_BB_PREV) / weeksDiff;
                if (ratePerWeek > 2) {
                    items.push({type:'risk', sev:'warning', icon:'ki-graph-up',
                        title:`⚠ Kenaikan BB Cepat — +${(bb-ANC_BB_PREV).toFixed(1)} kg dalam ${weeksDiff.toFixed(1)} mg`,
                        risk:'Risiko: preeklampsia, DM gestasional, makrosomia, edema patologis.',
                        recom:'✅ Cek edema pretibial, proteinuria, gula darah. Diet rendah garam & gula. Aktivitas fisik aman.'});
                } else if (ratePerWeek < 0 && uk >= 13) {
                    items.push({type:'risk', sev:'warning', icon:'ki-graph-down',
                        title:`⚠ BB Turun — ${(bb-ANC_BB_PREV).toFixed(1)} kg dari kunjungan lalu`,
                        risk:'Risiko: hiperemesis, malnutrisi, infeksi kronis, IUGR.',
                        recom:'✅ Skrining hiperemesis (frekuensi muntah, dehidrasi). Cek elektrolit, urinalisis. Suplementasi gizi.'});
                }
            }
        }

        // Status TT update reminder
        if (statusTt && !ttDiberi) {
            const ttNum = parseInt(statusTt.replace('TT', ''));
            if (ttNum < 5) {
                items.push({type:'stat', color:'info', icon:'ki-syringe',
                    title:`💉 Status TT: ${statusTt}`,
                    detail:`Target ibu hamil minimum TT2. Pertimbangkan pemberian TT${ttNum + 1} kalau jadwal sudah waktunya.`});
            }
        }

        // Reminder alergi (kalau ada di K1)
        if (ANC_RIWAYAT_ALERGI && ANC_RIWAYAT_ALERGI.trim()) {
            items.push({type:'stat', color:'danger', icon:'ki-shield-cross',
                title:`⚠ Alergi: ${ANC_RIWAYAT_ALERGI}`,
                detail:'Hindari obat-obatan yang berkaitan saat resep terapi.'});
        }

        // ===== Render =====
        const $sum = $('#ancSummary');
        const $badge = $('#ancSummaryBadge');
        const stats = items.filter(i => i.type === 'stat');
        const risks = items.filter(i => i.type === 'risk');
        const dangerN  = risks.filter(r => r.sev === 'danger').length;
        const warningN = risks.filter(r => r.sev === 'warning').length;

        $badge.text(items.length + ' item');
        $badge.removeClass(function(_, c) { return (c.match(/badge-light-\S+/g) || []).join(' '); });
        if (dangerN > 0)        $badge.addClass('badge-light-danger');
        else if (warningN > 0)  $badge.addClass('badge-light-warning');
        else                    $badge.addClass('badge-light-success');

        if (items.length === 0) {
            $sum.html('<div class="text-muted fs-8 text-center py-3"><i class="ki-outline ki-information fs-2x text-muted opacity-50"></i><div class="mt-1">Isi data pemeriksaan — ringkasan akan muncul otomatis di sini.</div></div>');
            return;
        }

        let html = '';

        // Overall status
        const overallColor = dangerN > 0 ? 'danger' : warningN > 0 ? 'warning' : 'success';
        const overallLabel = dangerN > 0
            ? `🚨 Risiko TINGGI — ${dangerN} kritis, ${warningN} warning`
            : warningN > 0
                ? `⚠ Risiko Sedang — ${warningN} warning`
                : '✅ Pemeriksaan dalam batas normal';
        html += `<div class="alert alert-${overallColor} d-flex align-items-center gap-2 py-2 px-3 mb-2 fs-8">
                    <i class="ki-outline ki-shield-tick fs-3 text-${overallColor}"></i>
                    <div class="fw-bold">${overallLabel}</div>
                </div>`;

        // Stats first (compact)
        stats.forEach(s => {
            html += `<div class="d-flex align-items-start gap-2 mb-2 p-2 rounded bg-light-${s.color}">
                <i class="ki-outline ${s.icon} fs-3 text-${s.color}"></i>
                <div class="flex-grow-1 fs-8">
                    <div class="fw-bold">${s.title}</div>
                    <div class="text-muted">${s.detail}</div>
                </div>
            </div>`;
        });

        // Risks with detail
        risks.forEach(r => {
            html += `<div class="border-start border-${r.sev} border-3 ps-3 mb-2">
                <div class="fw-bold text-${r.sev} fs-8 mb-1">
                    <i class="ki-outline ${r.icon} fs-3 me-1"></i>${r.title}
                </div>
                <div class="fs-9 text-muted mb-1"><b>Risiko:</b> ${r.risk}</div>
                <div class="fs-9 text-success"><b>Rekomendasi:</b> ${r.recom}</div>
            </div>`;
        });

        $sum.html(html);
    }

    // Hook semua input vital → re-evaluate ringkasan
    $('input[name=berat_badan_kg], input[name=tfu_cm], input[name=djj_per_menit], select[name=letak_janin], #anc_status_tt, #pemberian_tt, #anc_td_s, #anc_td_d, #anc_uk, #anc_visit_date').on('input change', () => setTimeout(evalAncSummary, 100));

    // Detail ANC popup
    $(document).on('click', '.btn-show-anc', function() {
        const v = $(this).data('data');
        $('#ad_no').text($(this).data('no'));
        const letakOptions = @json(\App\Models\AncVisit::letakOptions());
        const html = `
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="card border h-100"><div class="card-body py-3 text-center">
                    <div class="text-muted fs-8 text-uppercase fw-semibold">Tanggal</div>
                    <div class="fw-bold">${v.visit_date ? new Date(v.visit_date).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-'}</div>
                </div></div></div>
                <div class="col-md-3"><div class="card border h-100"><div class="card-body py-3 text-center">
                    <div class="text-muted fs-8 text-uppercase fw-semibold">UK</div>
                    <div class="fw-bold">${v.uk_minggu || '-'} mg</div>
                </div></div></div>
                <div class="col-md-3"><div class="card border h-100"><div class="card-body py-3 text-center">
                    <div class="text-muted fs-8 text-uppercase fw-semibold">TFU</div>
                    <div class="fw-bold">${v.tfu_cm || '-'} cm</div>
                </div></div></div>
                <div class="col-md-3"><div class="card border h-100"><div class="card-body py-3 text-center">
                    <div class="text-muted fs-8 text-uppercase fw-semibold">DJJ</div>
                    <div class="fw-bold">${v.djj_per_menit || '-'} /mnt</div>
                </div></div></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><div class="text-muted fs-8 text-uppercase fw-semibold">Letak Janin</div><div class="fw-bold">${letakOptions[v.letak_janin] || '-'}</div></div>
                <div class="col-md-4"><div class="text-muted fs-8 text-uppercase fw-semibold">BB</div><div class="fw-bold">${v.berat_badan_kg || '-'} kg</div></div>
                <div class="col-md-4"><div class="text-muted fs-8 text-uppercase fw-semibold">TD</div><div class="fw-bold">${v.tekanan_darah || '-'}</div></div>
                <div class="col-md-4"><div class="text-muted fs-8 text-uppercase fw-semibold">Tempat</div><div class="fw-bold">${v.tempat_periksa || '-'}</div></div>
                <div class="col-md-4"><div class="text-muted fs-8 text-uppercase fw-semibold">Status TT</div><div class="fw-bold">${v.status_tt || '-'} ${v.pemberian_tt ? '<span class="badge badge-light-success ms-1">Diberi</span>' : ''}</div></div>
                <div class="col-md-4"><div class="text-muted fs-8 text-uppercase fw-semibold">Tgl Kembali</div><div class="fw-bold">${v.tanggal_kembali ? new Date(v.tanggal_kembali).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '-'}</div></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Keluhan</div>
                    <div class="p-3 bg-light-primary rounded">${v.keluhan || '-'}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Terapi</div>
                    <div class="p-3 bg-light-success rounded">${v.terapi || '-'}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Hasil Lab</div>
                    <div class="p-3 bg-light-info rounded">${v.hasil_lab || '-'}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Penatalaksanaan</div>
                    <div class="p-3 bg-light-warning rounded">${v.penatalaksanaan || '-'}</div>
                </div>
            </div>
        `;
        $('#ad_body').html(html);
    });

    // ===== SweetAlert confirm untuk Mulai Persalinan (preterm warning) =====
    $('#btn_mulai_persalinan').on('click', function(e) {
        const uk = parseFloat($(this).data('uk'));
        if (! isNaN(uk) && uk < 36) {
            e.preventDefault();
            const href = $(this).attr('href');
            Swal.fire({
                title: '⚠ Kehamilan Preterm',
                html: `UK baru <b>${uk} minggu</b> (< 36 mg = preterm/kurang bulan).<br>Risiko bayi prematur tinggi.<br><br>Yakin mau mulai persalinan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
            }).then(r => { if (r.isConfirmed) window.location = href; });
        }
    });

    $('.form-del-anc').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus catatan ANC?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
