@php $isEdit = $isEdit ?? false; @endphp

<div class="row">
    {{-- ========== KIRI: Identitas + Alamat + Medis ========== --}}
    <div class="col-md-8">

        {{-- ===== Identitas Utama ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Identitas Pasien</h3></div>
            <div class="card-body">

                @if($isEdit)
                    <div class="row mb-4">
                        <label class="col-form-label col-md-3 fw-semibold">No. RM</label>
                        <div class="col-md-9">
                            <input type="text" value="{{ $patient->no_rm }}" class="form-control form-control-solid" readonly>
                            <div class="form-text fs-8">No. RM tidak bisa diubah.</div>
                        </div>
                    </div>
                @else
                    <div class="row mb-4">
                        <label class="col-form-label col-md-3 fw-semibold">No. RM</label>
                        <div class="col-md-9">
                            <input type="text" value="(otomatis di-generate saat simpan)" class="form-control form-control-solid" readonly>
                            <div class="form-text fs-8">Format: <code>SS-YYYY-NNNNNN</code> — contoh <code>01-2026-000001</code></div>
                        </div>
                    </div>
                @endif

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">CM Lama</label>
                    <div class="col-md-9">
                        <input type="text" name="cm_lama" value="{{ old('cm_lama', $patient->cm_lama) }}"
                               class="form-control form-control-solid @error('cm_lama') is-invalid @enderror"
                               placeholder="Nomor RM dari klinik lain (opsional)" maxlength="50">
                        @error('cm_lama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">NIK (KTP)</label>
                    <div class="col-md-9">
                        <input type="text" name="nik" value="{{ old('nik', $patient->nik) }}"
                               class="form-control form-control-solid @error('nik') is-invalid @enderror"
                               placeholder="16 digit NIK" maxlength="16" inputmode="numeric">
                        @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">No. BPJS</label>
                    <div class="col-md-9">
                        <input type="text" name="no_bpjs" value="{{ old('no_bpjs', $patient->no_bpjs) }}"
                               class="form-control form-control-solid @error('no_bpjs') is-invalid @enderror"
                               maxlength="20" inputmode="numeric">
                        @error('no_bpjs')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">No. KK</label>
                    <div class="col-md-9">
                        <div class="position-relative">
                            <input type="text" name="no_kk" id="no_kk" value="{{ old('no_kk', $patient->no_kk) }}"
                                   class="form-control form-control-solid" maxlength="20" inputmode="numeric"
                                   placeholder="16 digit No. KK" autocomplete="off">
                            <span class="position-absolute top-50 translate-middle-y end-0 me-4 d-none" id="kk_lookup_spinner">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </span>

                            {{-- Suggestion dropdown (muncul saat >= 7 digit) --}}
                            <div id="kk_suggestions" class="position-absolute w-100 bg-body border border-gray-300 rounded shadow-sm d-none"
                                 style="top: 100%; z-index: 1050; max-height: 300px; overflow-y: auto;"></div>
                        </div>
                        <div class="form-text fs-8">
                            💡 Ketik min. 7 digit untuk lihat saran KK keluarga yang sudah terdaftar. Sistem juga auto-cek alamat keluarga saat KK lengkap.
                        </div>
                        <div id="kk_lookup_result" class="mt-2"></div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Nama KK</label>
                    <div class="col-md-9">
                        <div class="position-relative">
                            <input type="text" name="nama_kk" id="nama_kk" value="{{ old('nama_kk', $patient->nama_kk) }}"
                                   class="form-control form-control-solid" maxlength="150"
                                   placeholder="Nama kepala keluarga" autocomplete="off">
                            {{-- Suggestion dropdown nama KK (mirrored dari KK suggestions) --}}
                            <div id="nama_kk_suggestions" class="position-absolute w-100 bg-body border border-gray-300 rounded shadow-sm d-none"
                                 style="top: 100%; z-index: 1050; max-height: 300px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>

                <div class="separator my-4"></div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold required">Nama Lengkap</label>
                    <div class="col-md-9">
                        <input type="text" name="name" value="{{ old('name', $patient->name) }}"
                               class="form-control form-control-solid @error('name') is-invalid @enderror"
                               maxlength="150" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Tempat Lahir</label>
                    <div class="col-md-9">
                        <input type="text" name="birth_place" value="{{ old('birth_place', $patient->birth_place) }}"
                               class="form-control form-control-solid" maxlength="100">
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold required">Tanggal Lahir</label>
                    <div class="col-md-5">
                        <input type="date" name="birth_date"
                               value="{{ old('birth_date', optional($patient->birth_date)->format('Y-m-d')) }}"
                               class="form-control form-control-solid @error('birth_date') is-invalid @enderror"
                               max="{{ date('Y-m-d') }}" required>
                        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold required">Jenis Kelamin</label>
                    <div class="col-md-9">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="g_L" value="L"
                                   @checked(old('gender', $patient->gender)==='L') required>
                            <label class="form-check-label" for="g_L">Laki-laki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="g_P" value="P"
                                   @checked(old('gender', $patient->gender)==='P')>
                            <label class="form-check-label" for="g_P">Perempuan</label>
                        </div>
                        @error('gender')<div class="text-danger fs-7">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Status Kawin</label>
                    <div class="col-md-9">
                        <select name="marital_status" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih status kawin..." data-allow-clear="true">
                            <option></option>
                            @foreach(['belum_menikah'=>'Belum Menikah','menikah'=>'Menikah','cerai_hidup'=>'Cerai Hidup','cerai_mati'=>'Cerai Mati'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('marital_status', $patient->marital_status)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Agama</label>
                    <div class="col-md-9">
                        <select name="religion_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih agama..." data-allow-clear="true">
                            <option></option>
                            @foreach($religions as $r)
                                <option value="{{ $r->id }}" @selected((int)old('religion_id', $patient->religion_id) === $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Pendidikan</label>
                    <div class="col-md-9">
                        <select name="education_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih pendidikan..." data-allow-clear="true">
                            <option></option>
                            @foreach($educations as $e)
                                <option value="{{ $e->id }}" @selected((int)old('education_id', $patient->education_id) === $e->id)>{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Pekerjaan</label>
                    <div class="col-md-9">
                        <input type="text" name="occupation" value="{{ old('occupation', $patient->occupation) }}"
                               class="form-control form-control-solid" maxlength="100">
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Gol. Darah</label>
                    <div class="col-md-9">
                        <select name="blood_type" class="form-select form-select-solid w-200px"
                                data-control="select2" data-placeholder="Pilih..." data-allow-clear="true" data-minimum-results-for-search="-1">
                            <option></option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type', $patient->blood_type)===$bt)>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Pembiayaan</label>
                    <div class="col-md-9">
                        <select name="payer_type_id" class="form-select form-select-solid"
                                data-control="select2" data-placeholder="Pilih jenis pembiayaan..." data-allow-clear="true">
                            <option></option>
                            @foreach($payerTypes as $pt)
                                <option value="{{ $pt->id }}" @selected((int)old('payer_type_id', $patient->payer_type_id) === $pt->id)>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Alamat & Kontak ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Alamat &amp; Kontak</h3></div>
            <div class="card-body">

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Alamat Lengkap</label>
                    <div class="col-md-9">
                        <textarea name="address" rows="5" class="form-control form-control-solid"
                                  placeholder="Tulis alamat lengkap (jalan, RT/RW, dusun, desa, kecamatan, kabupaten, kode pos)...">{{ old('address', $patient->address) }}</textarea>
                        <div class="form-text fs-8">Free-text — tulis selengkap mungkin sesuai KK.</div>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">No. HP / Telp</label>
                    <div class="col-md-5">
                        <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}"
                               class="form-control form-control-solid" maxlength="20" placeholder="0851-1234-5678">
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Email</label>
                    <div class="col-md-9">
                        <input type="email" name="email" value="{{ old('email', $patient->email) }}"
                               class="form-control form-control-solid" maxlength="100">
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Riwayat Medis ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Riwayat Medis</h3></div>
            <div class="card-body">
                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Alergi</label>
                    <div class="col-md-9">
                        <textarea name="allergies" rows="2" class="form-control form-control-solid"
                                  placeholder="Mis. Penisilin, udang, debu">{{ old('allergies', $patient->allergies) }}</textarea>
                    </div>
                </div>
                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Penyakit Kronis</label>
                    <div class="col-md-9">
                        <textarea name="chronic_diseases" rows="2" class="form-control form-control-solid"
                                  placeholder="Mis. Hipertensi, Diabetes">{{ old('chronic_diseases', $patient->chronic_diseases) }}</textarea>
                    </div>
                </div>
                <div class="row mb-4">
                    <label class="col-form-label col-md-3 fw-semibold">Riwayat Penyakit</label>
                    <div class="col-md-9">
                        <textarea name="medical_history" rows="3" class="form-control form-control-solid">{{ old('medical_history', $patient->medical_history) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== KANAN: Foto + Kontak Darurat + Status ========== --}}
    <div class="col-md-4">
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Foto Pasien</h3></div>
            <div class="card-body text-center">
                <div class="image-input image-input-outline mb-3" data-kt-image-input="true"
                     style="background-image:url('{{ asset('assets/media/svg/avatars/blank.svg') }}')">
                    <div class="image-input-wrapper w-150px h-150px"
                         @if($patient->photo_url) style="background-image:url('{{ asset('storage/'.$patient->photo_url) }}')" @endif>
                    </div>
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                           data-kt-image-input-action="change">
                        <i class="ki-outline ki-pencil fs-7"></i>
                        <input type="file" name="photo" accept=".png,.jpg,.jpeg,.webp">
                    </label>
                </div>
                <div class="form-text fs-8">JPG/PNG/WebP — Maks 2 MB</div>
                @error('photo')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Kontak Darurat</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fs-7">Nama</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $patient->emergency_contact) }}"
                           class="form-control form-control-solid" maxlength="150">
                </div>
                <div class="mb-3">
                    <label class="form-label fs-7">No. HP</label>
                    <input type="text" name="emergency_phone" value="{{ old('emergency_phone', $patient->emergency_phone) }}"
                           class="form-control form-control-solid" maxlength="20">
                </div>
                <div class="mb-3">
                    <label class="form-label fs-7">Hubungan</label>
                    <input type="text" name="emergency_relation" value="{{ old('emergency_relation', $patient->emergency_relation) }}"
                           class="form-control form-control-solid" placeholder="Suami/Istri/Anak/Orang Tua" maxlength="50">
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Status & Catatan</h3></div>
            <div class="card-body">
                <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                           @checked(old('is_active', $patient->is_active ?? true))>
                    <label class="form-check-label fw-semibold ms-3" for="is_active">Pasien Aktif</label>
                </div>
                <div>
                    <label class="form-label fs-7">Catatan Internal</label>
                    <textarea name="notes" rows="3" class="form-control form-control-solid">{{ old('notes', $patient->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('admin.patients.index') }}" class="btn btn-light">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="ki-outline ki-check fs-3"></i> {{ $isEdit ? 'Update' : 'Simpan' }}
    </button>
</div>

@push('scripts')
<script>
// ===== Lookup No. KK → suggestion dropdown + auto-fill nama_kk + apply alamat keluarga =====
$(function() {
    const $kk        = $('#no_kk');
    const $namaKk    = $('#nama_kk');
    const $spinner   = $('#kk_lookup_spinner');
    const $result    = $('#kk_lookup_result');
    const $sugKk     = $('#kk_suggestions');
    const $sugNamaKk = $('#nama_kk_suggestions');
    let kkTimer;
    let suggestTimer;
    let lastLookup = null;
    let lastSuggest = null;

    // ===== Suggest dropdown — show saat >= 7 digit =====
    function doSuggest() {
        const val = $kk.val().replace(/\D/g, '');
        if (val.length < 7) { $sugKk.addClass('d-none').empty(); return; }
        if (val === lastSuggest) return;
        lastSuggest = val;

        $.get('{{ route("admin.patients.ajax.suggest-kk") }}', { q: val })
            .done(res => {
                const rows = res.data || [];
                if (! rows.length) { $sugKk.addClass('d-none').empty(); return; }

                const html = rows.map(r => `
                    <div class="kk-sug-item p-3 border-bottom cursor-pointer"
                         data-no-kk="${r.no_kk}"
                         data-nama-kk="${(r.nama_kk || '').replace(/"/g,'&quot;')}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold font-monospace">${r.no_kk}</div>
                                <div class="text-muted fs-8">
                                    KK: <b>${r.nama_kk || '-'}</b>
                                    · Anggota: ${r.member_count}
                                    · Contoh: ${r.sample_name}
                                </div>
                            </div>
                            <i class="ki-outline ki-arrow-right fs-3 text-primary"></i>
                        </div>
                    </div>
                `).join('');
                $sugKk.html(html).removeClass('d-none');
            })
            .fail(() => $sugKk.addClass('d-none').empty());
    }

    // Pilih suggestion → fill kedua field + trigger lookup full
    $(document).on('click', '.kk-sug-item', function() {
        const noKk   = $(this).data('no-kk');
        const namaKk = $(this).data('nama-kk');
        $kk.val(noKk);
        if (namaKk) $namaKk.val(namaKk);
        $sugKk.addClass('d-none').empty();
        $sugNamaKk.addClass('d-none').empty();
        doLookup(); // jalankan lookup lengkap untuk dapatkan alamat
    });

    // Klik di luar → tutup suggestion
    $(document).on('click', function(e) {
        if (! $(e.target).closest('#no_kk, #kk_suggestions').length) $sugKk.addClass('d-none');
        if (! $(e.target).closest('#nama_kk, #nama_kk_suggestions').length) $sugNamaKk.addClass('d-none');
    });

    // Nama KK suggestion: tampilkan saran berdasarkan KK yang sudah terdeteksi (kalau ada)
    $namaKk.on('focus', function() {
        const val = $kk.val().replace(/\D/g, '');
        if (val.length < 7) return;
        // Re-fetch suggestion list, tapi tampilkan sebagai pilihan nama
        $.get('{{ route("admin.patients.ajax.suggest-kk") }}', { q: val })
            .done(res => {
                const rows = (res.data || []).filter(r => r.nama_kk);
                if (! rows.length) { $sugNamaKk.addClass('d-none').empty(); return; }
                const html = rows.map(r => `
                    <div class="kk-sug-item p-3 border-bottom cursor-pointer"
                         data-no-kk="${r.no_kk}" data-nama-kk="${(r.nama_kk || '').replace(/"/g,'&quot;')}">
                        <div class="fw-bold">${r.nama_kk}</div>
                        <div class="text-muted fs-8 font-monospace">${r.no_kk} · ${r.member_count} anggota</div>
                    </div>
                `).join('');
                $sugNamaKk.html(html).removeClass('d-none');
            });
    });

    function doLookup() {
        const val = $kk.val().replace(/\D/g, '');
        if (val.length < 8) { $result.empty(); return; }
        // Skip kalau val sama dengan lookup terakhir
        if (val === lastLookup) return;
        lastLookup = val;

        $spinner.removeClass('d-none');
        $result.empty();

        $.get('{{ route("admin.patients.ajax.lookup-kk") }}', { no_kk: val })
            .done(res => {
                $spinner.addClass('d-none');
                const d = res.data;
                if (! d || ! d.found) {
                    $result.html('<div class="badge badge-light-info fs-8 py-2 px-3">📋 KK baru — silakan isi Nama KK & alamat manual</div>');
                    return;
                }

                // Auto-fill Nama KK kalau masih kosong
                if (! $namaKk.val() && d.nama_kk) {
                    $namaKk.val(d.nama_kk);
                }

                $result.html(`
                    <div class="alert alert-success py-3 mb-0">
                        <div class="d-flex align-items-start gap-3">
                            <i class="ki-outline ki-check-circle fs-2 text-success mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold">✓ KK sudah terdaftar (${d.count} anggota keluarga)</div>
                                <div class="text-muted fs-8 mb-2">
                                    Nama KK: <b>${d.nama_kk || '-'}</b>
                                    · Contoh anggota: ${d.sample_name}
                                </div>
                                <button type="button" id="btn_apply_kk_address" class="btn btn-sm btn-success">
                                    <i class="ki-outline ki-copy fs-3"></i> Pakai alamat keluarga ini
                                </button>
                            </div>
                        </div>
                    </div>
                `);

                // Stash data ke element untuk dipakai apply button
                $('#btn_apply_kk_address').data('payload', d);
            })
            .fail(() => {
                $spinner.addClass('d-none');
                $result.html('<div class="text-danger fs-8">Gagal cek KK.</div>');
            });
    }

    $kk.on('blur', function() {
        // Delay supaya click pada suggestion sempat ke-handle dulu
        setTimeout(doLookup, 200);
    });
    $kk.on('input', function() {
        clearTimeout(kkTimer);
        clearTimeout(suggestTimer);
        const len = this.value.replace(/\D/g, '').length;
        // Suggest dropdown saat >= 7 digit (debounce 250ms)
        if (len >= 7) suggestTimer = setTimeout(doSuggest, 250);
        else $sugKk.addClass('d-none').empty();
        // Lookup penuh saat sudah 16 digit
        if (len >= 16) kkTimer = setTimeout(doLookup, 300);
    });

    // Apply alamat dari KK existing — free-text mode
    $(document).on('click', '#btn_apply_kk_address', function() {
        const d = $(this).data('payload');
        if (! d) return;

        if (d.address) $('textarea[name=address]').val(d.address);

        // Visual feedback
        $(this).removeClass('btn-success').addClass('btn-light-success')
               .html('<i class="ki-outline ki-check fs-3"></i> Alamat keluarga diterapkan');
        setTimeout(() => $(this).prop('disabled', true), 100);
    });
});
</script>
@endpush
