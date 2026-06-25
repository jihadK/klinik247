@extends('admin.layouts.app')
@section('title', 'Tambah Pasien Anak')
@section('page_title', 'Tambah Pasien Anak — Pendaftaran Walk-in')

@section('content')
<div class="card">
    <div class="card-header"><h3 class="card-title">Pilih Pasien Anak</h3></div>
    <div class="card-body">
        <div class="alert alert-info py-2 fs-7 mb-4">
            <i class="ki-outline ki-information-5 fs-3 me-1"></i>
            Pilih pasien yang akan didaftarkan sebagai <b>anak/bayi</b> untuk modul imunisasi &amp; tumbuh kembang.
            Sistem akan otomatis buat <b>kartu bayi</b> dari data pasien (nama, tanggal lahir, jenis kelamin).
            Pasien yang sudah lahir di klinik (punya delivery record) akan dideteksi otomatis.
        </div>

        <label class="form-label fs-7 fw-bold">
            <i class="ki-outline ki-magnifier fs-3 text-primary me-1"></i>
            Cari Pasien
        </label>
        <select id="child_patient_picker" class="form-select form-select-solid"
                data-control="select2" data-placeholder="Cari pasien anak: nama / No.RM / NIK / HP ibu..." data-allow-clear="true">
            <option></option>
        </select>
        <div class="form-text fs-8 mt-2">
            💡 Ketik min. 2 huruf untuk cari pasien.
            Pasien baru? <a href="{{ route('admin.patients.create') }}" target="_blank">Daftarkan dulu di sini</a>.
        </div>

        <div class="d-flex justify-content-end mt-5">
            <a href="{{ route('admin.child.index') }}" class="btn btn-light">Batal</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<x-sweet-flash />
<script>
$(function() {
    const $picker = $('#child_patient_picker');
    if (! $picker.length) return;

    $picker.select2({
        placeholder: 'Cari pasien anak: nama / No.RM / NIK / HP ibu...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('admin.visits.ajax.search-patient') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: function(res) {
                return {
                    results: (res.data || []).map(p => ({
                        id: p.id,
                        text: `${p.name} — ${p.no_rm}`,
                        patient: p,
                    }))
                };
            }
        },
        templateResult: function(item) {
            if (! item.patient) return item.text;
            const p = item.patient;
            const phone = p.phone ? ` · 📞 ${p.phone}` : '';
            const payer = p.payer ? ` · ${p.payer}` : '';
            const safeAddr = (p.address || '').replace(/</g, '&lt;');
            const addrLine = safeAddr
                ? `<div class="text-muted fs-8 mt-1"><i class="ki-outline ki-geolocation fs-7"></i> ${safeAddr}</div>`
                : '';
            const genderBadge = p.gender === 'L'
                ? '<span class="badge badge-light-info">L</span>'
                : '<span class="badge badge-light-danger">P</span>';
            return $(
                `<div class="py-1">
                    <div class="fw-bold">${p.name} ${genderBadge}</div>
                    <div class="text-muted fs-8">
                        <span class="badge badge-light-primary">${p.no_rm}</span>
                        ${p.nik ? ' · NIK ' + p.nik : ''}
                        · ${p.age}${phone}${payer}
                    </div>
                    ${addrLine}
                </div>`
            );
        },
        templateSelection: function(item) {
            if (! item.patient) return item.text;
            return `${item.patient.name} (${item.patient.no_rm})`;
        }
    });

    $picker.on('select2:select', function(e) {
        const patientId = e.params.data.id;
        if (! patientId) return;
        const url = new URL("{{ route('admin.child.create') }}", window.location.origin);
        url.searchParams.set('patient_id', patientId);
        window.location.href = url.toString();
    });
});
</script>
@endpush
