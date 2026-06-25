@extends('admin.layouts.app')
@section('title', 'Bayi & Anak')
@section('page_title', 'Pelayanan Bayi & Anak (Imunisasi + Tumbuh Kembang)')

@section('content')
<div class="card">
    <div class="card-header pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-solid w-250px" placeholder="Cari Nama/Kartu/Ibu...">
                <button class="btn btn-sm btn-light-primary">Cari</button>
            </form>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('admin.child.create') }}" class="btn btn-sm btn-primary">
                <i class="ki-outline ki-plus fs-3"></i> Tambah Pasien Anak
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle gs-0 gy-3">
                <thead><tr class="fw-bold text-muted bg-light-info">
                    <th class="ps-4 w-50px">#</th>
                    <th>No. Kartu</th>
                    <th>Bayi/Anak</th>
                    <th>Ibu</th>
                    <th>Umur</th>
                    <th>BB Lahir</th>
                    <th>Imunisasi</th>
                    <th>Kunjungan KMS</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($children as $n)
                        @php
                            $imm = $immCounts[$n->id] ?? 0;
                            $vis = $visitCounts[$n->id] ?? 0;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($children->currentPage()-1) * $children->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.child.show', $n) }}" class="text-dark fw-bold font-monospace">{{ $n->no_kartu_bayi }}</a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $n->nama_bayi }}</div>
                                <span class="badge badge-light-{{ $n->jenis_kelamin === 'L' ? 'primary' : 'danger' }} fs-8">{{ $n->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                            </td>
                            <td class="fs-8">
                                <div class="fw-semibold">{{ $n->patient?->name }}</div>
                                <div class="text-muted">{{ $n->patient?->no_rm }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light-{{ $n->is_child ? 'success' : 'warning' }}">{{ $n->umur_label ?? '-' }}</span>
                            </td>
                            <td>{{ $n->bb_lahir_gram ?? '-' }} gr</td>
                            <td>
                                <span class="badge badge-light-info">💉 {{ $imm }} dose</span>
                            </td>
                            <td>
                                <span class="badge badge-light-success">📋 {{ $vis }}x</span>
                            </td>
                            <td><span class="badge badge-light-{{ $n->status_color }}">{{ $n->status_label }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.child.show', $n) }}" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-eye fs-3"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-10">Belum ada data bayi/anak.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between mt-4">
            <div class="text-muted fs-7">{{ $children->total() }} bayi/anak</div>
            {{ $children->links() }}
        </div>
    </div>
</div>
@endsection
@push('scripts')<x-sweet-flash />@endpush
