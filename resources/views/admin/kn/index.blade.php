@extends('admin.layouts.app')
@section('title', 'Neonatus (KN)')
@section('page_title', 'Pelayanan Neonatus (KN)')

@section('content')
<div class="card">
    <div class="card-header pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-solid w-250px" placeholder="Cari No.Kartu/Nama bayi/Ibu...">
                <button class="btn btn-sm btn-light-primary">Cari</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle gs-0 gy-3">
                <thead><tr class="fw-bold text-muted bg-light-info">
                    <th class="ps-4 w-50px">#</th>
                    <th>No. Kartu Bayi</th>
                    <th>Bayi</th>
                    <th>Ibu</th>
                    <th>Lahir</th>
                    <th>Umur</th>
                    <th>Progress KN</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($neonates as $n)
                        @php
                            $jmlKn = $knCounts[$n->id] ?? 0;
                            $umur = $n->umur_hari;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($neonates->currentPage()-1) * $neonates->perPage() }}</td>
                            <td>
                                <a href="{{ route('admin.kn.show', $n) }}" class="text-dark fw-bold text-hover-primary font-monospace">{{ $n->no_kartu_bayi }}</a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $n->nama_bayi }}</div>
                                <div class="text-muted fs-8">
                                    <span class="badge badge-light-{{ $n->jenis_kelamin === 'L' ? 'primary' : 'danger' }}">{{ $n->jenis_kelamin }}</span>
                                    · {{ $n->bb_lahir_gram ?? '-' }} gr
                                </div>
                            </td>
                            <td class="fs-8">
                                <div class="fw-semibold">{{ $n->patient?->name }}</div>
                                <div class="text-muted">{{ $n->patient?->no_rm }}</div>
                            </td>
                            <td class="fs-7">{{ optional($n->tanggal_lahir)->isoFormat('D MMM YY') }}</td>
                            <td>
                                @if($umur !== null)
                                    <span class="badge badge-light-{{ $umur > 28 ? 'success' : 'warning' }}">{{ $umur }} hari</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @foreach($knPeriods as $kn => $info)
                                        @php $done = $jmlKn >= $kn; @endphp
                                        <span class="badge badge-{{ $done ? $info['color'] : 'light' }}" title="{{ $info['label'] }} — {{ $info['periode'] }}">
                                            KN{{ $kn }} @if($done) ✓ @endif
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td><span class="badge badge-light-{{ $n->status_color }}">{{ $n->status_label }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.kn.show', $n) }}" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-eye fs-3"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-10">Belum ada data bayi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between mt-4">
            <div class="text-muted fs-7">{{ $neonates->total() }} bayi</div>
            {{ $neonates->links() }}
        </div>
    </div>
</div>
@endsection
@push('scripts')<x-sweet-flash />@endpush
