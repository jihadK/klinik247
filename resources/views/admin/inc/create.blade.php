@extends('admin.layouts.app')
@section('title', 'Mulai Persalinan')
@section('page_title', 'Mulai Persalinan / INC — Penapisan & Masuk PMB')

@section('content')

{{-- ===== Patient Picker (kalau pregnancy belum dipilih) ===== --}}
@unless($pregnancy)
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">Pilih Pasien (Ibu Bersalin)</h3></div>
        <div class="card-body">
            <div class="alert alert-info py-2 fs-7 mb-4">
                <i class="ki-outline ki-information-5 fs-3 me-1"></i>
                Pilih pasien yang akan melakukan persalinan. Sistem akan otomatis pakai kehamilan aktif kalau ada,
                atau <b>buat catatan kehamilan minimal</b> kalau pasien datang langsung tanpa ANC.
            </div>
            <label class="form-label fs-7 fw-bold">
                <i class="ki-outline ki-magnifier fs-3 text-primary me-1"></i>
                Cari Pasien
            </label>
            <select id="inc_patient_picker" class="form-select form-select-solid"
                    data-control="select2" data-placeholder="Cari pasien: nama / No.RM / NIK / HP..." data-allow-clear="true">
                <option></option>
            </select>
            <div class="form-text fs-8 mt-2">
                💡 Ketik min. 2 huruf. Hanya pasien <b>perempuan</b> yang ditampilkan.
                Pasien baru? <a href="{{ route('admin.patients.create') }}" target="_blank">Daftarkan dulu di sini</a>.
            </div>
        </div>
    </div>

    @push('scripts')
    <x-sweet-flash />
    <script>
    $(function() {
        const $picker = $('#inc_patient_picker');
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
    @endpush
@endunless

{{-- ===== Form Persalinan (cuma muncul kalau pregnancy ada) ===== --}}
@if($pregnancy)
<form action="{{ route('admin.inc.store') }}" method="POST">
    @csrf
    <input type="hidden" name="pregnancy_id" value="{{ $pregnancy->id }}">
    <input type="hidden" name="patient_visit_id" value="{{ $visit?->id }}">
    <input type="hidden" name="visit_date" value="{{ today()->format('Y-m-d') }}">

    <div class="row">
        <div class="col-md-8">
            {{-- Header Pasien & Kehamilan --}}
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-4 p-3 bg-light-success rounded">
                        <span class="symbol symbol-50px"><span class="symbol-label bg-success text-white fs-2 fw-bold">{{ mb_substr($pregnancy->patient->name, 0, 1) }}</span></span>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5">{{ $pregnancy->patient->name }}</div>
                            <div class="text-muted fs-7">
                                <span class="badge badge-light-primary">{{ $pregnancy->patient->no_rm }}</span>
                                · <b>{{ $pregnancy->no_kartu_hamil }}</b>
                                · <span class="badge badge-light-info">{{ $pregnancy->gpa_label }}</span>
                                @if($pregnancy->uk_sekarang)· UK <b>{{ $pregnancy->uk_sekarang }} mg</b>@endif
                                @if($pregnancy->hpl)· HPL <b>{{ $pregnancy->hpl->isoFormat('D MMM') }}</b>@endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION A: PENAPISAN 18 ITEM ===== --}}
            <div class="card mb-5 border border-2 border-danger">
                <div class="card-header bg-light-danger">
                    <h3 class="card-title text-danger">
                        <i class="ki-outline ki-shield-search fs-2 me-1"></i>
                        A. Penapisan Ibu Bersalin (18 Faktor Risiko)
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-warning fs-7">Skor: <span id="penapisan_count">0</span> / 18</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning py-2 fs-7 mb-3">
                        <i class="ki-outline ki-shield-warning fs-3 me-1"></i>
                        <b>Centang faktor risiko yang ditemukan.</b> Jika ada SATU atau lebih → biasanya rujuk ke RS dengan SpOG.
                    </div>

                    <div class="row g-2">
                        @foreach($penapisan as $field => $label)
                            <div class="col-md-6">
                                <label class="d-flex align-items-center gap-2 p-2 rounded penapisan-row cursor-pointer" style="border: 1px solid #e4e6ef; transition: all .15s;">
                                    <input type="checkbox" class="form-check-input penapisan-check" name="{{ $field }}" value="1" @checked(old($field))>
                                    <span class="fs-7 flex-grow-1">{{ $loop->iteration }}. {{ $label }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <style>
                        .penapisan-row.checked { background: #fef2f2; border-color: #f87171 !important; }
                        .penapisan-row.checked span { color: #b91c1c; font-weight: 600; }
                    </style>

                    <div class="row mt-4 g-3">
                        <div class="col-md-12">
                            <label class="form-label fs-7 required">Keputusan</label>
                            <select name="penapisan_keputusan" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="-1" required>
                                @foreach($keputusanOptions as $code => $label)
                                    <option value="{{ $code }}" @selected(old('penapisan_keputusan')===$code)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text fs-9" id="keputusan_info">Berdasarkan skor penapisan</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION B: PEMERIKSAAN SAAT MASUK PMB ===== --}}
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">B. Pemeriksaan Saat Masuk PMB</h3></div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fs-7">Tgl/Jam Masuk</label>
                            <input type="datetime-local" name="masuk_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">Nadi /mnt</label>
                            <input type="number" name="masuk_ttv_nadi" class="form-control form-control-solid" placeholder="60-100 normal">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">Suhu °C</label>
                            <input type="number" step="0.1" name="masuk_ttv_suhu" class="form-control form-control-solid" placeholder="36-37.5">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">RR /mnt</label>
                            <input type="number" name="masuk_ttv_rr" class="form-control form-control-solid" placeholder="12-20">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fs-7">Tekanan Darah</label>
                            <div class="input-group">
                                <input type="number" id="inc_td_s" class="form-control form-control-solid text-center" placeholder="Sistol">
                                <span class="input-group-text">/</span>
                                <input type="number" id="inc_td_d" class="form-control form-control-solid text-center" placeholder="Diastol">
                                <span class="input-group-text">mmHg</span>
                            </div>
                            <input type="hidden" name="masuk_ttv_td" id="inc_td">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-7">DJJ /mnt</label>
                            <input type="number" name="masuk_djj" min="60" max="220" class="form-control form-control-solid" placeholder="120-160">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">His / 10 mnt</label>
                            <input type="number" name="masuk_his_per_10" min="0" max="10" class="form-control form-control-solid" placeholder="kontraksi">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">VT Pembukaan (cm)</label>
                            <input type="number" step="0.5" name="masuk_vt_pembukaan" min="0" max="10" class="form-control form-control-solid" placeholder="0-10">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fs-7">Ketuban</label>
                            <select name="masuk_ketuban" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                                <option></option>
                                @foreach($ketubanOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fs-7">Keluhan</label>
                            <textarea name="masuk_keluhan" rows="2" class="form-control form-control-solid" placeholder="Mis. mules teratur sejak jam 02:00, lendir+darah keluar"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-5">
                <div class="card-header"><h3 class="card-title">Catatan</h3></div>
                <div class="card-body">
                    <textarea name="notes" rows="4" class="form-control form-control-solid">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="card mb-5 border border-2 border-warning">
                <div class="card-header bg-light-warning"><h3 class="card-title text-warning">⚠ Catatan Penting</h3></div>
                <div class="card-body fs-8">
                    <ul class="ps-3">
                        <li>Penapisan ada 18 faktor risiko sesuai standar Kemenkes</li>
                        <li>Jika SKOR > 0 → pertimbangkan rujuk ke RS</li>
                        <li>Setelah submit, sistem otomatis catat <b>SOAP #1 (Masuk PMB)</b></li>
                        <li>4 Kala persalinan dicatat di halaman detail</li>
                        <li>Kehamilan akan auto-update ke status "Partus" saat persalinan selesai</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('admin.anc.show', $pregnancy) }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-danger">
            <i class="ki-outline ki-pulse fs-3"></i> Mulai Persalinan
        </button>
    </div>
</form>
@endif
@endsection

@push('scripts')
<x-sweet-flash />
<script>
$(function() {
    // Combine TD
    function combineTd() {
        const s = $('#inc_td_s').val(), d = $('#inc_td_d').val();
        $('#inc_td').val(s && d ? s + '/' + d : '');
    }
    $('#inc_td_s, #inc_td_d').on('input', combineTd);

    // Penapisan: highlight checked + count + auto-suggest keputusan
    function updatePenapisan() {
        let count = 0;
        $('.penapisan-check').each(function() {
            const $row = $(this).closest('.penapisan-row');
            if (this.checked) { $row.addClass('checked'); count++; } else { $row.removeClass('checked'); }
        });
        $('#penapisan_count').text(count);
        const $sel = $('select[name=penapisan_keputusan]');
        if (count > 0) {
            $sel.val('rujuk').trigger('change');
            $('#keputusan_info').html('<span class="text-danger">🚨 Disarankan RUJUK karena ada ' + count + ' faktor risiko</span>');
        } else {
            $sel.val('lanjut').trigger('change');
            $('#keputusan_info').html('<span class="text-success">✅ Tidak ada faktor risiko, persalinan bisa di klinik</span>');
        }
    }
    $('.penapisan-check').on('change', updatePenapisan);
    updatePenapisan();
});
</script>
@endpush
