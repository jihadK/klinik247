@extends('admin.layouts.app')

@section('title', 'Kunjungan Pasien')
@section('page_title', 'Kunjungan Pasien')

@section('content')

{{-- ===== Stats per Kategori Hari Ini ===== --}}
<div class="row g-4 mb-5">
    @foreach($categories as $code => $cat)
        @php
            $catStats = $todayStats->get($code, collect());
            $total = $catStats->sum('total');
            $waiting = $catStats->where('status','waiting')->sum('total');
            $done = $catStats->where('status','done')->sum('total');
        @endphp
        <div class="col-sm-6 col-lg-3">
            <div class="card border-{{ $cat['color'] }} border-2 h-100">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="symbol symbol-30px symbol-circle">
                                <span class="symbol-label bg-light-{{ $cat['color'] }}">
                                    <i class="ki-outline {{ $cat['icon'] }} fs-3 text-{{ $cat['color'] }}"></i>
                                </span>
                            </span>
                            <span class="fw-bold">{{ $cat['label'] }}</span>
                        </div>
                        <span class="badge badge-light-{{ $cat['color'] }} fs-7">{{ $code }}</span>
                    </div>
                    <div class="fs-2x fw-bolder text-{{ $cat['color'] }}">{{ $total }}</div>
                    <div class="text-muted fs-8">
                        Menunggu: <b>{{ $waiting }}</b> · Selesai: <b>{{ $done }}</b>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center gap-2 my-1">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-3 mt-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control form-control-solid w-250px ps-12"
                           placeholder="Cari No.Reg, Nama, RM, NIK...">
                </div>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-solid w-150px">
                <span class="text-muted">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-solid w-150px">
                <button type="submit" class="btn btn-sm btn-light-primary">Cari</button>
                @if(request()->hasAny(['q','category','status']) || $dateFrom !== today()->toDateString())
                    <a href="{{ route('admin.visits.index') }}" class="btn btn-sm btn-light">Reset</a>
                @endif
            </form>
        </div>

        <div class="card-toolbar">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                    <i class="ki-outline ki-filter fs-3"></i> Filter
                </button>
                @if(auth()->user()->hasPermission('visits.create'))
                    <a href="{{ route('admin.visits.create') }}" class="btn btn-sm btn-primary">
                        <i class="ki-outline ki-plus fs-3"></i> Kunjungan Baru
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="collapse {{ request()->hasAny(['category','status']) ? 'show' : '' }}" id="filterPanel">
        <div class="card-body border-top">
            <form method="GET" class="row g-3">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">

                <div class="col-md-4">
                    <label class="form-label fs-7">Kategori</label>
                    <select name="category" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua kategori" data-allow-clear="true" data-minimum-results-for-search="-1">
                        <option></option>
                        @foreach($categories as $code => $cat)
                            <option value="{{ $code }}" @selected(request('category')===$code)>{{ $code }} — {{ $cat['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-7">Status</label>
                    <select name="status" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua status" data-allow-clear="true" data-minimum-results-for-search="-1">
                        <option></option>
                        @foreach($statuses as $code => $s)
                            <option value="{{ $code }}" @selected(request('status')===$code)>{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                <thead>
                    <tr class="fw-bold text-muted bg-light-primary">
                        <th class="ps-4 w-50px">#</th>
                        <th>No. Register</th>
                        <th>Tgl/Jam</th>
                        <th>Pasien</th>
                        <th>Kategori</th>
                        <th>Pembiayaan</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $v)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($visits->currentPage()-1) * $visits->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.visits.show', $v) }}" class="text-dark fw-bold text-hover-primary font-monospace">{{ $v->no_register }}</a>
                                @if($v->queue_number)
                                    <div class="fs-8 text-muted">Antri #{{ $v->queue_number }}</div>
                                @endif
                            </td>
                            <td class="text-muted fs-7">
                                {{ $v->visit_date?->isoFormat('D MMM YY') }}<br>
                                <small>{{ $v->visit_time?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $v->patient?->name }}</div>
                                <div class="text-muted fs-8">{{ $v->patient?->no_rm }} · {{ $v->patient?->gender }} · {{ $v->patient?->age }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $v->category_color }}">{{ $v->category }} — {{ $v->category_label }}</span>
                            </td>
                            <td class="fs-7">{{ optional($v->payerType)->name ?? '-' }}</td>
                            <td class="text-muted fs-7" style="max-width:200px;">
                                {{ \Illuminate\Support\Str::limit($v->chief_complaint, 60) ?? '-' }}
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $v->status_color }}">{{ $v->status_label }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.visits.show', $v) }}" class="btn btn-sm btn-icon btn-light-info" title="Detail">
                                    <i class="ki-outline ki-eye fs-3"></i>
                                </a>
                                @if(auth()->user()->hasPermission('visits.update') && $v->status !== 'done' && $v->status !== 'cancelled')
                                <a href="{{ route('admin.visits.edit', $v) }}" class="btn btn-sm btn-icon btn-light-warning" title="Edit">
                                    <i class="ki-outline ki-pencil fs-3"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-10">Tidak ada kunjungan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-muted fs-7">
                Menampilkan {{ $visits->firstItem() ?? 0 }}–{{ $visits->lastItem() ?? 0 }} dari {{ $visits->total() }} kunjungan
            </div>
            {{ $visits->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<x-sweet-flash />
@endpush
