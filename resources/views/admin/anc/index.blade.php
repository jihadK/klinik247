@extends('admin.layouts.app')

@section('title', 'Data Kehamilan (ANC)')
@section('page_title', 'Data Kehamilan (ANC)')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-3 mt-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="form-control form-control-solid w-250px ps-12"
                           placeholder="Cari No.Kartu/Nama/RM...">
                </div>
                <button type="submit" class="btn btn-sm btn-light-primary">Cari</button>
                @if(request()->hasAny(['q','status','trimester']))
                    <a href="{{ route('admin.anc.index') }}" class="btn btn-sm btn-light">Reset</a>
                @endif
            </form>
        </div>
        <div class="card-toolbar d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" data-bs-target="#ancFilter">
                <i class="ki-outline ki-filter fs-3"></i> Filter
            </button>
            @if(auth()->user()->hasPermission('anc.create'))
                <a href="{{ route('admin.anc.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-3"></i> Kehamilan Baru (K1)
                </a>
            @endif
        </div>
    </div>

    <div class="collapse {{ request()->hasAny(['status','trimester']) ? 'show' : '' }}" id="ancFilter">
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
                    <label class="form-label fs-7">Trimester</label>
                    <select name="trimester" class="form-select form-select-sm" data-control="select2" data-placeholder="Semua" data-allow-clear="true" data-minimum-results-for-search="-1">
                        <option></option>
                        <option value="1" @selected(request('trimester')==='1')>Trimester I (0-13 mg)</option>
                        <option value="2" @selected(request('trimester')==='2')>Trimester II (13-28 mg)</option>
                        <option value="3" @selected(request('trimester')==='3')>Trimester III (28+ mg)</option>
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
                    <tr class="fw-bold text-muted bg-light-success">
                        <th class="ps-4 w-50px">#</th>
                        <th>No. Kartu</th>
                        <th>Ibu Hamil</th>
                        <th>GPA</th>
                        <th>HPHT</th>
                        <th>HPL</th>
                        <th>UK (Sekarang)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pregnancies as $p)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($pregnancies->currentPage()-1) * $pregnancies->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.anc.show', $p) }}" class="text-dark fw-bold text-hover-primary font-monospace">{{ $p->no_kartu_hamil }}</a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $p->patient?->name }}</div>
                                <div class="text-muted fs-8">{{ $p->patient?->no_rm }} · {{ $p->patient?->age }}</div>
                            </td>
                            <td><span class="badge badge-light-info font-monospace">{{ $p->gpa_label }}</span></td>
                            <td class="text-muted fs-7">{{ optional($p->hpht)->isoFormat('D MMM YY') ?? '-' }}</td>
                            <td class="text-muted fs-7">{{ optional($p->hpl)->isoFormat('D MMM YY') ?? '-' }}</td>
                            <td>
                                @php $uk = $p->uk_sekarang; @endphp
                                @if($uk !== null)
                                    <span class="fw-bold">{{ $uk }}</span> mg
                                    @if($p->trimester)
                                        <div class="text-muted fs-8">Trim {{ $p->trimester }}</div>
                                    @endif
                                @else - @endif
                            </td>
                            <td><span class="badge badge-light-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.anc.show', $p) }}" class="btn btn-sm btn-icon btn-light-info" title="Detail">
                                    <i class="ki-outline ki-eye fs-3"></i>
                                </a>
                                <a href="{{ route('admin.anc.kartu', $p) }}" target="_blank" class="btn btn-sm btn-icon btn-light-success" title="Cetak">
                                    <i class="ki-outline ki-printer fs-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-10">Belum ada data kehamilan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-muted fs-7">{{ $pregnancies->total() }} kehamilan</div>
            {{ $pregnancies->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')<x-sweet-flash />@endpush
