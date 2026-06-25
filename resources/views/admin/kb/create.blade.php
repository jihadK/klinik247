@extends('admin.layouts.app')
@section('title', 'Akseptor KB Baru')
@section('page_title', 'Pendaftaran Akseptor KB')

@section('content')
<form action="{{ route('admin.kb.store') }}" method="POST">
    @csrf
    @include('admin.kb._form', ['isEdit' => false])
</form>
@endsection

@push('scripts')
<x-sweet-flash />

@unless($patient)
<script>
$(function() {
    const $picker = $('#kb_patient_picker');
    if (! $picker.length) return;

    $picker.select2({
        placeholder: 'Cari pasien: nama / No.RM / NIK / HP...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: "{{ route('admin.visits.ajax.search-patient') }}",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: function(res) {
                const rows = (res.data || []).filter(p => p.gender === 'P');
                return {
                    results: rows.map(p => ({
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
            return $(
                `<div class="py-1">
                    <div class="fw-bold">${p.name}</div>
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
        const url = new URL(window.location.href);
        url.searchParams.set('patient_id', patientId);
        window.location.href = url.toString();
    });
});
</script>
@endunless
@endpush
