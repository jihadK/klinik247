@extends('admin.layouts.app')
@section('title', 'Nifas (PNC)')
@section('page_title', 'Pelayanan Nifas (PNC)')

@section('content')
<div class="card">
    <div class="card-header pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-solid w-250px" placeholder="Cari nama/No.RM...">
                <button class="btn btn-sm btn-light-primary">Cari</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle gs-0 gy-3">
                <thead><tr class="fw-bold text-muted bg-light-warning">
                    <th class="ps-4 w-50px">#</th>
                    <th>Ibu Nifas</th>
                    <th>Persalinan</th>
                    <th>Bayi Lahir</th>
                    <th>Hari Nifas</th>
                    <th>Progress KF</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($deliveries as $d)
                        @php
                            $jmlKf = $kfCounts[$d->id] ?? 0;
                            $hariNifas = $d->bayi_lahir_at ? (int) $d->bayi_lahir_at->diffInDays(now()) : null;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($deliveries->currentPage()-1) * $deliveries->perPage() }}</td>
                            <td>
                                <div class="fw-semibold">{{ $d->patient?->name }}</div>
                                <div class="text-muted fs-8">{{ $d->patient?->no_rm }} · {{ $d->patient?->age }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin.inc.show', $d) }}" class="text-dark font-monospace fs-8">{{ $d->no_persalinan }}</a>
                                <div class="text-muted fs-8">{{ optional($d->bayi_lahir_at)->isoFormat('D MMM YY') }}</div>
                            </td>
                            <td>
                                @if($d->bayi_jenis_kelamin)
                                    <span class="badge badge-light-{{ $d->bayi_jenis_kelamin === 'L' ? 'primary' : 'danger' }}">{{ $d->bayi_jenis_kelamin }}</span>
                                @endif
                                {{ $d->bayi_bb_gram ?? '-' }} gr
                            </td>
                            <td>
                                @if($hariNifas !== null)
                                    <span class="badge badge-light-{{ $hariNifas > 42 ? 'success' : 'warning' }}">{{ $hariNifas }} hari</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @foreach($kfPeriods as $kf => $info)
                                        @php $done = $jmlKf >= $kf; @endphp
                                        <span class="badge badge-{{ $done ? $info['color'] : 'light' }}" title="{{ $info['label'] }} — {{ $info['periode'] }}">
                                            KF{{ $kf }} @if($done) ✓ @endif
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.pnc.show', $d) }}" class="btn btn-sm btn-light-primary">
                                    <i class="ki-outline ki-eye fs-3"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-10">Belum ada data nifas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between mt-4">
            <div class="text-muted fs-7">{{ $deliveries->total() }} ibu nifas</div>
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
@endsection
@push('scripts')<x-sweet-flash />@endpush
