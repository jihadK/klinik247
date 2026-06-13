@extends('admin.layouts.app')
@section('title', 'Persalinan (INC)')
@section('page_title', 'Persalinan (INC)')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <form method="GET" class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-3 mt-3"></i>
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-solid w-250px ps-12" placeholder="Cari No.Persalinan/Nama/RM">
                </div>
                <button type="submit" class="btn btn-sm btn-light-primary">Cari</button>
                @if(request()->hasAny(['q','status']))
                    <a href="{{ route('admin.inc.index') }}" class="btn btn-sm btn-light">Reset</a>
                @endif
            </form>
        </div>
        <div class="card-toolbar d-flex gap-2">
            <select onchange="window.location='?status='+this.value" class="form-select form-select-sm w-200px">
                <option value="">Semua Status</option>
                @foreach($statuses as $code => $s)<option value="{{ $code }}" @selected(request('status')===$code)>{{ $s['label'] }}</option>@endforeach
            </select>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table table-row-bordered align-middle gs-0 gy-3">
                <thead><tr class="fw-bold text-muted bg-light-warning">
                    <th class="ps-4 w-50px">#</th>
                    <th>No. Persalinan</th>
                    <th>Pasien</th>
                    <th>Kehamilan</th>
                    <th>Tanggal/Jam Masuk</th>
                    <th>Penapisan</th>
                    <th>Keputusan</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr></thead>
                <tbody>
                    @php $keputusanOpts = \App\Models\Delivery::keputusanPenapisanOptions(); @endphp
                    @forelse($deliveries as $d)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($deliveries->currentPage()-1) * $deliveries->perPage() }}</td>
                            <td><a href="{{ route('admin.inc.show', $d) }}" class="text-dark fw-bold text-hover-primary font-monospace">{{ $d->no_persalinan }}</a></td>
                            <td>
                                <div class="fw-semibold">{{ $d->patient?->name }}</div>
                                <div class="text-muted fs-8">{{ $d->patient?->no_rm }}</div>
                            </td>
                            <td><span class="badge badge-light-success font-monospace fs-8">{{ $d->pregnancy?->no_kartu_hamil }}</span></td>
                            <td class="text-muted fs-7">{{ optional($d->masuk_at)->isoFormat('D MMM YY HH:mm') }}</td>
                            <td>
                                @if($d->penapisan_skor > 0)
                                    <span class="badge badge-light-danger">{{ $d->penapisan_skor }} risk</span>
                                @else
                                    <span class="badge badge-light-success">0 risk</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $kep = $d->penapisan_keputusan;
                                    $kepColor = $kep === 'rujuk' ? 'danger' : ($kep === 'observasi' ? 'warning' : ($kep === 'lanjut' ? 'success' : 'secondary'));
                                    $kepIcon  = $kep === 'rujuk' ? '🚨' : ($kep === 'observasi' ? '⏳' : ($kep === 'lanjut' ? '✅' : '⚪'));
                                @endphp
                                @if($kep)
                                    <span class="badge badge-light-{{ $kepColor }}">{{ $kepIcon }} {{ $keputusanOpts[$kep] ?? $kep }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><span class="badge badge-light-{{ $d->status_color }}">{{ $d->status_label }}</span></td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.inc.show', $d) }}" class="btn btn-sm btn-icon btn-light-info"><i class="ki-outline ki-eye fs-3"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-10">Belum ada data persalinan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-5">
            <div class="text-muted fs-7">{{ $deliveries->total() }} persalinan</div>
            {{ $deliveries->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')<x-sweet-flash />@endpush
