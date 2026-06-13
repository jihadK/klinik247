@extends('admin.layouts.app')
@section('title', 'Persalinan — '.$delivery->no_persalinan)
@section('page_title', 'Detail Persalinan (INC)')

@section('content')

@if(in_array($delivery->status, ['selesai','rujuk']))
    <div class="alert alert-{{ $delivery->status === 'selesai' ? 'success' : 'danger' }} d-flex gap-3 mb-5 border border-2 border-{{ $delivery->status === 'selesai' ? 'success' : 'danger' }}">
        <i class="ki-outline {{ $delivery->status === 'selesai' ? 'ki-check-circle' : 'ki-arrow-right-square' }} fs-3x"></i>
        <div>
            <h4 class="mb-1">{{ $delivery->status_label }}</h4>
            <div class="fs-8 text-muted">Data terkunci untuk audit. Edit & tambah SOAP tidak diperbolehkan.</div>
            @if($delivery->status === 'rujuk' && $delivery->rujukan_alasan)
                <div class="fs-7 mt-2"><b>Alasan rujukan:</b> {{ $delivery->rujukan_alasan }} → <b>{{ $delivery->rujukan_ke }}</b></div>
            @endif
        </div>
    </div>
@endif

<div class="row">
    {{-- LEFT: Pasien + Status + Quick Actions --}}
    <div class="col-md-4">
        <div class="card mb-5">
            <div class="card-body text-center">
                <span class="badge badge-light-{{ $delivery->status_color }} fs-6 mb-3">{{ $delivery->status_label }}</span>
                <h3 class="mb-1">{{ $delivery->patient?->name }}</h3>
                <div class="text-muted fs-7">{{ $delivery->patient?->no_rm }} · {{ $delivery->patient?->age }}</div>
                <div class="fs-2x fw-bolder text-danger mt-3 font-monospace">{{ $delivery->no_persalinan }}</div>

                {{-- Highlight Penapisan --}}
                <div class="my-4 p-3 rounded text-white shadow-sm" style="background: linear-gradient(135deg,{{ $delivery->penapisan_skor > 0 ? '#dc2626 0%, #991b1b' : '#16a34a 0%, #166534' }} 100%);">
                    <div class="text-white-50 fs-8 fw-semibold text-uppercase">Skor Penapisan</div>
                    <div class="fs-2 fw-bolder text-white">{{ $delivery->penapisan_skor }} / 18</div>
                    <div class="fs-8">{{ ucfirst($delivery->penapisan_keputusan ?? '-') }}</div>
                </div>

                <div class="text-muted fs-7">⏰ Masuk: <b class="text-dark">{{ optional($delivery->masuk_at)->isoFormat('D MMM YY HH:mm') }}</b></div>
                @if($delivery->bayi_lahir_at)
                    <div class="text-muted fs-7 mt-1">👶 Bayi Lahir: <b class="text-dark">{{ $delivery->bayi_lahir_at->isoFormat('D MMM YY HH:mm') }}</b></div>
                @endif
                @if($delivery->total_duration)
                    <div class="text-muted fs-7 mt-1">⏱ Total: <b class="text-dark">{{ $delivery->total_duration }} jam</b></div>
                @endif

                <a href="{{ route('admin.anc.show', $delivery->pregnancy) }}" class="btn btn-sm btn-light-info w-100 mt-3">
                    <i class="ki-outline ki-heart-circle fs-3"></i> Lihat Kehamilan ({{ $delivery->pregnancy?->no_kartu_hamil }})
                </a>

                {{-- ===== Tombol PNC + KN (kalau persalinan selesai) ===== --}}
                @if($delivery->status === 'selesai' || $delivery->bayi_lahir_at)
                    @php $neonate = \App\Models\Neonate::where('delivery_id', $delivery->id)->first(); @endphp
                    @if(auth()->user()->hasPermission('pnc.view'))
                        <a href="{{ route('admin.pnc.show', $delivery) }}" class="btn btn-sm btn-warning w-100 mt-2">
                            <i class="ki-outline ki-heart fs-3"></i> 🟡 Catat/Lihat Nifas (KF)
                        </a>
                    @endif
                    @if($neonate && auth()->user()->hasPermission('kn.view'))
                        <a href="{{ route('admin.kn.show', $neonate) }}" class="btn btn-sm btn-info w-100 mt-2">
                            <i class="ki-outline ki-tag fs-3"></i> 🟠 Catat/Lihat Bayi (KN) — {{ $neonate->no_kartu_bayi }}
                        </a>
                    @endif
                @endif

                {{-- ===== Tombol Cetak Surat Rujukan ===== --}}
                @if(auth()->user()->hasPermission('inc.print') && ($delivery->status === 'rujuk' || $delivery->penapisan_keputusan === 'rujuk' || $delivery->penapisan_skor > 0))
                    <button type="button" class="btn btn-sm btn-danger w-100 mt-2" data-bs-toggle="modal" data-bs-target="#modal_surat_rujukan">
                        <i class="ki-outline ki-printer fs-3"></i> Cetak Surat Rujukan
                    </button>
                @endif
                @if(auth()->user()->hasPermission('inc.update') && ! in_array($delivery->status, ['selesai','rujuk']))
                    <a href="{{ route('admin.inc.edit', $delivery) }}" class="btn btn-sm btn-light-warning w-100 mt-2">
                        <i class="ki-outline ki-pencil fs-3"></i> Edit / Kelola 4 Kala
                    </a>
                @endif
                <a href="{{ route('admin.inc.index') }}" class="btn btn-sm btn-light w-100 mt-2">← Daftar Persalinan</a>
            </div>
        </div>

        {{-- Penapisan Detail --}}
        @if($delivery->penapisan_skor > 0)
        <div class="card mb-5 border border-danger">
            <div class="card-header bg-light-danger py-2"><h4 class="card-title text-danger fs-6">⚠ Faktor Risiko Terdeteksi</h4></div>
            <div class="card-body py-2">
                @foreach($penapisan as $field => $label)
                    @if($delivery->{$field})
                        <div class="d-flex align-items-center gap-2 py-1 fs-8">
                            <i class="ki-outline ki-warning fs-3 text-danger"></i>
                            <span>{{ $label }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: 4 Kala + SOAP + Terapi --}}
    <div class="col-md-8">
        {{-- Masuk PMB --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">📥 Masuk PMB</h3></div>
            <div class="card-body">
                <div class="row g-2 fs-8">
                    <div class="col-md-3"><div class="text-muted">TD</div><div class="fw-bold">{{ $delivery->masuk_ttv_td ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="text-muted">Nadi</div><div class="fw-bold">{{ $delivery->masuk_ttv_nadi ?? '-' }} /mnt</div></div>
                    <div class="col-md-3"><div class="text-muted">Suhu</div><div class="fw-bold">{{ $delivery->masuk_ttv_suhu ?? '-' }} °C</div></div>
                    <div class="col-md-3"><div class="text-muted">RR</div><div class="fw-bold">{{ $delivery->masuk_ttv_rr ?? '-' }} /mnt</div></div>
                    <div class="col-md-3"><div class="text-muted">DJJ</div><div class="fw-bold">{{ $delivery->masuk_djj ?? '-' }} /mnt</div></div>
                    <div class="col-md-3"><div class="text-muted">His/10 mnt</div><div class="fw-bold">{{ $delivery->masuk_his_per_10 ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="text-muted">VT Pembukaan</div><div class="fw-bold">{{ $delivery->masuk_vt_pembukaan ?? '-' }} cm</div></div>
                    <div class="col-md-3"><div class="text-muted">Ketuban</div><div class="fw-bold">{{ $ketubanOptions[$delivery->masuk_ketuban] ?? '-' }}</div></div>
                </div>
                @if($delivery->masuk_keluhan)
                    <div class="mt-3"><div class="text-muted fs-8">Keluhan</div><div>{{ $delivery->masuk_keluhan }}</div></div>
                @endif
            </div>
        </div>

        {{-- ============ 4 KALA TIMELINE ============ --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">🩺 Timeline 4 Kala Persalinan</h3></div>
            <div class="card-body">
                <div class="timeline-vertical">
                    {{-- Kala I --}}
                    <div class="kala-box mb-3 p-3 rounded border-start border-4 border-primary bg-light-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary">Kala I — Fase Aktif</h5>
                            @if($delivery->kala1_mulai_at)
                                <span class="badge badge-light-primary">{{ $delivery->kala1_mulai_at->isoFormat('HH:mm') }} @if($delivery->kala1_selesai_at)→ {{ $delivery->kala1_selesai_at->isoFormat('HH:mm') }} ({{ $delivery->kala1_duration }} jam) @endif</span>
                            @endif
                        </div>
                        @if($delivery->kala1_keterangan)<div class="fs-8 mt-2">{{ $delivery->kala1_keterangan }}</div>@endif
                    </div>

                    {{-- Kala II --}}
                    <div class="kala-box mb-3 p-3 rounded border-start border-4 border-warning bg-light-warning">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-warning">Kala II — Mengejan & Bayi Lahir</h5>
                            @if($delivery->bayi_lahir_at)
                                <span class="badge badge-warning">Bayi Lahir: {{ $delivery->bayi_lahir_at->isoFormat('D MMM HH:mm') }}</span>
                            @endif
                        </div>
                        @if($delivery->bayi_jenis_kelamin || $delivery->bayi_bb_gram)
                            <div class="row g-2 fs-8 mt-2">
                                <div class="col-md-3"><div class="text-muted">JK</div><div class="fw-bold">{{ $delivery->bayi_jenis_kelamin === 'L' ? 'Laki-laki' : ($delivery->bayi_jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</div></div>
                                <div class="col-md-3"><div class="text-muted">BB</div><div class="fw-bold">{{ $delivery->bayi_bb_gram ?? '-' }} gr</div></div>
                                <div class="col-md-3"><div class="text-muted">PB</div><div class="fw-bold">{{ $delivery->bayi_pb_cm ?? '-' }} cm</div></div>
                                <div class="col-md-3"><div class="text-muted">APGAR 1'/5'</div><div class="fw-bold">{{ $delivery->bayi_apgar_1 ?? '-' }} / {{ $delivery->bayi_apgar_5 ?? '-' }}</div></div>
                            </div>
                            <div class="mt-2 d-flex gap-2">
                                @if($delivery->bayi_lahir_spontan)<span class="badge badge-light-success fs-9">✓ Lahir Spontan</span>@endif
                                @if($delivery->bayi_lgs_menangis)<span class="badge badge-light-success fs-9">✓ Langsung Menangis</span>@endif
                            </div>
                        @endif
                    </div>

                    {{-- Kala III --}}
                    <div class="kala-box mb-3 p-3 rounded border-start border-4 border-info bg-light-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-info">Kala III — Plasenta</h5>
                            @if($delivery->plasenta_lahir_at)
                                <span class="badge badge-light-info">Plasenta Lahir: {{ $delivery->plasenta_lahir_at->isoFormat('HH:mm') }}</span>
                            @endif
                        </div>
                        @if($delivery->kala3_mulai_at)
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @if($delivery->mak_iii_dilakukan)<span class="badge badge-light-success fs-9">✓ MAK III</span>@endif
                                @if($delivery->amniotomi)<span class="badge badge-light-warning fs-9">⚠ Amniotomi</span>@endif
                                @if($delivery->plasenta_lahir_spontan)<span class="badge badge-light-success fs-9">✓ Plasenta Spontan</span>@endif
                                @if($delivery->tfu_sepusat)<span class="badge badge-light-info fs-9">TFU Sepusat</span>@endif
                                @if($delivery->uc_kuat)<span class="badge badge-light-success fs-9">✓ UC Kuat</span>@endif
                                @if($delivery->eksplorasi_dilakukan)<span class="badge badge-light-warning fs-9">Eksplorasi</span>@endif
                                @if($delivery->sisa_plasenta)<span class="badge badge-light-danger fs-9">⚠ Sisa Plasenta</span>@endif
                                @if(! $delivery->selaput_lengkap)<span class="badge badge-light-danger fs-9">⚠ Selaput Tidak Lengkap</span>@endif
                            </div>
                        @endif
                    </div>

                    {{-- Kala IV --}}
                    <div class="kala-box p-3 rounded border-start border-4 border-success bg-light-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-success">Kala IV — Observasi 2 jam</h5>
                            @if($delivery->kala4_mulai_at)
                                <span class="badge badge-light-success">{{ $delivery->kala4_mulai_at->isoFormat('HH:mm') }} @if($delivery->kala4_selesai_at)→ {{ $delivery->kala4_selesai_at->isoFormat('HH:mm') }} @endif</span>
                            @endif
                        </div>
                        @if($delivery->kala4_mulai_at)
                            <div class="row g-2 fs-8 mt-2">
                                <div class="col-md-4"><div class="text-muted">Perineum Laserasi</div><div class="fw-bold">{{ $laserasiOptions[$delivery->perineum_laserasi] ?? '-' }}</div></div>
                                <div class="col-md-4"><div class="text-muted">Perdarahan</div><div class="fw-bold">{{ $delivery->perdarahan_ml ?? '-' }} ml</div></div>
                                <div class="col-md-4">
                                    @if($delivery->heckting_dilakukan)<span class="badge badge-light-info fs-9">✓ Heckting</span>@endif
                                    @if($delivery->heckting_lidocain)<span class="badge badge-light-info fs-9">+ Lidocain</span>@endif
                                </div>
                            </div>
                            @if($delivery->kala4_keluhan)<div class="mt-2 fs-8 text-muted">{{ $delivery->kala4_keluhan }}</div>@endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ TERAPI PASCA PERSALINAN ============ --}}
        @if($delivery->terapi_amoxicillin || $delivery->terapi_asam_mef || $delivery->terapi_fe || $delivery->terapi_metergin || $delivery->bayi_injeksi_neo_k || $delivery->bayi_salep_mata || $delivery->bayi_imunisasi_hb0)
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">💊 Terapi Pasca Persalinan</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h5 class="fs-7 text-primary mb-2">Untuk Ibu</h5>
                        <div class="d-flex flex-wrap gap-1">
                            @if($delivery->terapi_amoxicillin)<span class="badge badge-light-primary">Amoxicillin</span>@endif
                            @if($delivery->terapi_asam_mef)<span class="badge badge-light-primary">As. Mefenamat</span>@endif
                            @if($delivery->terapi_fe)<span class="badge badge-light-primary">Fe (Zat Besi)</span>@endif
                            @if($delivery->terapi_metergin)<span class="badge badge-light-primary">Metergin</span>@endif
                            @if($delivery->terapi_vita_dose1_at)<span class="badge badge-light-success">Vit A Dose 1: {{ $delivery->terapi_vita_dose1_at->isoFormat('D MMM HH:mm') }}</span>@endif
                            @if($delivery->terapi_vita_dose2_at)<span class="badge badge-light-success">Vit A Dose 2: {{ $delivery->terapi_vita_dose2_at->isoFormat('D MMM HH:mm') }}</span>@endif
                        </div>
                        @if($delivery->terapi_ibu_dosis_notes)<div class="fs-8 text-muted mt-2">{{ $delivery->terapi_ibu_dosis_notes }}</div>@endif
                    </div>
                    <div class="col-md-6">
                        <h5 class="fs-7 text-warning mb-2">Untuk Bayi</h5>
                        <div class="d-flex flex-wrap gap-1">
                            @if($delivery->bayi_injeksi_neo_k)<span class="badge badge-light-warning">Injeksi Neo K (Vit K1) @ {{ optional($delivery->bayi_neo_k_at)->isoFormat('HH:mm') }}</span>@endif
                            @if($delivery->bayi_salep_mata)<span class="badge badge-light-warning">Salep Mata</span>@endif
                            @if($delivery->bayi_imunisasi_hb0)<span class="badge badge-light-success">HB-0 @ {{ optional($delivery->bayi_hb0_at)->isoFormat('HH:mm') }} (Batch: {{ $delivery->bayi_hb0_no_batch ?? '-' }})</span>@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ============ TIMELINE SOAP ============ --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">📝 Timeline SOAP Pengkajian ({{ $delivery->soaps->count() }} observasi)</h3>
                @if(auth()->user()->hasPermission('inc.soap') && ! in_array($delivery->status, ['selesai','rujuk']))
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_soap">
                            <i class="ki-outline ki-plus fs-3"></i> Tambah SOAP
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                @forelse($delivery->soaps as $s)
                    <div class="border-start border-{{ $s->kala === 'kala_ii' ? 'warning' : ($s->kala === 'kala_iii' ? 'info' : ($s->kala === 'kala_iv' ? 'success' : 'primary')) }} border-3 ps-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge badge-light-{{ $s->kala === 'kala_ii' ? 'warning' : ($s->kala === 'kala_iii' ? 'info' : ($s->kala === 'kala_iv' ? 'success' : 'primary')) }} fs-8 mb-1">{{ $s->kala_label }}</span>
                                <div class="fw-bold fs-7">{{ $s->observed_at->isoFormat('D MMM YYYY HH:mm') }}</div>
                            </div>
                            @if(auth()->user()->hasPermission('inc.soap'))
                                <form action="{{ route('admin.inc.soap.destroy', $s) }}" method="POST" class="form-del-soap">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-light-danger" title="Hapus"><i class="ki-outline ki-trash fs-3"></i></button>
                                </form>
                            @endif
                        </div>

                        <div class="row g-2 fs-8 mt-2">
                            @if($s->subjective)<div class="col-12"><div class="text-muted fw-bold">S — Subjective</div><div>{{ $s->subjective }}</div></div>@endif
                            <div class="col-12">
                                <div class="text-muted fw-bold">O — Objective</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($s->ttv_td)<span class="badge badge-light-warning fs-9">TD: {{ $s->ttv_td }}</span>@endif
                                    @if($s->ttv_nadi)<span class="badge badge-light fs-9">N: {{ $s->ttv_nadi }}/mnt</span>@endif
                                    @if($s->ttv_suhu)<span class="badge badge-light fs-9">S: {{ $s->ttv_suhu }}°C</span>@endif
                                    @if($s->ttv_rr)<span class="badge badge-light fs-9">RR: {{ $s->ttv_rr }}</span>@endif
                                    @if($s->djj)<span class="badge badge-light-danger fs-9">DJJ: {{ $s->djj }}/mnt</span>@endif
                                    @if($s->his_per_10)<span class="badge badge-light-info fs-9">His: {{ $s->his_per_10 }}×/10' {{ $s->his_durasi }}</span>@endif
                                    @if($s->vt_pembukaan !== null)<span class="badge badge-light-primary fs-9">VT: {{ $s->vt_pembukaan }} cm</span>@endif
                                    @if($s->vt_penurunan)<span class="badge badge-light-primary fs-9">{{ $s->vt_penurunan }}</span>@endif
                                    @if($s->ketuban)<span class="badge badge-light-info fs-9">Ketuban: {{ $s->ketuban }}</span>@endif
                                    @if($s->hb_gr_dl)<span class="badge badge-light-warning fs-9">Hb: {{ $s->hb_gr_dl }} g/dl</span>@endif
                                    @if($s->alb)<span class="badge badge-light fs-9">Alb: {{ $s->alb }}</span>@endif
                                </div>
                            </div>
                            @if($s->assessment)<div class="col-12"><div class="text-muted fw-bold">A — Assessment</div><div class="text-primary fw-semibold">{{ $s->assessment }}</div></div>@endif
                            @if($s->plan)<div class="col-12"><div class="text-muted fw-bold">P — Plan</div><div class="text-success fw-semibold">{{ $s->plan }}</div></div>@endif
                            @if($s->notes)<div class="col-12 text-muted fs-9">{{ $s->notes }}</div>@endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-5">Belum ada catatan SOAP.</div>
                @endforelse
            </div>
        </div>

        {{-- ============ SIKLUS RUJUKAN — tampil kalau ada activity rujukan ============ --}}
        @if($delivery->rujukan_ke || $delivery->rujuk_dikirim_at || $delivery->status === 'rujuk')
            @php
                $rujukStatuses = \App\Models\Delivery::rujukSiklusStatuses();
                $curStatus = $rujukStatuses[$delivery->rujuk_siklus_status] ?? $rujukStatuses['belum_kirim'];
                $stages = [
                    ['key' => 'belum_kirim',  'label' => 'Pra-Rujuk',       'icon' => 'ki-shield-search', 'check' => $delivery->rujukan_ke],
                    ['key' => 'dikirim',      'label' => 'Dikirim',         'icon' => 'ki-arrow-right',  'check' => $delivery->rujuk_dikirim_at],
                    ['key' => 'diterima_rs',  'label' => 'Diterima RS',     'icon' => 'ki-check',         'check' => $delivery->rujuk_diterima_at],
                    ['key' => 'ada_balasan',  'label' => 'Surat Balasan',   'icon' => 'ki-message-text', 'check' => $delivery->rujuk_balik_diterima_at],
                    ['key' => 'selesai',      'label' => 'Selesai',         'icon' => 'ki-check-circle', 'check' => $delivery->rujuk_siklus_status === 'selesai'],
                ];
            @endphp
            <div class="card mb-5 border border-2 border-danger">
                <div class="card-header bg-light-danger">
                    <h3 class="card-title text-danger">
                        <i class="ki-outline ki-arrows-circle fs-2 me-1"></i>
                        🔄 Siklus Rujukan Utuh
                    </h3>
                    <div class="card-toolbar d-flex gap-2 align-items-center">
                        <span class="badge badge-light-{{ $curStatus['color'] }} fs-7">{{ $curStatus['label'] }}</span>
                        {{-- Quick Actions Dropdown --}}
                        @if(auth()->user()->hasPermission('inc.update') && $delivery->rujuk_siklus_status !== 'selesai')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="dropdown">
                                    ⚡ Quick Action <i class="ki-outline ki-down fs-4"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if(! $delivery->rujuk_dikirim_at)
                                        <li><a class="dropdown-item action-rujuk" href="#" data-action="dikirim" data-confirm="Tandai pasien sudah dikirim ke RS?">
                                            <i class="ki-outline ki-arrow-right text-info"></i> Tandai Dikirim ke RS
                                        </a></li>
                                    @endif
                                    @if($delivery->rujuk_dikirim_at && ! $delivery->rujuk_diterima_at)
                                        <li><a class="dropdown-item action-rujuk" href="#" data-action="diterima_rs" data-confirm="Tandai sudah diterima di RS?">
                                            <i class="ki-outline ki-check text-primary"></i> Tandai Diterima RS
                                        </a></li>
                                    @endif
                                    @if($delivery->rujuk_diterima_at && ! $delivery->rujuk_balik_diterima_at)
                                        <li><a class="dropdown-item action-rujuk" href="#" data-action="ada_balasan" data-confirm="Tandai sudah ada surat balasan dari RS? Lengkapi isi via Edit.">
                                            <i class="ki-outline ki-message-text text-warning"></i> Tandai Ada Surat Balasan
                                        </a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-success action-rujuk" href="#" data-action="selesai" data-confirm="🎯 Selesaikan rujukan ini? <br><br>Sistem akan:<br>• Set status persalinan = <b>Dirujuk</b><br>• Set siklus rujukan = <b>Selesai</b><br>• Update kehamilan ke status <b>Dirujuk</b>">
                                        <i class="ki-outline ki-check-circle text-success"></i> <b>✅ Tandai Rujukan SELESAI</b>
                                    </a></li>
                                    <li><a class="dropdown-item text-danger action-rujuk" href="#" data-action="batal" data-confirm="⛔ Batalkan rujukan ini?">
                                        <i class="ki-outline ki-cross-circle text-danger"></i> Batalkan Rujukan
                                    </a></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    {{-- Stepper Progress --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 position-relative">
                        @foreach($stages as $i => $stage)
                            <div class="text-center flex-grow-1 position-relative" style="z-index: 2;">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                                     style="width:48px; height:48px; background: {{ $stage['check'] ? '#22c55e' : '#e4e6ef' }}; color: {{ $stage['check'] ? '#fff' : '#9ca3af' }};">
                                    <i class="ki-outline {{ $stage['icon'] }} fs-2"></i>
                                </div>
                                <div class="fs-8 fw-semibold {{ $stage['check'] ? 'text-success' : 'text-muted' }}">{{ $stage['label'] }}</div>
                            </div>
                        @endforeach
                        {{-- Connecting line --}}
                        <div class="position-absolute top-0 start-0 end-0" style="height:4px; background:#e4e6ef; margin-top:22px; z-index:1;"></div>
                    </div>

                    <div class="row g-3">
                        {{-- 1. Tujuan Rujukan --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light-secondary">
                                <div class="text-muted fs-8 fw-bold text-uppercase mb-1">🎯 Tujuan & Alasan</div>
                                <div class="fw-bold">{{ $delivery->rujukan_ke ?? '-' }}</div>
                                @if($delivery->rujukan_alasan)
                                    <div class="fs-8 text-muted mt-1">{{ $delivery->rujukan_alasan }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- 2. Pengiriman --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light-info">
                                <div class="text-muted fs-8 fw-bold text-uppercase mb-1">🚑 Pengiriman</div>
                                @if($delivery->rujuk_dikirim_at)
                                    <div class="fw-bold">{{ $delivery->rujuk_dikirim_at->isoFormat('D MMM YY HH:mm') }}</div>
                                    @if($delivery->rujuk_transport)
                                        <div class="fs-8">{{ \App\Models\Delivery::transportOptions()[$delivery->rujuk_transport] ?? $delivery->rujuk_transport }}</div>
                                    @endif
                                    @if($delivery->rujuk_pendamping)
                                        <div class="fs-8">Pendamping: <b>{{ $delivery->rujuk_pendamping }}</b></div>
                                    @endif
                                    @if($delivery->rujuk_bawa)
                                        <div class="fs-8 text-muted">📦 {{ $delivery->rujuk_bawa }}</div>
                                    @endif
                                @else
                                    <div class="text-muted fs-8">Belum dikirim</div>
                                @endif
                            </div>
                        </div>

                        {{-- 3. Diterima RS --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light-primary">
                                <div class="text-muted fs-8 fw-bold text-uppercase mb-1">🏥 Diterima di RS</div>
                                @if($delivery->rujuk_diterima_at)
                                    <div class="fw-bold">{{ $delivery->rujuk_diterima_at->isoFormat('D MMM YY HH:mm') }}</div>
                                    @if($delivery->rujuk_diterima_by)
                                        <div class="fs-8">Oleh: <b>{{ $delivery->rujuk_diterima_by }}</b></div>
                                    @endif
                                    @php
                                        $duration = $delivery->rujuk_dikirim_at ? $delivery->rujuk_dikirim_at->diffInMinutes($delivery->rujuk_diterima_at) : null;
                                    @endphp
                                    @if($duration)
                                        <div class="fs-8 text-muted">⏱ Lama transit: {{ $duration }} menit</div>
                                    @endif
                                @else
                                    <div class="text-muted fs-8">Belum dikonfirmasi</div>
                                @endif
                            </div>
                        </div>

                        {{-- 4. Surat Balik --}}
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light-warning">
                                <div class="text-muted fs-8 fw-bold text-uppercase mb-1">📩 Surat Balasan RS</div>
                                @if($delivery->rujuk_balik_diterima_at)
                                    <div class="fw-bold">{{ $delivery->rujuk_balik_diterima_at->isoFormat('D MMM YY HH:mm') }}</div>
                                    <div class="fs-8">No: {{ $delivery->rujuk_balik_no ?? '-' }}</div>
                                    @if($delivery->rujuk_balik_dokter_rs)
                                        <div class="fs-8">Oleh: {{ $delivery->rujuk_balik_dokter_rs }}</div>
                                    @endif
                                @else
                                    <div class="text-muted fs-8">Belum ada balasan</div>
                                @endif
                            </div>
                        </div>

                        {{-- Detail Outcome dari RS --}}
                        @if($delivery->rujuk_balik_diagnosis || $delivery->rujuk_balik_tindakan)
                            <div class="col-md-12">
                                <div class="border-top pt-3 mt-2">
                                    <h6 class="fs-7 fw-bold text-warning mb-2">📋 Detail dari Surat Balasan RS</h6>
                                    <div class="row g-2 fs-8">
                                        @if($delivery->rujuk_balik_diagnosis)
                                            <div class="col-md-6">
                                                <div class="text-muted">Diagnosis Final</div>
                                                <div class="p-2 bg-light-primary rounded">{{ $delivery->rujuk_balik_diagnosis }}</div>
                                            </div>
                                        @endif
                                        @if($delivery->rujuk_balik_tindakan)
                                            <div class="col-md-6">
                                                <div class="text-muted">Tindakan RS</div>
                                                <div class="p-2 bg-light-info rounded">{{ $delivery->rujuk_balik_tindakan }}</div>
                                            </div>
                                        @endif
                                        @if($delivery->rujuk_balik_outcome_ibu)
                                            <div class="col-md-6">
                                                <div class="text-muted">Outcome Ibu</div>
                                                <div class="p-2 bg-light-success rounded">{{ $delivery->rujuk_balik_outcome_ibu }}</div>
                                            </div>
                                        @endif
                                        @if($delivery->rujuk_balik_outcome_bayi)
                                            <div class="col-md-6">
                                                <div class="text-muted">Outcome Bayi</div>
                                                <div class="p-2 bg-light-success rounded">{{ $delivery->rujuk_balik_outcome_bayi }}</div>
                                            </div>
                                        @endif
                                        @if($delivery->rujuk_balik_rekomendasi)
                                            <div class="col-md-12">
                                                <div class="text-muted">Rekomendasi Follow-up untuk PMB</div>
                                                <div class="p-2 bg-light-warning rounded fw-semibold">{{ $delivery->rujuk_balik_rekomendasi }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Outcome --}}
        @if($delivery->ibu_kondisi || $delivery->bayi_kondisi)
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">🎯 Outcome Persalinan</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted fs-7">Kondisi Ibu</div><div class="fw-bold">{{ $ibuKondisiOptions[$delivery->ibu_kondisi] ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Kondisi Bayi</div><div class="fw-bold">{{ $bayiKondisiOptions[$delivery->bayi_kondisi] ?? '-' }}</div></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ============ MODAL SURAT RUJUKAN — Edit Sebelum Preview ============ --}}
@php
    // Pre-fill data dari delivery + site (auto-default, bisa diedit)
    $defaultFaktorRisiko = collect(\App\Models\Delivery::penapisanItems())
        ->filter(fn ($_, $f) => (bool) $delivery->{$f})
        ->values()
        ->implode("\n• ");
    if ($defaultFaktorRisiko) $defaultFaktorRisiko = "• " . $defaultFaktorRisiko;

    $defaultTindakan = "• Pemeriksaan vital sign dan obstetri lengkap\n"
                    . "• Penapisan 18 faktor risiko ibu bersalin\n"
                    . ($delivery->kala1_mulai_at ? "• Observasi Kala I (mulai " . $delivery->kala1_mulai_at->format('H:i') . ")\n" : '')
                    . ($delivery->amniotomi ? "• Amniotomi (pecah ketuban)\n" : '')
                    . "• Penjelasan dan persetujuan rujukan kepada pasien & keluarga";

    $site = $delivery->site;
@endphp
<div class="modal fade" id="modal_surat_rujukan" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="{{ route('admin.inc.surat-rujukan', $delivery) }}" method="POST" target="_blank" class="modal-content">
            @csrf
            <div class="modal-header bg-light-danger">
                <div>
                    <h3 class="mb-0"><i class="ki-outline ki-printer fs-2 me-2 text-danger"></i> Surat Rujukan — Edit Sebelum Cetak</h3>
                    <div class="text-muted fs-7 mt-1">Auto-isi dari sistem. Edit kolom yang perlu lalu klik <b>Preview & Cetak</b>.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- ===== Kop Surat Section ===== --}}
                <div class="alert alert-info py-2 fs-7 mb-3">
                    <i class="ki-outline ki-information-5 fs-3 me-1"></i>
                    <b>Kop surat & data klinik</b> diambil otomatis dari master <code>tbm_sites</code> (klinik <b>{{ $site->code }}</b>).
                </div>

                <h5 class="mb-2 fs-6 text-primary"><i class="ki-outline ki-shop fs-3 me-1"></i> Identitas Klinik (Kop Surat)</h5>
                <div class="row g-2 mb-4 border rounded p-3 bg-light">
                    <div class="col-md-12"><label class="form-label fs-7">Nama Klinik</label>
                        <input type="text" name="kop_name" value="{{ $site->name }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7">Subtitle (mis. "Praktik Mandiri Bidan")</label>
                        <input type="text" name="kop_subtitle" value="{{ $site->letterhead_subtitle ?? 'Praktik Mandiri Bidan (PMB)' }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7">Alamat</label>
                        <input type="text" name="kop_address" value="{{ $site->address }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Telp</label>
                        <input type="text" name="kop_phone" value="{{ $site->phone }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Email</label>
                        <input type="text" name="kop_email" value="{{ $site->email }}" class="form-control form-control-solid">
                    </div>
                </div>

                <h5 class="mb-2 fs-6 text-primary"><i class="ki-outline ki-document fs-3 me-1"></i> Tujuan Rujukan</h5>
                <div class="row g-2 mb-4">
                    <div class="col-md-6"><label class="form-label fs-7 required">Rujuk Ke (RS / Puskesmas)</label>
                        <input type="text" name="rujukan_ke" value="{{ $delivery->rujukan_ke ?? 'RSUD ' }}" placeholder="Mis. RSUD Lamongan" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Alamat Tujuan (opsional)</label>
                        <input type="text" name="rujukan_alamat" placeholder="Mis. Jl. Basuki Rahmat..." class="form-control form-control-solid">
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7 required">Alasan Rujukan</label>
                        <textarea name="rujukan_alasan" rows="2" class="form-control form-control-solid" required>{{ $delivery->rujukan_alasan ?? 'Berdasarkan hasil penapisan ibu bersalin, ditemukan faktor risiko yang memerlukan penanganan dengan fasilitas dan kewenangan yang lebih lengkap.' }}</textarea>
                    </div>
                </div>

                <h5 class="mb-2 fs-6 text-primary"><i class="ki-outline ki-shield-search fs-3 me-1"></i> Faktor Risiko & Tindakan Pra-Rujuk</h5>
                <div class="row g-2 mb-4">
                    <div class="col-md-6"><label class="form-label fs-7">Faktor Risiko Terdeteksi</label>
                        <textarea name="faktor_risiko" rows="4" class="form-control form-control-solid">{{ $defaultFaktorRisiko }}</textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Tindakan yang Sudah Dilakukan</label>
                        <textarea name="tindakan_pra_rujuk" rows="4" class="form-control form-control-solid">{{ $defaultTindakan }}</textarea>
                    </div>
                </div>

                <h5 class="mb-2 fs-6 text-primary"><i class="ki-outline ki-people fs-3 me-1"></i> Penandatangan</h5>
                <div class="row g-2 mb-4">
                    <div class="col-md-6"><label class="form-label fs-7">Kota & Tanggal</label>
                        <input type="text" name="tempat_tanggal" value="{{ ($site->letterhead_city ?? $site->city ?? 'Lamongan') . ', ' . now()->isoFormat('D MMMM YYYY') }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Nama Bidan PJ</label>
                        <input type="text" name="signer_name" value="{{ $site->letterhead_director ?: (optional($delivery->servedBy)->full_name ?? '') }}" class="form-control form-control-solid">
                        @if($site->letterhead_director)
                            <div class="form-text fs-9">📌 Dari Master Klinik. Edit di <a href="{{ route('admin.sites.edit', $site) }}" target="_blank">Master Klinik</a> untuk ubah default.</div>
                        @endif
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">No. SIPB / NIP</label>
                        <input type="text" name="signer_sipb" value="{{ $site->letterhead_sipb ?? '' }}" placeholder="Mis. SIPB/123/2024 — kosongkan jika tidak ada" class="form-control form-control-solid">
                        <div class="form-text fs-9">Jika kosong, tidak akan ditampilkan di surat.</div>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Jabatan</label>
                        <input type="text" name="signer_position" value="Bidan Penanggung Jawab" class="form-control form-control-solid">
                    </div>
                </div>

                {{-- Nomor surat opsi override --}}
                <div class="row g-2">
                    <div class="col-md-12"><label class="form-label fs-7">Nomor Surat (auto-generate, bisa di-edit)</label>
                        <input type="text" name="no_surat" value="{{ $delivery->no_persalinan }}/RUJ/{{ now()->format('m/Y') }}" class="form-control form-control-solid">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i class="ki-outline ki-eye fs-3"></i> Preview & Cetak Surat
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Modal Tambah SOAP ===== --}}
<div class="modal fade" id="modal_soap" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('admin.inc.soap.store', $delivery) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h3>Tambah SOAP — {{ $delivery->no_persalinan }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-2">
                    <div class="col-md-4"><label class="form-label fs-7 required">Tgl/Pkl Observasi</label>
                        <input type="datetime-local" name="observed_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-4"><label class="form-label fs-7">Kala</label>
                        <select name="kala" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—">
                            <option></option>
                            @foreach($kalaOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>
                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">S — Subjective</div>
                <textarea name="subjective" rows="2" class="form-control form-control-solid mb-3" placeholder="Keluhan ibu saat ini"></textarea>

                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">O — Objective (TTV + Pemeriksaan)</div>
                <div class="row g-2 mb-3 fs-8">
                    <div class="col-md-3"><label class="form-label fs-9">TD</label>
                        <input type="text" name="ttv_td" class="form-control form-control-sm form-control-solid" placeholder="120/80">
                    </div>
                    <div class="col-md-2"><label class="form-label fs-9">N</label>
                        <input type="number" name="ttv_nadi" class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-md-2"><label class="form-label fs-9">S °C</label>
                        <input type="number" step="0.1" name="ttv_suhu" class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-md-2"><label class="form-label fs-9">RR</label>
                        <input type="number" name="ttv_rr" class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-9">DJJ /mnt</label>
                        <input type="number" name="djj" class="form-control form-control-sm form-control-solid" placeholder="120-160">
                    </div>

                    <div class="col-md-2"><label class="form-label fs-9">His/10'</label>
                        <input type="number" name="his_per_10" min="0" max="10" class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-9">Durasi His</label>
                        <input type="text" name="his_durasi" class="form-control form-control-sm form-control-solid" placeholder="30-40 detik">
                    </div>
                    <div class="col-md-2"><label class="form-label fs-9">VT (cm)</label>
                        <input type="number" step="0.5" name="vt_pembukaan" min="0" max="10" class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-md-2"><label class="form-label fs-9">Penurunan</label>
                        <input type="text" name="vt_penurunan" class="form-control form-control-sm form-control-solid" placeholder="Hodge II / 5/5">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-9">Ketuban</label>
                        <select name="ketuban" class="form-select form-select-sm form-select-solid">
                            <option value="">-</option>
                            @foreach(\App\Models\DeliverySoap::ketubanOptions() as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>

                    <div class="col-md-3"><label class="form-label fs-9">Hb (gr/dl)</label>
                        <input type="number" step="0.1" name="hb_gr_dl" class="form-control form-control-sm form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-9">Alb</label>
                        <input type="text" name="alb" class="form-control form-control-sm form-control-solid" placeholder="-/+/++/+++">
                    </div>
                </div>

                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">A — Assessment</div>
                <textarea name="assessment" rows="2" class="form-control form-control-solid mb-3" placeholder="Mis. INPARTU Kala I fase aktif"></textarea>

                <div class="text-muted fs-7 fw-bold text-uppercase mb-2">P — Plan</div>
                <textarea name="plan" rows="2" class="form-control form-control-solid mb-3" placeholder="Mis. Pimpin mengejan bila ada his, observasi DJJ tiap 30 menit"></textarea>

                <label class="form-label fs-7">Catatan</label>
                <input type="text" name="notes" class="form-control form-control-solid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ki-outline ki-check fs-3"></i> Simpan SOAP</button>
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
    // ===== Quick Actions Siklus Rujukan =====
    $('.action-rujuk').on('click', function(e) {
        e.preventDefault();
        const action = $(this).data('action');
        const confirmText = $(this).data('confirm') || 'Lanjutkan aksi?';
        const isFinish = action === 'selesai';
        const isCancel = action === 'batal';

        Swal.fire({
            title: isFinish ? '🎯 Selesaikan Rujukan?' : (isCancel ? '⛔ Batalkan?' : 'Konfirmasi'),
            html: confirmText,
            icon: isFinish ? 'success' : (isCancel ? 'warning' : 'question'),
            showCancelButton: true,
            confirmButtonText: isFinish ? '✅ Ya, Selesaikan' : (isCancel ? 'Ya, Batalkan' : 'Lanjutkan'),
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-' + (isFinish ? 'success' : (isCancel ? 'danger' : 'primary')),
                cancelButton: 'btn btn-secondary'
            }
        }).then(r => {
            if (! r.isConfirmed) return;
            // Submit via dynamic form
            const $form = $('<form>', { method: 'POST', action: '{{ route("admin.inc.set-rujuk-siklus", $delivery) }}' });
            $form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
            $form.append('<input type="hidden" name="action" value="' + action + '">');
            $('body').append($form);
            $form.submit();
        });
    });

    $('.form-del-soap').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus catatan SOAP?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
