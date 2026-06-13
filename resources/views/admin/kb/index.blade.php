@extends('admin.layouts.app')

@section('title', 'Akseptor KB')
@section('page_title', 'Akseptor KB')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-3 mt-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control form-control-solid w-250px ps-12"
                           placeholder="Cari No.Kartu/RM/Nama/Suami...">
                </div>
                <button type="submit" class="btn btn-sm btn-light-primary">Cari</button>
                @if(request()->hasAny(['q','status','kontrasepsi']))
                    <a href="{{ route('admin.kb.index') }}" class="btn btn-sm btn-light">Reset</a>
                @endif
            </form>
        </div>
        <div class="card-toolbar d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" data-bs-target="#kbFilter">
                <i class="ki-outline ki-filter fs-3"></i> Filter
            </button>
            @if(auth()->user()->hasPermission('kb.create'))
                <a href="{{ route('admin.kb.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-3"></i> Akseptor Baru
                </a>
            @endif
        </div>
    </div>

    <div class="collapse {{ request()->hasAny(['status','kontrasepsi']) ? 'show' : '' }}" id="kbFilter">
        <div class="card-body border-top">
            <form method="GET" class="row g-3">
                <input type="hidden" name="q" value="{{ request('q') }}">
                <div class="col-md-4">
                    <label class="form-label fs-7">Status</label>
                    <select name="status" class="form-select form-select-sm" data-control="select2" data-placeholder="Semua" data-allow-clear="true" data-minimum-results-for-search="-1">
                        <option></option>
                        @foreach($statuses as $code => $s)
                            <option value="{{ $code }}" @selected(request('status')===$code)>{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-7">Alat Kontrasepsi</label>
                    <select name="kontrasepsi" class="form-select form-select-sm" data-control="select2" data-placeholder="Semua" data-allow-clear="true">
                        <option></option>
                        @foreach($kontrasepsi as $k)
                            <option value="{{ $k->id }}" @selected((string)request('kontrasepsi')===(string)$k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12"><button class="btn btn-sm btn-primary">Terapkan</button></div>
            </form>
        </div>
    </div>

    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-3">
                <thead>
                    <tr class="fw-bold text-muted bg-light-primary">
                        <th class="ps-4 w-50px">#</th>
                        <th>No. Kartu KB</th>
                        <th>Pasien (Akseptor)</th>
                        <th>Alat</th>
                        <th>Tgl Layanan</th>
                        <th>Kontrol Berikutnya</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($acceptors as $a)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($acceptors->currentPage()-1) * $acceptors->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.kb.show', $a) }}" class="text-dark fw-bold text-hover-primary font-monospace">{{ $a->no_kartu_kb }}</a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $a->patient?->name }}</div>
                                <div class="text-muted fs-8">{{ $a->patient?->no_rm }} · {{ $a->patient?->age }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-info">{{ $a->kontrasepsi?->name }}</span>
                            </td>
                            <td class="text-muted fs-7">{{ optional($a->tanggal_dilayani)->isoFormat('D MMM YY') }}</td>
                            <td class="text-muted fs-7">
                                @if($a->tanggal_pesan_kontrol)
                                    {{ $a->tanggal_pesan_kontrol->isoFormat('D MMM YY') }}
                                    @php $days = now()->diffInDays($a->tanggal_pesan_kontrol, false); @endphp
                                    @if($days < 0)
                                        <span class="badge badge-light-danger fs-9">Lewat {{ abs(round($days)) }} hari</span>
                                    @elseif($days < 7)
                                        <span class="badge badge-light-warning fs-9">{{ round($days) }} hari lagi</span>
                                    @endif
                                @else - @endif
                            </td>
                            <td><span class="badge badge-light-{{ $a->status_color }}">{{ $a->status_label }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.kb.show', $a) }}" class="btn btn-sm btn-icon btn-light-info" title="Detail">
                                    <i class="ki-outline ki-eye fs-3"></i>
                                </a>
                                <a href="{{ route('admin.kb.kartu', $a) }}" target="_blank" class="btn btn-sm btn-icon btn-light-primary" title="Cetak Kartu">
                                    <i class="ki-outline ki-printer fs-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-10">Belum ada akseptor KB.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-muted fs-7">{{ $acceptors->total() }} akseptor</div>
            {{ $acceptors->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')<x-sweet-flash />@endpush
