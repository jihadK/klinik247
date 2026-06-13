@php $isEdit = $isEdit ?? false; @endphp

<div class="row">
    <div class="col-md-8">
        {{-- ===== Pasien Section ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Pasien</h3></div>
            <div class="card-body">
                {{-- Hidden input patient_id — TETAP di luar wrap supaya tidak ke-hapus saat replace innerHTML --}}
                <input type="hidden" name="patient_id" id="patient_id" value="{{ $patient?->id }}">

                @if($patient)
                    {{-- Pasien sudah dipilih (atau edit mode) --}}
                    <div id="patient_selected_wrap" class="d-flex align-items-center gap-4 p-4 bg-light-success rounded">
                        <span class="symbol symbol-50px">
                            <span class="symbol-label bg-success text-white fs-2 fw-bold">{{ mb_substr($patient->name, 0, 1) }}</span>
                        </span>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5">{{ $patient->name }}</div>
                            <div class="text-muted fs-7">
                                <span class="badge badge-light-primary">{{ $patient->no_rm }}</span>
                                · {{ $patient->gender_label }} · {{ $patient->age }}
                                @if($patient->phone) · 📞 {{ $patient->phone }} @endif
                            </div>
                            <div class="text-muted fs-8 mt-1">
                                NIK: {{ $patient->nik ?? '-' }}
                                · BPJS: {{ $patient->no_bpjs ?? '-' }}
                                · Pembiayaan default: <b>{{ optional($patient->payerType)->name ?? '-' }}</b>
                            </div>
                        </div>
                        @if(! $isEdit)
                            <button type="button" id="btn_change_patient" class="btn btn-sm btn-light-danger">
                                <i class="ki-outline ki-cross fs-3"></i> Ganti
                            </button>
                        @endif
                    </div>
                    {{-- Hidden search wrap, akan di-show kalau user klik Ganti --}}
                    <div id="patient_search_wrap" class="d-none">
                        <label class="form-label fw-semibold required">Cari Pasien</label>
                        <div class="position-relative">
                            <i class="ki-outline ki-magnifier fs-2 position-absolute ms-4 mt-3"></i>
                            <input type="text" id="patient_search" autocomplete="off"
                                   class="form-control form-control-solid ps-14"
                                   placeholder="Ketik nama, No. RM, NIK, BPJS (min 2 huruf)...">
                        </div>
                        <div class="form-text fs-8">
                            Belum terdaftar?
                            <a href="{{ route('admin.patients.create') }}" target="_blank" class="text-primary fw-semibold">
                                Daftarkan pasien baru
                            </a>
                        </div>
                        <div id="patient_results" class="mt-3"></div>
                    </div>
                @else
                    {{-- Search pasien --}}
                    <div id="patient_selected_wrap" class="d-none"></div>
                    <div id="patient_search_wrap">
                        <label class="form-label fw-semibold required">Cari Pasien</label>
                        <div class="position-relative">
                            <i class="ki-outline ki-magnifier fs-2 position-absolute ms-4 mt-3"></i>
                            <input type="text" id="patient_search" autocomplete="off"
                                   class="form-control form-control-solid ps-14"
                                   placeholder="Ketik nama, No. RM, NIK, BPJS (min 2 huruf)..." autofocus>
                        </div>
                        <div class="form-text fs-8">
                            Belum terdaftar?
                            <a href="{{ route('admin.patients.create') }}" target="_blank" class="text-primary fw-semibold">
                                Daftarkan pasien baru
                            </a>
                        </div>
                        <div id="patient_results" class="mt-3"></div>
                        @error('patient_id')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== Detail Kunjungan ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Detail Kunjungan</h3></div>
            <div class="card-body">
                @if(! $isEdit)
                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold required">Kategori</label>
                    <div class="col-md-9">
                        <div class="d-flex flex-nowrap gap-3" id="category_radios">
                            @foreach($categories as $code => $cat)
                                <input type="radio" class="btn-check category-input"
                                       name="category" value="{{ $code }}" id="cat_{{ $code }}"
                                       data-color="{{ $cat['color'] }}"
                                       @checked(old('category', $visit->category)===$code) required>
                                <label for="cat_{{ $code }}"
                                       class="category-card border border-2 rounded p-3 d-flex align-items-center gap-2 mb-0 cursor-pointer flex-fill"
                                       data-color="{{ $cat['color'] }}">
                                    <i class="ki-outline {{ $cat['icon'] }} fs-1 category-icon text-{{ $cat['color'] }}"></i>
                                    <div>
                                        <div class="fw-bolder fs-2 lh-1 category-code text-{{ $cat['color'] }}">{{ $code }}</div>
                                        <div class="fs-7 fw-semibold text-gray-700">{{ $cat['label'] }}</div>
                                    </div>
                                    <i class="ki-solid ki-check-circle fs-2 text-{{ $cat['color'] }} ms-2 category-check d-none"></i>
                                </label>
                            @endforeach
                        </div>
                        @error('category')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
                        <div class="form-text fs-8 mt-3">
                            <b>A</b>=Anak · <b>I</b>=Ibu (Hamil/Nifas/Reguler) · <b>K</b>=KB · <b>R</b>=Reproduksi
                        </div>
                    </div>
                </div>

                <style>
                    /* Custom radio card style — clear visual feedback saat checked */
                    .category-card {
                        cursor: pointer;
                        flex: 1 1 0;
                        min-width: 0;
                        background: #fff;
                        border-color: #e4e6ef !important;
                        transition: all .15s ease;
                    }
                    .category-card:hover {
                        border-color: #b5b5c3 !important;
                        background: #f9f9fb;
                    }
                    .btn-check:checked + .category-card[data-color="danger"]  { background: #fff5f8; border-color: #f1416c !important; box-shadow: 0 0 0 3px rgba(241,65,108,.18); }
                    .btn-check:checked + .category-card[data-color="info"]    { background: #f0f9ff; border-color: #7239ea !important; box-shadow: 0 0 0 3px rgba(114,57,234,.18); }
                    .btn-check:checked + .category-card[data-color="primary"] { background: #eef6ff; border-color: #009ef7 !important; box-shadow: 0 0 0 3px rgba(0,158,247,.18); }
                    .btn-check:checked + .category-card[data-color="warning"] { background: #fff8dd; border-color: #ffc700 !important; box-shadow: 0 0 0 3px rgba(255,199,0,.22); }
                    .btn-check:checked + .category-card .category-check { display: inline-block !important; }
                    .btn-check:checked + .category-card { transform: translateY(-2px); }
                </style>
                @else
                    <div class="row mb-4">
                        <label class="col-form-label col-md-3 fw-semibold">Kategori</label>
                        <div class="col-md-9">
                            <span class="badge badge-light-{{ $visit->category_color }} fs-6">{{ $visit->category }} — {{ $visit->category_label }}</span>
                            <span class="ms-2 text-muted fs-7">Kategori tidak bisa diubah (terkait no register).</span>
                        </div>
                    </div>
                @endif

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Jenis Kunjungan</label>
                    <div class="col-md-9">
                        <select name="visit_type" id="visit_type" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih jenis..." data-allow-clear="true">
                            <option></option>
                            @foreach($visitTypes as $code => $label)
                                <option value="{{ $code }}" @selected(old('visit_type', $visit->visit_type)===$code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        {{-- History info — diisi oleh JS saat patient + category dipilih --}}
                        <div id="visit_history_info" class="mt-2"></div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold required">Tanggal Kunjungan</label>
                    <div class="col-md-5">
                        <input type="date" name="visit_date"
                               value="{{ old('visit_date', optional($visit->visit_date)->format('Y-m-d') ?? today()->format('Y-m-d')) }}"
                               class="form-control form-control-solid" required {{ $isEdit ? 'readonly' : '' }}>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Pembiayaan</label>
                    <div class="col-md-9">
                        <select name="payer_type_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih pembiayaan..." data-allow-clear="true">
                            <option></option>
                            @foreach($payerTypes as $pt)
                                <option value="{{ $pt->id }}" @selected((int)old('payer_type_id', $visit->payer_type_id ?? $patient?->payer_type_id) === $pt->id)>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text fs-8">Kosongkan untuk pakai default dari data pasien.</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Keluhan Utama</label>
                    <div class="col-md-9">
                        <textarea name="chief_complaint" rows="3" class="form-control form-control-solid"
                                  placeholder="Mis. Periksa kehamilan rutin, demam 2 hari, kontrol KB suntik, dll">{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Catatan</label>
                    <div class="col-md-9">
                        <textarea name="notes" rows="2" class="form-control form-control-solid">{{ old('notes', $visit->notes) }}</textarea>
                    </div>
                </div>

                @if($isEdit && isset($statuses))
                    <div class="row mb-4">
                        <label class="col-form-label col-md-3 fw-semibold">Status</label>
                        <div class="col-md-9">
                            <select name="status" class="form-select form-select-solid"
                                    data-control="select2" data-minimum-results-for-search="-1">
                                @foreach($statuses as $code => $s)
                                    <option value="{{ $code }}" @selected(old('status', $visit->status)===$code)>{{ $s['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4 d-none" id="cancel_reason_row">
                        <label class="col-form-label col-md-3 fw-semibold">Alasan Batal</label>
                        <div class="col-md-9">
                            <textarea name="cancel_reason" rows="2" class="form-control form-control-solid">{{ old('cancel_reason', $visit->cancel_reason) }}</textarea>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Info Pendaftaran</h3></div>
            <div class="card-body">
                @if($isEdit)
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">No. Register</span> <b class="font-monospace">{{ $visit->no_register }}</b></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">No. Antrian</span> <b>#{{ $visit->queue_number }}</b></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tgl Daftar</span> <b>{{ $visit->visit_time?->isoFormat('D MMM YY HH:mm') }}</b></div>
                @else
                    <div class="alert alert-info py-3 mb-0">
                        <div class="fw-bold mb-1">🔢 No. Register otomatis</div>
                        <div class="fs-8">Format: <code>K-SS-YYYY-NNNNNN</code> (K=kategori, SS=site, NNNNNN=urut)</div>
                        <div class="fs-8 mt-1">Contoh: <code>I-01-2026-000001</code> = Ibu, klinik 01, kunjungan ke-1</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('admin.visits.index') }}" class="btn btn-light">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="ki-outline ki-check fs-3"></i> {{ $isEdit ? 'Update' : 'Daftarkan' }}
    </button>
</div>

@push('scripts')
<script>
$(function() {
    // ===== AJAX Search Pasien =====
    let $search = $('#patient_search'), $results = $('#patient_results'), $pid = $('#patient_id');
    let searchTimer;

    $search.on('input', function() {
        clearTimeout(searchTimer);
        const term = this.value.trim();
        if (term.length < 2) { $results.empty(); return; }
        searchTimer = setTimeout(() => doSearch(term), 250);
    });

    function doSearch(term) {
        $results.html('<div class="text-muted text-center py-3"><i class="spinner-border spinner-border-sm"></i> Mencari...</div>');
        $.get('{{ route("admin.visits.ajax.search-patient") }}', {q: term})
            .done(res => {
                const data = res.data || [];
                if (!data.length) {
                    $results.html('<div class="text-muted text-center py-3 fs-7">Tidak ada pasien cocok. <a href="{{ route("admin.patients.create") }}" target="_blank">Daftarkan baru</a>?</div>');
                    return;
                }
                $results.html(data.map(p => `
                    <div class="d-flex align-items-center gap-3 p-3 border rounded mb-2 patient-pick"
                         style="cursor:pointer;" data-id="${p.id}" data-rm="${p.no_rm}" data-name="${p.name}"
                         data-payer="${p.payer || ''}">
                        <span class="symbol symbol-40px"><span class="symbol-label bg-light-primary text-primary fw-bold">${p.name.charAt(0)}</span></span>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${p.name}</div>
                            <div class="text-muted fs-8">
                                <span class="badge badge-light-primary">${p.no_rm}</span>
                                · ${p.gender} · ${p.age || '-'}
                                ${p.phone ? '· 📞 ' + p.phone : ''}
                            </div>
                        </div>
                        <i class="ki-outline ki-arrow-right fs-2 text-primary"></i>
                    </div>
                `).join(''));
            })
            .fail(() => $results.html('<div class="text-danger fs-7">Gagal cari pasien.</div>'));
    }

    $(document).on('click', '.patient-pick', function() {
        const $el = $(this);
        // 1. Set hidden input value (input ini sudah ada di luar wrap, tidak ke-hapus)
        $('#patient_id').val($el.data('id'));

        // 2. Tampilkan card pasien terpilih
        $('#patient_selected_wrap').removeClass('d-none').html(`
            <span class="symbol symbol-50px"><span class="symbol-label bg-success text-white fs-2 fw-bold">${$el.data('name').charAt(0)}</span></span>
            <div class="flex-grow-1">
                <div class="fw-bold fs-5">${$el.data('name')}</div>
                <div class="text-muted fs-7"><span class="badge badge-light-primary">${$el.data('rm')}</span></div>
            </div>
            <button type="button" id="btn_change_patient" class="btn btn-sm btn-light-danger">
                <i class="ki-outline ki-cross fs-3"></i> Ganti
            </button>
        `).addClass('d-flex align-items-center gap-4 p-4 bg-light-success rounded');

        // 3. Sembunyikan area search
        $('#patient_search_wrap').addClass('d-none');
        $('#patient_search').val('');
        $('#patient_results').empty();
    });

    $(document).on('click', '#btn_change_patient', function() {
        // Reset: clear pilihan, tampilkan search lagi
        $('#patient_id').val('');
        $('#patient_selected_wrap').addClass('d-none').removeClass('d-flex align-items-center gap-4 p-4 bg-light-success rounded').empty();
        $('#patient_search_wrap').removeClass('d-none');
        $('#patient_search').val('').focus();
    });

    // ===== Status change: tampilkan/sembunyikan cancel_reason =====
    $('select[name=status]').on('change', function() {
        $('#cancel_reason_row').toggleClass('d-none', this.value !== 'cancelled');
    }).trigger('change');

    // ===== Auto-suggest visit_type berdasarkan history pasien per kategori =====
    let lastHistoryKey = null;

    function checkVisitHistory() {
        const patientId = $('#patient_id').val();
        const category  = $('input[name=category]:checked').val();
        if (! patientId || ! category) {
            $('#visit_history_info').empty();
            return;
        }
        const key = patientId + ':' + category;
        if (key === lastHistoryKey) return;   // skip kalau sudah pernah di-fetch
        lastHistoryKey = key;

        $.get('{{ route("admin.visits.ajax.check-history") }}', { patient_id: patientId, category })
            .done(res => {
                const d = res.data;
                if (! d) { $('#visit_history_info').empty(); return; }

                if (d.has_history) {
                    // Auto-set Jenis Kunjungan = "kontrol" jika user belum pilih manual
                    const $vt = $('#visit_type');
                    if (! $vt.val()) {
                        $vt.val('kontrol').trigger('change');
                    }
                    $('#visit_history_info').html(`
                        <div class="alert alert-info py-2 px-3 mb-0 fs-8">
                            <i class="ki-outline ki-information-5 fs-3 text-info me-1"></i>
                            <b>Pasien ini sudah pernah kunjungan kategori ${category}</b> sebanyak <b>${d.count}x</b>.
                            Terakhir: <span class="font-monospace">${d.last_no_register || '-'}</span>
                            (${d.last_visit_date || '-'} · status: ${d.last_status || '-'})
                            ${d.last_complaint ? '<br><span class="text-muted">Keluhan lalu: ' + d.last_complaint.substring(0, 100) + '</span>' : ''}
                            <div class="mt-1 text-success"><i class="ki-outline ki-check fs-4"></i> Jenis kunjungan otomatis disarankan: <b>Kontrol/Ulangan</b></div>
                        </div>
                    `);
                } else {
                    // Pasien BARU di kategori ini → suggest "baru"
                    const $vt = $('#visit_type');
                    if (! $vt.val()) {
                        $vt.val('baru').trigger('change');
                    }
                    $('#visit_history_info').html(`
                        <div class="alert alert-light-success py-2 px-3 mb-0 fs-8">
                            <i class="ki-outline ki-sparkles fs-3 text-success me-1"></i>
                            <b>Kunjungan pertama</b> kategori ${category} untuk pasien ini.
                            Jenis kunjungan otomatis disarankan: <b>Pasien Baru</b>.
                        </div>
                    `);
                }
            })
            .fail(() => $('#visit_history_info').empty());
    }

    // Trigger saat patient dipilih (lewat AJAX search) atau category berubah
    $(document).on('click', '.patient-pick', () => setTimeout(checkVisitHistory, 200));
    $(document).on('change', 'input[name=category]', checkVisitHistory);
    // Trigger saat halaman load (kalau patient sudah ada via ?patient_id=)
    setTimeout(checkVisitHistory, 300);
});
</script>
@endpush
