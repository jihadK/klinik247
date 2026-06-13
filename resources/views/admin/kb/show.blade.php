@extends('admin.layouts.app')

@section('title', 'Akseptor KB — '.$acceptor->no_kartu_kb)
@section('page_title', 'Detail Akseptor KB')

@section('content')

{{-- ===== Banner LOCKED: status non-aktif → tampilkan alasan + link ke acceptor pengganti ===== --}}
@if($acceptor->status !== 'aktif')
    @php
        $nextAcc = $acceptor->nextAcceptor()->with('kontrasepsi')->first();
        $statusInfo = [
            'ganti_metode' => ['icon' => 'ki-arrows-circle',  'color' => 'warning', 'title' => 'Akseptor Sudah Ganti Metode'],
            'drop'         => ['icon' => 'ki-cross-circle',   'color' => 'danger',  'title' => 'Akseptor Drop Out'],
            'selesai'      => ['icon' => 'ki-check-circle',   'color' => 'success', 'title' => 'Akseptor Selesai Program'],
        ];
        $info = $statusInfo[$acceptor->status] ?? ['icon' => 'ki-information-5', 'color' => 'secondary', 'title' => 'Akseptor Tidak Aktif'];
    @endphp
    <div class="alert alert-{{ $info['color'] }} d-flex align-items-start gap-4 p-5 mb-5 border border-{{ $info['color'] }} border-2">
        <i class="ki-outline {{ $info['icon'] }} fs-3x text-{{ $info['color'] }}"></i>
        <div class="flex-grow-1">
            <h4 class="mb-2 text-{{ $info['color'] }}">
                <i class="ki-outline ki-lock fs-2 me-1"></i> {{ $info['title'] }} — Data Terkunci
            </h4>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="text-muted fs-8 fw-semibold text-uppercase">Tanggal Dilepas</div>
                    <div class="fw-bold fs-6">{{ optional($acceptor->tanggal_dilepas)->isoFormat('D MMMM YYYY') ?: '-' }}</div>
                </div>
                <div class="col-md-8">
                    <div class="text-muted fs-8 fw-semibold text-uppercase">Alasan</div>
                    <div class="fw-bold fs-6">{{ $acceptor->drop_reason ?: 'Tidak dicantumkan' }}</div>
                </div>
            </div>

            @if($nextAcc)
                <div class="separator separator-dashed my-3"></div>
                <div class="d-flex align-items-center gap-3">
                    <i class="ki-outline ki-arrow-right-square fs-2 text-{{ $info['color'] }}"></i>
                    <div class="flex-grow-1">
                        <div class="text-muted fs-8 fw-semibold text-uppercase">Diganti ke akseptor baru</div>
                        <a href="{{ route('admin.kb.show', $nextAcc) }}" class="fw-bold fs-5 text-{{ $info['color'] }}">
                            {{ $nextAcc->no_kartu_kb }} — {{ $nextAcc->kontrasepsi?->name }}
                        </a>
                        <div class="fs-8 text-muted">Mulai {{ optional($nextAcc->tanggal_dilayani)->isoFormat('D MMM YYYY') }}</div>
                    </div>
                    <a href="{{ route('admin.kb.show', $nextAcc) }}" class="btn btn-sm btn-{{ $info['color'] }}">
                        Lihat Akseptor Baru <i class="ki-outline ki-arrow-right fs-3 ms-1"></i>
                    </a>
                </div>
            @endif

            <div class="fs-8 text-muted mt-3">
                <i class="ki-outline ki-shield-tick fs-4 me-1"></i>
                Data akseptor ini terkunci untuk audit & pelaporan. Edit & tambah kunjungan tidak diperbolehkan.
                Untuk koreksi data, hubungi super_admin.
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        @php
            // Mapping alat → icon + color theme
            $alatTheme = [
                'KTR-KONDOM' => ['icon' => 'ki-shield-tick',    'color' => 'success', 'gradient' => 'linear-gradient(135deg,#50cd89 0%,#36b37e 100%)'],
                'KTR-PIL'    => ['icon' => 'ki-pill',           'color' => 'warning', 'gradient' => 'linear-gradient(135deg,#ffc700 0%,#f6a609 100%)'],
                'KTR-SUNTIK' => ['icon' => 'ki-syringe',        'color' => 'info',    'gradient' => 'linear-gradient(135deg,#7239ea 0%,#5014d0 100%)'],
                'KTR-IUD'    => ['icon' => 'ki-tablet',         'color' => 'danger',  'gradient' => 'linear-gradient(135deg,#f1416c 0%,#d9214e 100%)'],
                'KTR-IMPLAN' => ['icon' => 'ki-capsule',        'color' => 'primary', 'gradient' => 'linear-gradient(135deg,#009ef7 0%,#0073d1 100%)'],
            ];
            $code  = $acceptor->kontrasepsi?->code;
            $theme = $alatTheme[$code] ?? ['icon' => 'ki-medical-cross', 'color' => 'secondary', 'gradient' => 'linear-gradient(135deg,#7e8299 0%,#5e6278 100%)'];
        @endphp

        {{-- Kartu Identitas --}}
        <div class="card mb-5">
            <div class="card-body text-center">
                <span class="badge badge-light-{{ $acceptor->status_color }} fs-6 mb-3">{{ $acceptor->status_label }}</span>
                <h3 class="mb-1">{{ $acceptor->patient?->name }}</h3>
                <div class="text-muted fs-7">{{ $acceptor->patient?->no_rm }} · {{ $acceptor->patient?->age }}</div>
                <div class="fs-2x fw-bolder text-primary mt-3 font-monospace">{{ $acceptor->no_kartu_kb }}</div>

                {{-- ===== Highlight Alat Kontrasepsi ===== --}}
                <div class="my-4 p-3 rounded text-white shadow-sm" style="background: {{ $theme['gradient'] }};">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <span class="symbol symbol-50px">
                            <span class="symbol-label bg-white bg-opacity-25">
                                <i class="ki-outline {{ $theme['icon'] }} fs-2x text-white"></i>
                            </span>
                        </span>
                        <div class="text-start">
                            <div class="text-white-50 fs-8 fw-semibold text-uppercase ls-1">Alat Kontrasepsi</div>
                            <div class="fs-3 fw-bolder text-white">{{ $acceptor->kontrasepsi?->name ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="text-muted fs-7">📅 Tgl Layanan: <b class="text-dark">{{ optional($acceptor->tanggal_dilayani)->isoFormat('D MMM YYYY') }}</b></div>
                @if($acceptor->tanggal_pesan_kontrol)
                    @php $daysToControl = (int) now()->startOfDay()->diffInDays($acceptor->tanggal_pesan_kontrol->startOfDay(), false); @endphp
                    <div class="text-muted fs-7 mt-1">
                        🔔 Kontrol Berikutnya: <b class="text-dark">{{ $acceptor->tanggal_pesan_kontrol->isoFormat('D MMM YYYY') }}</b>
                        @if($daysToControl < 0)
                            <span class="badge badge-light-danger fs-9 ms-1">Lewat {{ abs($daysToControl) }} hari</span>
                        @elseif($daysToControl <= 7)
                            <span class="badge badge-light-warning fs-9 ms-1">{{ $daysToControl }} hari lagi</span>
                        @else
                            <span class="badge badge-light-success fs-9 ms-1">{{ $daysToControl }} hari lagi</span>
                        @endif
                    </div>
                @endif
                @if($acceptor->tanggal_dilepas)
                    <div class="text-muted fs-7 mt-1">🗓️ Dilepas: <b class="text-danger">{{ $acceptor->tanggal_dilepas->isoFormat('D MMM YYYY') }}</b></div>
                @endif

                <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                    <a href="{{ route('admin.kb.kartu', $acceptor) }}" target="_blank" class="btn btn-sm btn-light-info">
                        <i class="ki-outline ki-printer fs-3"></i> Cetak Kartu
                    </a>
                    @if(auth()->user()->hasPermission('kb.update') && $acceptor->status === 'aktif')
                        <a href="{{ route('admin.kb.edit', $acceptor) }}" class="btn btn-sm btn-light-warning">
                            <i class="ki-outline ki-pencil fs-3"></i> Edit
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('kb.create') && $acceptor->status === 'aktif')
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_ganti_alat">
                            <i class="ki-outline ki-arrows-circle fs-3"></i> Ganti Alat
                        </button>
                    @endif
                    @if($acceptor->status !== 'aktif')
                        <span class="btn btn-sm btn-light-secondary disabled" title="Data terkunci untuk audit">
                            <i class="ki-outline ki-lock fs-3"></i> Terkunci
                        </span>
                    @endif
                </div>
                <a href="{{ route('admin.kb.index') }}" class="btn btn-sm btn-light w-100 mt-2">← Kembali</a>

                {{-- ===== History Ganti Alat ===== --}}
                @php
                    $prev = $acceptor->previousAcceptor;
                    $nexts = $acceptor->nextAcceptor()->with('kontrasepsi')->get();
                @endphp
                @if($prev || $nexts->count())
                    <div class="separator my-4"></div>
                    <div class="text-start">
                        <div class="text-muted fs-8 fw-semibold text-uppercase mb-2">📜 History Ganti Alat</div>
                        @if($prev)
                            <a href="{{ route('admin.kb.show', $prev) }}" class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none border border-gray-300 mb-2 bg-light-warning text-dark">
                                <i class="ki-outline ki-arrow-left fs-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fs-8 text-muted">Sebelumnya</div>
                                    <div class="fw-bold fs-7">{{ $prev->no_kartu_kb }}</div>
                                    <div class="fs-8">{{ $prev->kontrasepsi?->name }} · {{ optional($prev->tanggal_dilepas)->isoFormat('D MMM YY') }}</div>
                                </div>
                            </a>
                        @endif
                        @foreach($nexts as $next)
                            <a href="{{ route('admin.kb.show', $next) }}" class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none border border-gray-300 mb-2 bg-light-success text-dark">
                                <div class="flex-grow-1">
                                    <div class="fs-8 text-muted">Berikutnya</div>
                                    <div class="fw-bold fs-7">{{ $next->no_kartu_kb }}</div>
                                    <div class="fs-8">{{ $next->kontrasepsi?->name }} · {{ optional($next->tanggal_dilayani)->isoFormat('D MMM YY') }}</div>
                                </div>
                                <i class="ki-outline ki-arrow-right fs-3"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Identitas Suami</h3></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Nama</span><b>{{ $acceptor->suami_name ?? '-' }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Umur</span><b>{{ $acceptor->suami_age ?? '-' }} th</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Pendidikan</span><b>{{ optional($acceptor->suamiEducation)->name ?? '-' }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Pekerjaan</span><b>{{ $acceptor->suami_occupation ?? '-' }}</b></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Kawin ke</span><b>{{ $acceptor->suami_kawin_ke ?? '-' }}</b></div>
            </div>
        </div>

        @if($acceptor->consent_signed)
            <div class="alert alert-success">
                ✓ Informed consent sudah ditandatangani<br>
                <small class="text-muted">{{ optional($acceptor->consent_signed_at)->isoFormat('D MMM YYYY HH:mm') }} · Saksi: {{ $acceptor->consent_witness ?? '-' }}</small>
            </div>
        @else
            <div class="alert alert-warning">⚠ Informed consent belum ditandatangani</div>
        @endif
    </div>

    <div class="col-md-8">
        {{-- Status Peserta KB Baru --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">A. Status Peserta KB</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted fs-7">Jumlah Anak Hidup</div><div class="fw-semibold">{{ $acceptor->jumlah_anak_hidup ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted fs-7">Ingin Anak Lagi</div><div class="fw-semibold">{{ ucwords(str_replace('_',' ', $acceptor->keinginan_punya_anak_lagi ?? '-')) }}</div></div>
                    <div class="col-md-4"><div class="text-muted fs-7">Kapan</div><div class="fw-semibold">{{ $acceptor->kapan_ingin_anak_lagi ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted fs-7">Kehamilan Saat Ini</div><div class="fw-semibold">{{ ucwords(str_replace('_',' ', $acceptor->status_kehamilan_saat_ini ?? '-')) }}</div></div>
                    <div class="col-md-4"><div class="text-muted fs-7">Sikap Pasangan</div><div class="fw-semibold">{{ ucwords(str_replace('_',' ', $acceptor->sikap_pasangan_terhadap_kb ?? '-')) }}</div></div>
                    <div class="col-md-4"><div class="text-muted fs-7">Riwayat Komplikasi</div><div class="fw-semibold">{{ $acceptor->riwayat_komplikasi_kehamilan ?? '-' }}</div></div>
                    <div class="col-md-6"><span class="badge {{ $acceptor->edukasi_hiv_aids_pms ? 'badge-light-success' : 'badge-light-secondary' }}">{{ $acceptor->edukasi_hiv_aids_pms ? '✓' : '✗' }} Edukasi HIV/AIDS/PMS</span></div>
                    <div class="col-md-6"><span class="badge {{ $acceptor->metode_ganda_pakai_kondom ? 'badge-light-success' : 'badge-light-secondary' }}">{{ $acceptor->metode_ganda_pakai_kondom ? '✓' : '✗' }} Metode Ganda (Kondom)</span></div>
                </div>
            </div>
        </div>

        {{-- Pemeriksaan Awal --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">B. Pemeriksaan Awal</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><div class="text-muted fs-7">TD</div><div class="fw-semibold">{{ $acceptor->tekanan_darah ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="text-muted fs-7">BB</div><div class="fw-semibold">{{ $acceptor->berat_badan ?? '-' }} kg</div></div>
                    <div class="col-md-3"><div class="text-muted fs-7">Haid Terakhir</div><div class="fw-semibold">{{ optional($acceptor->haid_terakhir)->isoFormat('D MMM YY') }}</div></div>
                    <div class="col-md-3"><div class="text-muted fs-7">Partus Terakhir</div><div class="fw-semibold">{{ optional($acceptor->tanggal_persalinan_terakhir)->isoFormat('D MMM YY') }}</div></div>
                </div>
                <div class="separator my-3"></div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @if($acceptor->kebiasaan_merokok)<span class="badge badge-warning">Merokok</span>@endif
                    @if($acceptor->sedang_menyusui)<span class="badge badge-info">Sedang Menyusui</span>@endif
                    @if($acceptor->sakit_kuning)<span class="badge badge-danger">Sakit Kuning</span>@endif
                    @if($acceptor->perdarahan_per_vaginam)<span class="badge badge-danger">Perd. Pervaginam</span>@endif
                    @if($acceptor->tumor_payudara)<span class="badge badge-danger">Tumor Payudara</span>@endif
                </div>
                @if($acceptor->keluhan)
                    <div class="mb-2"><div class="text-muted fs-7">Keluhan</div><div>{{ $acceptor->keluhan }}</div></div>
                @endif
                @php
                    $fluo = collect([
                        $acceptor->fluoralbus_gatal ? 'Gatal' : null,
                        $acceptor->fluoralbus_seperti_susu ? 'Seperti Susu' : null,
                        $acceptor->fluoralbus_busa ? 'Busa' : null,
                        $acceptor->fluoralbus_cair ? 'Cair' : null,
                    ])->filter()->values();
                @endphp
                @if($fluo->isNotEmpty())
                    <div><div class="text-muted fs-7">Fluoralbus</div><div>{{ $fluo->implode(', ') }}</div></div>
                @endif
            </div>
        </div>

        {{-- IUD Specific --}}
        @if($acceptor->is_iud)
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">C. Pemeriksaan IUD</h3></div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if($acceptor->iud_tanda_radang)<span class="badge badge-warning">Tanda Radang</span>@endif
                        @if($acceptor->iud_tumor)<span class="badge badge-warning">Tumor</span>@endif
                        @if($acceptor->iud_posisi_rahim)<span class="badge badge-info">Posisi Rahim: {{ ucwords($acceptor->iud_posisi_rahim) }}</span>@endif
                        @if($acceptor->iud_genetalia_varices)<span class="badge badge-secondary">Varices</span>@endif
                        @if($acceptor->iud_genetalia_jengger)<span class="badge badge-secondary">Jengger</span>@endif
                        @if($acceptor->iud_genetalia_condilo)<span class="badge badge-secondary">Condilo</span>@endif
                        @if($acceptor->iud_genetalia_bartholinitis)<span class="badge badge-secondary">Bartholinitis</span>@endif
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== Kunjungan Ulang ===== --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">Kunjungan Ulang</h3>
                @if(auth()->user()->hasPermission('kb.visit') && $acceptor->status === 'aktif')
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_kb_visit">
                            <i class="ki-outline ki-plus fs-3"></i> Tambah Kunjungan
                        </button>
                    </div>
                @elseif($acceptor->status !== 'aktif')
                    <div class="card-toolbar text-muted fs-8">
                        <i class="ki-outline ki-lock fs-3 me-1"></i> Tidak bisa tambah (akseptor terkunci)
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted bg-light-secondary">
                                <th>Tgl</th>
                                <th>Haid Tgl</th>
                                <th>BB</th>
                                <th>TD</th>
                                <th>Keluhan</th>
                                <th>Tindakan</th>
                                <th>Tgl Kembali</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($acceptor->visits as $v)
                                <tr>
                                    <td>{{ $v->visit_date?->isoFormat('D MMM YY') }}</td>
                                    <td>{{ optional($v->haid_tanggal)->isoFormat('D MMM YY') ?? '-' }}</td>
                                    <td>{{ $v->berat_badan ?? '-' }}</td>
                                    <td>{{ $v->tekanan_darah ?? '-' }}</td>
                                    <td>
                                        {{ \Illuminate\Support\Str::limit($v->keluhan, 40) ?: '-' }}
                                        @if($v->efek_samping)<div class="text-warning fs-8">ES: {{ \Illuminate\Support\Str::limit($v->efek_samping, 30) }}</div>@endif
                                        @if($v->komplikasi)<div class="text-danger fs-8">Komp: {{ \Illuminate\Support\Str::limit($v->komplikasi, 30) }}</div>@endif
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($v->tindakan, 40) ?: '-' }}</td>
                                    <td>{{ optional($v->tanggal_kembali)->isoFormat('D MMM YY') ?? '-' }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-icon btn-light-info btn-show-visit"
                                                data-bs-toggle="modal" data-bs-target="#modal_visit_detail"
                                                data-no="{{ $loop->iteration }}"
                                                data-tanggal="{{ optional($v->visit_date)->isoFormat('dddd, D MMM YYYY') }}"
                                                data-haid="{{ optional($v->haid_tanggal)->isoFormat('D MMM YYYY') }}"
                                                data-bb="{{ $v->berat_badan }}"
                                                data-td="{{ $v->tekanan_darah }}"
                                                data-keluhan="{{ $v->keluhan }}"
                                                data-efek="{{ $v->efek_samping }}"
                                                data-komplikasi="{{ $v->komplikasi }}"
                                                data-tindakan="{{ $v->tindakan }}"
                                                data-kembali="{{ optional($v->tanggal_kembali)->isoFormat('D MMM YYYY') }}"
                                                data-notes="{{ $v->notes }}"
                                                data-served="{{ optional($v->servedBy)->full_name }}"
                                                data-created="{{ optional($v->created_date)->isoFormat('D MMM YYYY HH:mm') }}"
                                                title="Detail">
                                            <i class="ki-outline ki-eye fs-3"></i>
                                        </button>
                                        @if(auth()->user()->hasPermission('kb.visit'))
                                            <form action="{{ route('admin.kb.visit.destroy', $v) }}" method="POST" class="d-inline form-del-visit">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-icon btn-light-danger" title="Hapus"><i class="ki-outline ki-trash fs-3"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-5">Belum ada kunjungan ulang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal Ganti Alat Kontrasepsi ===== --}}
@php
    $kontrasepsiList = \App\Models\KontrasepsiMethod::active()->get();
    $hariSejakLayani = (int) now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($acceptor->tanggal_dilayani)->startOfDay());
@endphp
<div class="modal fade" id="modal_ganti_alat" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.kb.ganti-alat', $acceptor) }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="current_kontrasepsi_id" value="{{ $acceptor->kontrasepsi_id }}">

            <div class="modal-header bg-light-primary">
                <div>
                    <h3 class="mb-0"><i class="ki-outline ki-arrows-circle fs-2 text-primary me-2"></i> Ganti Alat Kontrasepsi</h3>
                    <div class="text-muted fs-7 mt-1">Acceptor: <b>{{ $acceptor->no_kartu_kb }}</b> · Saat ini: <b>{{ $acceptor->kontrasepsi?->name }}</b></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- Warning kalau alat lama < 30 hari --}}
                @if($hariSejakLayani < 30)
                    <div class="alert alert-warning d-flex align-items-start gap-3">
                        <i class="ki-outline ki-shield-warning fs-2 text-warning"></i>
                        <div>
                            <b>⚠ Perhatian:</b> Alat saat ini ({{ $acceptor->kontrasepsi?->name }}) baru dipakai
                            <b>{{ $hariSejakLayani }} hari</b> sejak {{ optional($acceptor->tanggal_dilayani)->isoFormat('D MMM YYYY') }}.
                            Ganti alat dalam waktu kurang dari 1 bulan biasanya hanya untuk kasus efek samping berat atau komplikasi.
                            Pastikan keputusan ini sudah dipertimbangkan dengan pasien.
                        </div>
                    </div>
                @endif

                <div class="alert alert-info py-3 fs-7">
                    <b>ℹ Otomatis:</b> Data <b>suami</b>, <b>status peserta KB</b>, dan <b>riwayat pemeriksaan</b> akan di-copy ke acceptor baru.
                    Pemeriksaan vital (TD, BB, haid) akan diambil dari kunjungan terakhir kalau ada.
                    Anda bisa edit data setelah ganti alat dibuat.
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 required">Alat Kontrasepsi Baru</label>
                        <select name="new_kontrasepsi_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih alat baru..." required>
                            <option></option>
                            @foreach($kontrasepsiList as $k)
                                <option value="{{ $k->id }}" data-code="{{ $k->code }}" @disabled($k->id === $acceptor->kontrasepsi_id)>
                                    {{ $k->name }} @if($k->id === $acceptor->kontrasepsi_id) (alat saat ini) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 required">Tgl Alat Lama Dilepas</label>
                        <input type="date" name="tanggal_dilepas" value="{{ today()->format('Y-m-d') }}"
                               class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 required">Tgl Alat Baru Dipasang</label>
                        <input type="date" name="tanggal_dilayani_baru" id="ga_tgl_dilayani" value="{{ today()->format('Y-m-d') }}"
                               class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">Tgl Kontrol Berikutnya</label>
                        <input type="date" name="tanggal_pesan_kontrol_baru" id="ga_tgl_kontrol"
                               class="form-control form-control-solid">
                        <div class="form-text fs-9">Auto-suggest sesuai alat (bisa diubah)</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 required">Alasan Ganti Alat</label>
                        <textarea name="alasan_ganti" rows="2" class="form-control form-control-solid"
                                  placeholder="Mis. Efek samping pil (sakit kepala), permintaan akseptor, jadwal lepas IUD, dll..." required></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-arrows-circle fs-3"></i> Ganti Alat & Buat Akseptor Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Kunjungan --}}
<div class="modal fade" id="modal_visit_detail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light-info">
                <div>
                    <h3 class="mb-0"><i class="ki-outline ki-clipboard fs-2 text-info me-2"></i> Detail Kunjungan KB #<span id="vd_no">-</span></h3>
                    <div class="text-muted fs-7 mt-1"><span id="vd_tanggal">-</span></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Vital Sign Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border border-gray-300 h-100">
                            <div class="card-body py-3 text-center">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Haid Tanggal</div>
                                <div class="fw-bold fs-5" id="vd_haid">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border border-gray-300 h-100">
                            <div class="card-body py-3 text-center">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Berat Badan</div>
                                <div class="fw-bold fs-5"><span id="vd_bb">-</span> <span class="fs-7 text-muted">kg</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border border-gray-300 h-100">
                            <div class="card-body py-3 text-center">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Tekanan Darah</div>
                                <div class="fw-bold fs-5"><span id="vd_td">-</span> <span class="fs-7 text-muted">mmHg</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border border-gray-300 h-100">
                            <div class="card-body py-3 text-center">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Tgl Kembali</div>
                                <div class="fw-bold fs-5" id="vd_kembali">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Klinis Section --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="text-muted fs-8 fw-semibold text-uppercase mb-1">Keluhan</div>
                        <div class="p-3 bg-light-primary rounded text-dark" id="vd_keluhan" style="min-height: 60px;">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-8 fw-semibold text-uppercase mb-1">Tindakan</div>
                        <div class="p-3 bg-light-success rounded text-dark" id="vd_tindakan" style="min-height: 60px;">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-8 fw-semibold text-uppercase mb-1">Efek Samping</div>
                        <div class="p-3 bg-light-warning rounded text-dark" id="vd_efek" style="min-height: 60px;">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-8 fw-semibold text-uppercase mb-1">Komplikasi</div>
                        <div class="p-3 bg-light-danger rounded text-dark" id="vd_komplikasi" style="min-height: 60px;">-</div>
                    </div>
                </div>

                {{-- Catatan + Meta --}}
                <div class="mb-3">
                    <div class="text-muted fs-8 fw-semibold text-uppercase mb-1">Catatan</div>
                    <div class="p-3 bg-light rounded" id="vd_notes" style="min-height: 40px;">-</div>
                </div>

                <div class="d-flex justify-content-between border-top pt-3 fs-8 text-muted">
                    <div>👨‍⚕️ Dilayani oleh: <b id="vd_served">-</b></div>
                    <div>🕐 Dicatat: <b id="vd_created">-</b></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Kunjungan --}}
<div class="modal fade" id="modal_kb_visit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.kb.visit.store', $acceptor) }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="acceptor_id" value="{{ $acceptor->id }}">
            <div class="modal-header"><h3>Catat Kunjungan Ulang KB</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- ===== Row 1: Vital sign (Tanggal, Haid, BB, TD) ===== --}}
                <div class="row g-3 mb-2">
                    <div class="col-md-3">
                        <label class="form-label fs-7 required">Tanggal Kunjungan</label>
                        <input type="date" name="visit_date" value="{{ today()->format('Y-m-d') }}" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Haid Tanggal</label>
                        <input type="date" name="haid_tanggal" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7">BB (kg)</label>
                        <input type="number" step="0.1" name="berat_badan" class="form-control form-control-solid" placeholder="kg">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">Tekanan Darah</label>
                        <div class="input-group">
                            <input type="number" id="kv_td_sistol" min="50" max="300" class="form-control form-control-solid text-center" placeholder="Sistol">
                            <span class="input-group-text fw-bold">/</span>
                            <input type="number" id="kv_td_diastol" min="30" max="200" class="form-control form-control-solid text-center" placeholder="Diastol">
                            <span class="input-group-text fw-semibold">mmHg</span>
                        </div>
                        <input type="hidden" name="tekanan_darah" id="kv_tekanan_darah">
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>

                {{-- ===== Row 2: Klinis (Keluhan + Efek Samping + Komplikasi) ===== --}}
                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fs-7">Keluhan</label>
                        <textarea name="keluhan" rows="3" class="form-control form-control-solid" placeholder="Mis. nyeri perut, pusing..."></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">Efek Samping</label>
                        <textarea name="efek_samping" rows="3" class="form-control form-control-solid" placeholder="Mis. mual, perdarahan ringan..."></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">Komplikasi</label>
                        <textarea name="komplikasi" rows="3" class="form-control form-control-solid" placeholder="Komplikasi serius (jika ada)"></textarea>
                    </div>
                </div>

                {{-- ===== Row 3: Tindakan + Jadwal + Catatan ===== --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fs-7">Tindakan</label>
                        <textarea name="tindakan" rows="2" class="form-control form-control-solid" placeholder="Mis. lanjut suntik, edukasi..."></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Tanggal Kembali</label>
                        <input type="date" name="tanggal_kembali" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Catatan</label>
                        <input type="text" name="notes" class="form-control form-control-solid" placeholder="(opsional)">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="ki-outline ki-check fs-3"></i> Simpan Kunjungan</button>
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
    // Combine TD di modal kunjungan ulang
    function combineKvTd() {
        const s = $('#kv_td_sistol').val(), d = $('#kv_td_diastol').val();
        $('#kv_tekanan_darah').val(s && d ? s + '/' + d : '');
    }
    $('#kv_td_sistol, #kv_td_diastol').on('input', combineKvTd);

    // ===== Auto-suggest Tgl Kontrol Berikutnya saat ganti alat (sesuai jenis kontrasepsi) =====
    // Default interval per alat (hari):
    const kbControlIntervalDays = {
        'KTR-KONDOM': 0,      // tidak perlu kontrol rutin
        'KTR-PIL':    30,     // ambil pil tiap bulan
        'KTR-SUNTIK': 30,     // suntik 1 bulan (bidan bisa edit ke 90 kalau suntik 3-bulanan)
        'KTR-IUD':    365,    // cek tahunan
        'KTR-IMPLAN': 365,    // cek tahunan (masa pakai 3-5 thn)
    };
    function suggestKontrolGantiAlat() {
        const $opt = $('select[name=new_kontrasepsi_id] option:selected');
        const code = $opt.data('code') || $opt.text().trim();
        // Lookup code dari teks option (fallback) atau data-code
        const interval = kbControlIntervalDays[$opt.data('code') || ''] || 0;
        const tglDilayani = $('#ga_tgl_dilayani').val();
        if (! tglDilayani || ! interval) { $('#ga_tgl_kontrol').val(''); return; }
        const d = new Date(tglDilayani);
        d.setDate(d.getDate() + interval);
        $('#ga_tgl_kontrol').val(d.toISOString().slice(0, 10));
    }
    $('select[name=new_kontrasepsi_id], #ga_tgl_dilayani').on('change', suggestKontrolGantiAlat);

    // ===== Populate modal detail kunjungan =====
    $(document).on('click', '.btn-show-visit', function() {
        const $b = $(this);
        const fallback = '<span class="text-muted">-</span>';

        $('#vd_no').text($b.data('no'));
        $('#vd_tanggal').text($b.data('tanggal') || '-');
        $('#vd_haid').text($b.data('haid') || '-');
        $('#vd_bb').text($b.data('bb') || '-');
        $('#vd_td').text($b.data('td') || '-');
        $('#vd_kembali').text($b.data('kembali') || '-');

        $('#vd_keluhan').html($b.data('keluhan') || fallback);
        $('#vd_tindakan').html($b.data('tindakan') || fallback);
        $('#vd_efek').html($b.data('efek') || fallback);
        $('#vd_komplikasi').html($b.data('komplikasi') || fallback);
        $('#vd_notes').html($b.data('notes') || fallback);

        $('#vd_served').text($b.data('served') || '-');
        $('#vd_created').text($b.data('created') || '-');
    });

    $('.form-del-visit').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus catatan kunjungan?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' },
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
