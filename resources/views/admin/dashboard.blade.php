@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
@php
    $user = $currentUser ?? auth()->user();
    $initial = strtoupper(mb_substr($user->full_name, 0, 1));
@endphp

{{-- Welcome Card --}}
<div class="card mb-5 mb-xl-10">
    <div class="card-body pt-9 pb-0">
        <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
            <div class="me-7 mb-4">
                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                    <div class="symbol-label fs-1 bg-light-primary text-primary fw-bold">{{ $initial }}</div>
                    <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-white h-20px w-20px"></div>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-gray-900 fs-2 fw-bold me-2">{{ $user->full_name }}</span>
                            <span class="badge badge-light-success me-1">{{ ucfirst($user->role->name ?? '—') }}</span>
                            @if($isSuper)
                                <span class="badge badge-light-danger ms-1"><i class="ki-outline ki-shield-tick"></i> Super Admin</span>
                            @else
                                <span class="badge badge-light-info ms-1"><i class="ki-outline ki-shop"></i> {{ $currentSite?->name }}</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                            <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                <i class="ki-outline ki-profile-circle fs-4 me-1"></i>{{ $user->username }}
                            </span>
                            <span class="d-flex align-items-center text-gray-500 me-5 mb-2">
                                <i class="ki-outline ki-sms fs-4 me-1"></i>{{ $user->email ?? '—' }}
                            </span>
                            @if($user->phone)
                                <span class="d-flex align-items-center text-gray-500 mb-2">
                                    <i class="ki-outline ki-phone fs-4 me-1"></i>{{ $user->phone }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4 Stat Cards --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <div class="col-md-6 col-lg-3">
        <div class="card card-flush h-md-100" style="background-color: #1e88e5; background-image: url({{ asset('assets/media/patterns/vector-1.png') }})">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($stats['patients']) }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Pasien</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($stats['doctors']) }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Dokter</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <i class="ki-outline ki-pulse fs-3hx text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($stats['services']) }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Layanan</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <i class="ki-outline ki-pulse fs-3hx text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card card-flush h-md-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($stats['medicines']) }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Obat</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <i class="ki-outline ki-capsule fs-3hx text-gray-300"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-5">
    {{-- Super admin only: list semua klinik --}}
    @if($isSuper)
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Daftar Klinik</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $sites->count() }} klinik terdaftar</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    @forelse($sites as $site)
                        <div class="d-flex align-items-center mb-7">
                            <div class="symbol symbol-50px me-5">
                                <span class="symbol-label bg-light-info">
                                    <i class="ki-outline ki-shop fs-2x text-info"></i>
                                </span>
                            </div>
                            <div class="d-flex flex-column flex-grow-1">
                                <span class="text-gray-800 fw-bold fs-6">{{ $site->name }}</span>
                                <span class="text-gray-500 fw-semibold fs-7">{{ $site->code }} &middot; {{ $site->city ?? '—' }}</span>
                            </div>
                            <span class="badge badge-light-{{ $site->is_active ? 'success' : 'danger' }} fw-bold">
                                {{ $site->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada klinik terdaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-7">
    @else
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Info Klinik</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $currentSite?->name }}</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    @if($currentSite)
                        <div class="d-flex flex-stack mb-3">
                            <span class="text-muted">Kode:</span>
                            <span class="fw-bold">{{ $currentSite->code }}</span>
                        </div>
                        <div class="d-flex flex-stack mb-3">
                            <span class="text-muted">Kota:</span>
                            <span class="fw-bold">{{ $currentSite->city ?? '—' }}</span>
                        </div>
                        <div class="d-flex flex-stack mb-3">
                            <span class="text-muted">Phone:</span>
                            <span class="fw-bold">{{ $currentSite->phone ?? '—' }}</span>
                        </div>
                        <div class="d-flex flex-stack mb-3">
                            <span class="text-muted">Email:</span>
                            <span class="fw-bold">{{ $currentSite->email ?? '—' }}</span>
                        </div>
                        <div class="separator my-3"></div>
                        <div class="text-muted fs-7">{{ $currentSite->address }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-7">
    @endif

        {{-- Recent login attempts --}}
        <div class="card h-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Aktivitas Login Terakhir</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ $recentLogins->count() }} percobaan terbaru</span>
                </h3>
            </div>
            <div class="card-body pt-5">
                <table class="table table-row-bordered table-row-gray-200 align-middle gy-3 gs-0">
                    <thead>
                        <tr class="text-gray-500 fw-bold fs-7 text-uppercase">
                            <th>Username</th>
                            <th>IP</th>
                            <th>Status</th>
                            <th class="text-end">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold fs-6 text-gray-700">
                        @forelse ($recentLogins as $log)
                            <tr>
                                <td>{{ $log->username ?? '—' }}</td>
                                <td><span class="text-muted fs-7">{{ $log->ip_address }}</span></td>
                                <td>
                                    @if ($log->success)
                                        <span class="badge badge-light-success">Sukses</span>
                                    @else
                                        <span class="badge badge-light-danger" title="{{ $log->failure_reason }}">
                                            {{ $log->failure_reason }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end text-muted fs-7">
                                    {{ \Carbon\Carbon::parse($log->attempted_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada aktivitas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Info card: Status development --}}
<div class="card mt-5 bg-light-info">
    <div class="card-body d-flex align-items-center">
        <i class="ki-outline ki-information-5 fs-3hx text-info me-5"></i>
        <div>
            <h5 class="text-gray-800 mb-1">Phase 0 — Master & Auth</h5>
            <p class="text-muted mb-0 fs-7">
                Modul transaksi (rekam medis, pembayaran, antrian) akan dibuat setelah ada contoh format manual dari klinik.
                Saat ini fokus: master pasien, dokter, layanan, obat, jadwal praktek.
            </p>
        </div>
    </div>
</div>
@endsection
