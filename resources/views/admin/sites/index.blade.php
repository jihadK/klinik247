@extends('admin.layouts.app')
@section('title', 'Master Klinik')
@section('page_title', 'Master Klinik (Site)')

@section('content')
<div class="card">
    <div class="card-header pt-4">
        <h3 class="card-title">
            🏥 Daftar Klinik
            @if(auth()->user()->isSuperAdmin())
                <span class="badge badge-light-primary fs-8 ms-2">Super Admin — semua klinik</span>
            @else
                <span class="badge badge-light-info fs-8 ms-2">Hanya klinik Anda</span>
            @endif
        </h3>
    </div>
    <div class="card-body">
        <div class="row g-4">
            @forelse($sites as $s)
                <div class="col-md-6 col-lg-4">
                    <div class="card border h-100">
                        <div class="card-body text-center">
                            @if($s->logo_url)
                                <img src="{{ asset('storage/'.$s->logo_url) }}" alt="logo" style="max-height:80px; max-width:120px; object-fit:contain;" class="mb-3">
                            @else
                                <div class="symbol symbol-80px mb-3 mx-auto">
                                    <span class="symbol-label bg-light-primary text-primary fs-1 fw-bolder">{{ mb_substr($s->name, 0, 2) }}</span>
                                </div>
                            @endif
                            <h4 class="mb-1">{{ $s->name }}</h4>
                            <div class="text-muted fs-7">{{ $s->code }} · {{ $s->city ?? '-' }}</div>
                            @if($s->letterhead_subtitle)
                                <div class="text-muted fs-8 fst-italic mt-1">{{ $s->letterhead_subtitle }}</div>
                            @endif

                            <div class="separator my-3"></div>
                            <div class="text-start fs-8 text-muted">
                                <div>📍 {{ $s->address ?? '-' }}</div>
                                <div>📞 {{ $s->phone ?? '-' }}</div>
                                <div>✉ {{ $s->email ?? '-' }}</div>
                                @if($s->letterhead_director)<div class="mt-1">👤 PJ: <b>{{ $s->letterhead_director }}</b></div>@endif
                                @if($s->letterhead_sipb)<div>🪪 SIPB: {{ $s->letterhead_sipb }}</div>@endif
                            </div>

                            <div class="d-flex gap-2 mt-3">
                                @if($s->is_active)
                                    <span class="badge badge-light-success">Aktif</span>
                                @else
                                    <span class="badge badge-light-danger">Non-Aktif</span>
                                @endif
                                @if($s->kop_image_url)
                                    <span class="badge badge-light-info">✓ Kop Surat</span>
                                @endif
                            </div>

                            @if(auth()->user()->hasPermission('sites.update'))
                                <a href="{{ route('admin.sites.edit', $s) }}" class="btn btn-sm btn-light-warning w-100 mt-3">
                                    <i class="ki-outline ki-pencil fs-3"></i> Edit Pengaturan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-10">Tidak ada data klinik.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')<x-sweet-flash />@endpush
