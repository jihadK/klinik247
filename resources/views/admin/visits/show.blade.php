@extends('admin.layouts.app')

@section('title', 'Detail Kunjungan — '.$visit->no_register)
@section('page_title', 'Detail Kunjungan')

@section('content')
<div class="row">
    <div class="col-md-4">
        {{-- Card Pasien --}}
        <div class="card mb-5">
            <div class="card-body text-center">
                <div class="symbol symbol-80px mb-3 mx-auto">
                    <img src="{{ $visit->patient?->photo_url ? asset('storage/'.$visit->patient->photo_url) : asset('assets/media/svg/avatars/blank.svg') }}"
                         class="rounded" alt="">
                </div>
                <h3 class="mb-1">{{ $visit->patient?->name }}</h3>
                <div class="text-muted fs-7">{{ $visit->patient?->no_rm }} · {{ $visit->patient?->gender_label }} · {{ $visit->patient?->age }}</div>
                <div class="mt-3">
                    <a href="{{ route('admin.patients.show', $visit->patient) }}" class="btn btn-sm btn-light">
                        <i class="ki-outline ki-eye fs-3"></i> Lihat Profil Pasien
                    </a>
                </div>
            </div>
        </div>

        {{-- Status + Quick Action --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Status & Aksi</h3></div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="text-muted fs-7 mb-1">Status saat ini</div>
                    <span class="badge badge-light-{{ $visit->status_color }} fs-4 px-4 py-3" id="current_status">{{ $visit->status_label }}</span>
                </div>

                @if(auth()->user()->hasPermission('visits.serve') && $visit->status === 'waiting')
                    <button type="button" class="btn btn-info w-100 mb-2 btn-set-status" data-status="in_service">
                        <i class="ki-outline ki-play fs-3"></i> Mulai Layani
                    </button>
                @endif

                @if(auth()->user()->hasPermission('visits.update') && $visit->status === 'in_service')
                    <button type="button" class="btn btn-success w-100 mb-2 btn-set-status" data-status="done">
                        <i class="ki-outline ki-check fs-3"></i> Selesai Layani
                    </button>

                    {{-- Tombol catat layanan sesuai kategori --}}
                    @if($visit->category === 'K' && auth()->user()->hasPermission('kb.create'))
                        <a href="{{ route('admin.kb.create', ['visit_id' => $visit->id]) }}" class="btn btn-primary w-100 mb-2">
                            <i class="ki-outline ki-pulse fs-3"></i> Catat Layanan KB
                        </a>
                    @endif
                    @if($visit->category === 'I' && auth()->user()->hasPermission('anc.create'))
                        <a href="{{ route('admin.anc.create', ['visit_id' => $visit->id]) }}" class="btn btn-success w-100 mb-2">
                            <i class="ki-outline ki-heart-circle fs-3"></i> Catat ANC (Ibu Hamil)
                        </a>
                    @endif
                @endif

                @if(auth()->user()->hasPermission('visits.update') && in_array($visit->status, ['waiting','in_service']))
                    <a href="{{ route('admin.visits.edit', $visit) }}" class="btn btn-light-warning w-100 mb-2">
                        <i class="ki-outline ki-pencil fs-3"></i> Edit Data
                    </a>
                @endif

                @if(auth()->user()->hasPermission('visits.delete') && $visit->status === 'waiting')
                    <form action="{{ route('admin.visits.destroy', $visit) }}" method="POST" class="form-cancel">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-light-danger w-100">
                            <i class="ki-outline ki-cross fs-3"></i> Batalkan Kunjungan
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.visits.index') }}" class="btn btn-light w-100 mt-2">← Kembali ke Daftar</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Identitas Kunjungan --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title">Identitas Kunjungan</h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-{{ $visit->category_color }} fs-6">{{ $visit->category }} — {{ $visit->category_label }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted fs-7">No. Register</div>
                        <div class="fw-bold fs-3 font-monospace text-primary">{{ $visit->no_register }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7">No. Antrian</div>
                        <div class="fw-bold fs-3">#{{ $visit->queue_number }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7">Tanggal & Jam</div>
                        <div class="fw-semibold">{{ $visit->visit_time?->isoFormat('dddd, D MMMM YYYY HH:mm') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7">Jenis Kunjungan</div>
                        <div class="fw-semibold">{{ \App\Models\PatientVisit::visitTypes()[$visit->visit_type] ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7">Pembiayaan</div>
                        <div class="fw-semibold">{{ optional($visit->payerType)->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted fs-7">Klinik</div>
                        <div class="fw-semibold">{{ optional($visit->site)->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Keluhan & Catatan</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted fs-7">Keluhan Utama</div>
                    <div class="fw-semibold">{{ $visit->chief_complaint ?: '-' }}</div>
                </div>
                @if($visit->notes)
                    <div>
                        <div class="text-muted fs-7">Catatan</div>
                        <div>{{ $visit->notes }}</div>
                    </div>
                @endif
                @if($visit->cancel_reason)
                    <div class="alert alert-danger mt-3">
                        <b>Alasan Pembatalan:</b> {{ $visit->cancel_reason }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Timeline Service --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Timeline</h3></div>
            <div class="card-body">
                <div class="timeline">
                    <div class="d-flex gap-3 mb-3">
                        <i class="ki-outline ki-document fs-2 text-warning"></i>
                        <div>
                            <div class="fw-bold">Terdaftar</div>
                            <div class="text-muted fs-7">{{ $visit->created_date?->isoFormat('D MMM YYYY HH:mm') }} oleh {{ optional($visit->createdBy)->full_name ?? '-' }}</div>
                        </div>
                    </div>
                    @if($visit->served_at)
                        <div class="d-flex gap-3 mb-3">
                            <i class="ki-outline ki-play fs-2 text-info"></i>
                            <div>
                                <div class="fw-bold">Mulai Dilayani</div>
                                <div class="text-muted fs-7">{{ $visit->served_at->isoFormat('D MMM YYYY HH:mm') }} oleh {{ optional($visit->servedBy)->full_name ?? '-' }}</div>
                            </div>
                        </div>
                    @endif
                    @if($visit->completed_at)
                        <div class="d-flex gap-3">
                            <i class="ki-outline ki-check-circle fs-2 text-success"></i>
                            <div>
                                <div class="fw-bold">Selesai</div>
                                <div class="text-muted fs-7">{{ $visit->completed_at->isoFormat('D MMM YYYY HH:mm') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<x-sweet-flash />
<x-sweet-helpers />
<script>
$(function() {
    // Quick status change
    $('.btn-set-status').on('click', function() {
        const status = $(this).data('status');
        $.ajax({
            url: '{{ route("admin.visits.set-status", $visit) }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            data: { status }
        })
        .done(res => {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.resMsg, timer: 1200, showConfirmButton: false })
                .then(() => location.reload());
        })
        .fail(xhr => {
            Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.resMsg || 'Error' });
        });
    });

    // Cancel confirm
    $('.form-cancel').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Batalkan kunjungan ini?',
            text: 'Kunjungan akan ditandai sebagai dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, batalkan',
            cancelButtonText: 'Tidak',
            customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-secondary' }
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
