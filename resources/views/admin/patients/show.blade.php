@extends('admin.layouts.app')

@section('title', 'Detail Pasien — '.$patient->no_rm)
@section('page_title', 'Detail Pasien')

@section('content')
<div class="row">
    <div class="col-md-4">
        {{-- Profil Card --}}
        <div class="card mb-5">
            <div class="card-body text-center">
                <div class="symbol symbol-100px mb-3 mx-auto">
                    <img src="{{ $patient->photo_url ? asset('storage/'.$patient->photo_url) : asset('assets/media/svg/avatars/blank.svg') }}"
                         alt="{{ $patient->name }}" class="rounded">
                </div>
                <h2 class="mb-1">{{ $patient->name }}</h2>
                <div class="text-muted fs-6 mb-3">
                    <span class="badge {{ $patient->gender==='L' ? 'badge-light-primary' : 'badge-light-danger' }} fs-7">{{ $patient->gender_label }}</span>
                    · {{ $patient->age }}
                </div>
                <div class="fs-2x fw-bolder text-primary mb-1">{{ $patient->no_rm }}</div>
                @if($patient->cm_lama)
                    <div class="text-muted fs-7">CM Lama: <b>{{ $patient->cm_lama }}</b></div>
                @endif
                <div class="d-flex justify-content-center gap-2 mt-4">
                    @if(auth()->user()->hasPermission('patients.update'))
                        <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-sm btn-light-warning">
                            <i class="ki-outline ki-pencil fs-3"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('admin.patients.kartu', $patient) }}" target="_blank" class="btn btn-sm btn-light-info">
                        <i class="ki-outline ki-printer fs-3"></i> Cetak Kartu
                    </a>
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-light">
                        <i class="ki-outline ki-arrow-left fs-3"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        {{-- Status & Pembiayaan --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Pembiayaan & Status</h3></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Pembiayaan</span> <b>{{ optional($patient->payerType)->name ?? '-' }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">No. BPJS</span> <b>{{ $patient->no_bpjs ?? '-' }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Wilayah</span>
                    @if($patient->wilayah_type)
                        <span class="badge {{ $patient->wilayah_type==='dalam_wilayah' ? 'badge-light-success' : 'badge-light-warning' }}">
                            {{ $patient->wilayah_type==='dalam_wilayah' ? 'Dalam Wilayah' : 'Luar Wilayah' }}
                        </span>
                    @else - @endif
                </div>
                <div class="d-flex justify-content-between"><span class="text-muted">Status</span>
                    <span class="badge {{ $patient->is_active ? 'badge-light-success' : 'badge-light-danger' }}">
                        {{ $patient->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- Identitas Detail --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Identitas Lengkap</h3></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6"><div class="text-muted fs-7">NIK</div><div class="fw-semibold">{{ $patient->nik ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">No. KK</div><div class="fw-semibold">{{ $patient->no_kk ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Nama KK</div><div class="fw-semibold">{{ $patient->nama_kk ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Tempat, Tanggal Lahir</div><div class="fw-semibold">{{ $patient->birth_place ?? '-' }}, {{ optional($patient->birth_date)->isoFormat('D MMMM YYYY') }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Status Kawin</div><div class="fw-semibold">{{ ucwords(str_replace('_',' ', $patient->marital_status ?? '-')) }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Agama</div><div class="fw-semibold">{{ optional($patient->religion)->name ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Pendidikan</div><div class="fw-semibold">{{ optional($patient->education)->name ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Pekerjaan</div><div class="fw-semibold">{{ $patient->occupation ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Gol. Darah</div><div class="fw-semibold">{{ $patient->blood_type ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">No. HP</div><div class="fw-semibold">{{ $patient->phone ?? '-' }}</div></div>
                    <div class="col-md-12"><div class="text-muted fs-7">Email</div><div class="fw-semibold">{{ $patient->email ?? '-' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Alamat --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Alamat</h3></div>
            <div class="card-body">
                <div class="fw-semibold">{{ $patient->full_address ?: '-' }}</div>
                @if($patient->rt_rw)
                    <div class="text-muted fs-7 mt-1">RT/RW: {{ $patient->rt_rw }}</div>
                @endif
            </div>
        </div>

        {{-- Riwayat Medis --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Riwayat Medis</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted fs-7">Alergi</div>
                    <div class="fw-semibold">{{ $patient->allergies ?: 'Tidak ada catatan' }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted fs-7">Penyakit Kronis</div>
                    <div class="fw-semibold">{{ $patient->chronic_diseases ?: 'Tidak ada catatan' }}</div>
                </div>
                <div>
                    <div class="text-muted fs-7">Riwayat Penyakit</div>
                    <div class="fw-semibold">{{ $patient->medical_history ?: 'Tidak ada catatan' }}</div>
                </div>
            </div>
        </div>

        {{-- Kontak Darurat --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Kontak Darurat</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted fs-7">Nama</div><div class="fw-semibold">{{ $patient->emergency_contact ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">No. HP</div><div class="fw-semibold">{{ $patient->emergency_phone ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted fs-7">Hubungan</div><div class="fw-semibold">{{ $patient->emergency_relation ?? '-' }}</div></div>
                </div>
            </div>
        </div>

        @if($patient->notes)
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">Catatan Internal</h3></div>
                <div class="card-body">{{ $patient->notes }}</div>
            </div>
        @endif

        <div class="text-muted fs-8 mt-3">
            Klinik: <b>{{ optional($patient->site)->name }}</b> ·
            Didaftarkan oleh <b>{{ optional($patient->createdBy)->full_name ?? '-' }}</b> pada
            <b>{{ $patient->created_date?->isoFormat('D MMM YYYY HH:mm') }}</b>
            @if($patient->updated_date)
                · Diupdate <b>{{ $patient->updated_date->isoFormat('D MMM YYYY HH:mm') }}</b>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<x-sweet-flash />
@endpush
