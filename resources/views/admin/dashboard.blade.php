@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

{{-- ===== 4 Stat Cards ===== --}}
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <div class="col-6 col-lg-3">
        <div class="card card-flush h-100" style="background-color: #1e88e5; background-image: url({{ asset('assets/media/patterns/vector-1.png') }})">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ number_format($stats['patients']) }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Pasien</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <i class="ki-outline ki-people fs-3hx text-white opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-flush h-100">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($stats['doctors']) }}</span>
                    <span class="text-gray-500 pt-1 fw-semibold fs-6">Dokter / Bidan</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <i class="ki-outline ki-pulse fs-3hx text-gray-300"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-flush h-100">
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
    <div class="col-6 col-lg-3">
        <div class="card card-flush h-100">
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

{{-- ===== ANTRIAN HARI INI + JADWAL KUNJUNGAN ULANG ===== --}}
<div class="row g-5">

    {{-- ===== Antrian Kunjungan Hari Ini ===== --}}
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header pt-7 flex-wrap gap-2">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800 d-flex align-items-center">
                        <i class="ki-outline ki-people text-primary fs-2 me-2"></i>
                        Antrian Kunjungan Hari Ini
                    </span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-7">
                        Total <span class="text-primary fw-bold">{{ $queue['total'] }}</span> kunjungan ·
                        {{ \Carbon\Carbon::today()->isoFormat('dddd, D MMM YYYY') }}
                    </span>
                </h3>
                <div>
                    <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-light-primary">
                        Lihat Semua <i class="ki-outline ki-arrow-right fs-5"></i>
                    </a>
                </div>
            </div>

            <div class="card-body pt-5">
                {{-- Ringkasan per status (4 mini stat) --}}
                <div class="row g-2 mb-5">
                    @foreach(['waiting' => 'Menunggu', 'in_service' => 'Dilayani', 'done' => 'Selesai', 'no_show' => 'Tidak Hadir'] as $key => $label)
                        @php $s = $queue['by_status'][$key] ?? null; @endphp
                        @if($s)
                            <div class="col-6 col-md-3">
                                <div class="bg-light-{{ $s['color'] }} rounded p-3 h-100">
                                    <div class="fw-bold fs-2 text-{{ $s['color'] }}">{{ $s['total'] }}</div>
                                    <div class="text-gray-700 fs-8 fw-semibold">{{ $s['label'] }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Distribusi per kategori --}}
                <div class="separator mb-4"></div>
                <div class="text-gray-500 fw-bold fs-7 text-uppercase mb-3">Distribusi Per Kategori</div>
                <div class="row g-2 mb-5">
                    @foreach($queue['by_category'] as $key => $c)
                        <div class="col-6">
                            <div class="d-flex align-items-center bg-light-{{ $c['color'] }} rounded p-2">
                                <i class="ki-outline {{ $c['icon'] }} text-{{ $c['color'] }} fs-2 me-2"></i>
                                <div class="flex-grow-1">
                                    <div class="text-gray-700 fs-8 fw-semibold">{{ $c['label'] }}</div>
                                    <div class="fw-bold fs-5 text-{{ $c['color'] }}">{{ $c['total'] }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- List pasien aktif (Menunggu + Dilayani) --}}
                <div class="separator mb-4"></div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-gray-500 fw-bold fs-7 text-uppercase">Antrian Aktif</div>
                    <span class="badge badge-light-info">{{ $queue['active_list']->count() }}</span>
                </div>

                @forelse($queue['active_list'] as $v)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom border-gray-200">
                        <div class="symbol symbol-40px me-3 flex-shrink-0">
                            <span class="symbol-label bg-light-{{ $v->category_color }} text-{{ $v->category_color }} fw-bold">
                                {{ $v->queue_number ?? '—' }}
                            </span>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-gray-900 fw-bold fs-7 text-truncate">{{ $v->patient_name }}</div>
                            <div class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                <span class="text-muted fs-8 font-monospace">{{ $v->no_rm }}</span>
                                <span class="badge badge-light-{{ $v->category_color }} badge-sm">{{ $v->category_label }}</span>
                                <span class="badge badge-{{ $v->status_color }} badge-sm">{{ $v->status_label }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <i class="ki-outline ki-information-3 fs-3hx text-gray-300 d-block mb-2"></i>
                        <span class="text-muted fs-7">Tidak ada antrian aktif saat ini.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===== Jadwal Kunjungan Ulang ===== --}}
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header pt-7 flex-wrap gap-2">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800 d-flex align-items-center">
                        <i class="ki-outline ki-calendar-tick text-success fs-2 me-2"></i>
                        Jadwal Kunjungan Ulang
                    </span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-7">
                        Dijadwalkan dalam 3 hari ke depan ·
                        <span class="text-success fw-bold">{{ $upcomingVisits->count() }}</span> jadwal
                    </span>
                </h3>
            </div>
            <div class="card-body pt-3">

                {{-- DESKTOP TABLE --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-row-bordered table-row-gray-200 align-middle gy-3 gs-0 mb-0">
                        <thead>
                            <tr class="text-gray-500 fw-bold fs-7 text-uppercase">
                                <th>Pasien</th>
                                <th>Jenis</th>
                                <th class="text-nowrap">Jadwal</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold fs-6 text-gray-700">
                            @forelse ($upcomingVisits as $v)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold">{{ $v->name }}</span>
                                            <span class="text-muted fs-7 font-monospace">
                                                {{ $v->no_rm }}
                                                @if($v->phone)
                                                    &middot; <i class="ki-outline ki-phone fs-7"></i> {{ $v->phone }}
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ $v->color }} fw-bold">{{ $v->type }}</span>
                                        <div class="text-muted fs-8 mt-1">{{ $v->type_label }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="text-gray-800 fw-bold">{{ $v->date_human }}</div>
                                    </td>
                                    <td class="text-end">
                                        @if($v->days_left === 0)
                                            <span class="badge badge-danger fw-bold">{{ $v->day_label }}</span>
                                        @elseif($v->days_left === 1)
                                            <span class="badge badge-warning fw-bold">{{ $v->day_label }}</span>
                                        @else
                                            <span class="badge badge-light-primary fw-bold">{{ $v->day_label }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8">
                                        <i class="ki-outline ki-calendar-remove fs-3hx text-gray-300 d-block mb-3"></i>
                                        <span class="text-muted">Belum ada jadwal kunjungan ulang dalam 3 hari ke depan.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARDS --}}
                <div class="d-md-none">
                    @forelse ($upcomingVisits as $v)
                        <div class="border border-gray-200 rounded p-4 mb-3 bg-light-{{ $v->color }} bg-opacity-25">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1 me-2">
                                    <div class="text-gray-900 fw-bold fs-6">{{ $v->name }}</div>
                                    <div class="text-muted fs-7 font-monospace">{{ $v->no_rm }}</div>
                                </div>
                                @if($v->days_left === 0)
                                    <span class="badge badge-danger fw-bold flex-shrink-0">{{ $v->day_label }}</span>
                                @elseif($v->days_left === 1)
                                    <span class="badge badge-warning fw-bold flex-shrink-0">{{ $v->day_label }}</span>
                                @else
                                    <span class="badge badge-light-primary fw-bold flex-shrink-0">{{ $v->day_label }}</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge badge-light-{{ $v->color }} fw-bold">{{ $v->type }}</span>
                                <span class="text-muted fs-7 align-self-center">{{ $v->type_label }}</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center text-gray-700 fs-7">
                                <i class="ki-outline ki-calendar text-primary fs-5 me-1"></i>
                                <span class="fw-semibold">{{ $v->date_human }}</span>
                            </div>
                            @if($v->phone)
                                <div class="mt-2">
                                    <a href="tel:{{ $v->phone }}" class="btn btn-sm btn-light-success">
                                        <i class="ki-outline ki-phone fs-5"></i>{{ $v->phone }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="ki-outline ki-calendar-remove fs-3hx text-gray-300 d-block mb-3"></i>
                            <span class="text-muted">Belum ada jadwal kunjungan ulang dalam 3 hari ke depan.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
