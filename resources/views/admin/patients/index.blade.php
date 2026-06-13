@extends('admin.layouts.app')

@section('title', 'Master Pasien')
@section('page_title', 'Master Pasien')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                       class="form-control form-control-solid w-300px ps-13"
                       placeholder="Cari nama, No.RM, NIK, BPJS, KK..." />
                <button type="submit" class="btn btn-sm btn-light-primary ms-2">Cari</button>
                @if(request()->hasAny(['q','gender','payer','wilayah','inactive']))
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-light ms-2">Reset</a>
                @endif
            </form>
        </div>

        <div class="card-toolbar">
            <div class="d-flex gap-2">
                {{-- Filter dropdown --}}
                <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                    <i class="ki-outline ki-filter fs-3"></i> Filter
                </button>
                @if(auth()->user()->hasPermission('patients.create'))
                    <a href="{{ route('admin.patients.create') }}" class="btn btn-sm btn-primary">
                        <i class="ki-outline ki-plus fs-3"></i> Pasien Baru
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter panel --}}
    <div class="collapse {{ request()->hasAny(['gender','payer','wilayah','inactive']) ? 'show' : '' }}" id="filterPanel">
        <div class="card-body border-top">
            <form method="GET" class="row g-3">
                <input type="hidden" name="q" value="{{ request('q') }}">

                <div class="col-md-3">
                    <label class="form-label fs-7">Jenis Kelamin</label>
                    <select name="gender" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua" data-allow-clear="true" data-minimum-results-for-search="-1">
                        <option></option>
                        <option value="L" @selected(request('gender')==='L')>Laki-laki</option>
                        <option value="P" @selected(request('gender')==='P')>Perempuan</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-7">Pembiayaan</label>
                    <select name="payer" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua" data-allow-clear="true">
                        <option></option>
                        @foreach($payerTypes as $p)
                            <option value="{{ $p->id }}" @selected((string)request('payer')===(string)$p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-7">Wilayah</label>
                    <select name="wilayah" class="form-select form-select-sm"
                            data-control="select2" data-placeholder="Semua" data-allow-clear="true" data-minimum-results-for-search="-1">
                        <option></option>
                        <option value="dalam_wilayah" @selected(request('wilayah')==='dalam_wilayah')>Dalam Wilayah</option>
                        <option value="luar_wilayah" @selected(request('wilayah')==='luar_wilayah')>Luar Wilayah</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-7">Status</label>
                    <select name="inactive" class="form-select form-select-sm"
                            data-control="select2" data-minimum-results-for-search="-1">
                        <option value="0" @selected(! request()->boolean('inactive'))>Aktif</option>
                        <option value="1" @selected(request()->boolean('inactive'))>Non-Aktif</option>
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
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>NIK / BPJS</th>
                        <th>L/P</th>
                        <th>Umur</th>
                        <th>Wilayah</th>
                        <th>Pembiayaan</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $p)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($patients->currentPage()-1) * $patients->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.patients.show', $p) }}" class="text-dark fw-bold text-hover-primary">
                                    {{ $p->no_rm }}
                                </a>
                                @if($p->cm_lama)
                                    <div class="text-muted fs-8">CM Lama: {{ $p->cm_lama }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                @if($p->nama_kk)
                                    <div class="text-muted fs-8">KK: {{ $p->nama_kk }}</div>
                                @endif
                            </td>
                            <td class="text-muted fs-7">
                                @if($p->nik)    NIK: {{ $p->nik }}<br>@endif
                                @if($p->no_bpjs) BPJS: {{ $p->no_bpjs }}@endif
                            </td>
                            <td>
                                <span class="badge {{ $p->gender==='L' ? 'badge-light-primary' : 'badge-light-danger' }}">
                                    {{ $p->gender }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $p->age }}</td>
                            <td>
                                @if($p->wilayah_type)
                                    <span class="badge {{ $p->wilayah_type==='dalam_wilayah' ? 'badge-light-success' : 'badge-light-warning' }}">
                                        {{ $p->wilayah_type==='dalam_wilayah' ? 'Dalam' : 'Luar' }}
                                    </span>
                                @endif
                                @if($p->village)
                                    <div class="text-muted fs-8">{{ $p->village->name }}</div>
                                @endif
                            </td>
                            <td>
                                {{ optional($p->payerType)->name ?? '-' }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.patients.show', $p) }}" class="btn btn-sm btn-icon btn-light-info" title="Detail">
                                    <i class="ki-outline ki-eye fs-3"></i>
                                </a>
                                @if(auth()->user()->hasPermission('patients.update'))
                                <a href="{{ route('admin.patients.edit', $p) }}" class="btn btn-sm btn-icon btn-light-warning" title="Edit">
                                    <i class="ki-outline ki-pencil fs-3"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('patients.delete'))
                                <form action="{{ route('admin.patients.destroy', $p) }}" method="POST" class="d-inline form-delete">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="Hapus">
                                        <i class="ki-outline ki-trash fs-3"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-10">Tidak ada pasien.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-muted fs-7">
                Menampilkan {{ $patients->firstItem() ?? 0 }}–{{ $patients->lastItem() ?? 0 }} dari {{ $patients->total() }} pasien
            </div>
            {{ $patients->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<x-sweet-helpers />
<script>
document.querySelectorAll('.form-delete').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus pasien?',
            text: 'Data pasien akan di-soft delete (bisa di-restore).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' },
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
