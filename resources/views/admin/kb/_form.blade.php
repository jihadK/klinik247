@php
    $isEdit = $isEdit ?? false;
    // Parse tekanan darah "120/80" jadi 2 part untuk input group
    $tdCurrent  = old('tekanan_darah', $acceptor->tekanan_darah ?? '');
    $tdParts    = $tdCurrent ? explode('/', $tdCurrent) : ['', ''];
    $tdSistol   = trim($tdParts[0] ?? '');
    $tdDiastol  = trim($tdParts[1] ?? '');
@endphp

<input type="hidden" name="patient_id" value="{{ $patient?->id }}">
<input type="hidden" name="patient_visit_id" value="{{ $visit?->id ?? $acceptor->patient_visit_id }}">

<div class="row">
    <div class="col-md-8">

        {{-- ===== PASIEN HEADER ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Akseptor</h3></div>
            <div class="card-body">
                @if($patient)
                    <div class="d-flex align-items-center gap-4 p-3 bg-light-success rounded">
                        <span class="symbol symbol-50px"><span class="symbol-label bg-success text-white fs-2 fw-bold">{{ mb_substr($patient->name, 0, 1) }}</span></span>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5">{{ $patient->name }}</div>
                            <div class="text-muted fs-7">
                                <span class="badge badge-light-primary">{{ $patient->no_rm }}</span>
                                · {{ $patient->gender_label }} · {{ $patient->age }}
                                @if($patient->phone) · 📞 {{ $patient->phone }} @endif
                            </div>
                        </div>
                        @if(! ($isEdit ?? false))
                            <a href="{{ route('admin.kb.create') }}" class="btn btn-sm btn-light-warning" title="Ganti pasien">
                                <i class="ki-outline ki-arrows-loop fs-3"></i> Ganti
                            </a>
                        @endif
                    </div>
                @else
                    <label class="form-label fs-7 fw-bold mb-2">
                        <i class="ki-outline ki-magnifier fs-3 text-primary me-1"></i>
                        Pilih Pasien (Akseptor KB)
                    </label>
                    <select id="kb_patient_picker" class="form-select form-select-solid"
                            data-control="select2" data-placeholder="Cari pasien: nama / No.RM / NIK / HP..." data-allow-clear="true">
                        <option></option>
                    </select>
                    <div class="form-text fs-8 mt-2">
                        💡 Ketik min. 2 huruf untuk cari pasien. Hanya pasien <b>perempuan</b> usia subur yang ditampilkan.
                        Pasien baru? <a href="{{ route('admin.patients.create') }}" target="_blank">Daftarkan dulu di sini</a>.
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== SECTION A — Status Peserta KB Baru (8 questions) ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">A. Status Peserta KB Baru</h3></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fs-7">1. Jumlah Anak Hidup</label>
                        <input type="number" name="jumlah_anak_hidup" value="{{ old('jumlah_anak_hidup', $acceptor->jumlah_anak_hidup) }}"
                               min="0" max="30" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">2. Keinginan Punya Anak Lagi?</label>
                        <select name="keinginan_punya_anak_lagi" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-minimum-results-for-search="-1" data-placeholder="—">
                            <option></option>
                            @foreach(['ya'=>'Ya','tidak'=>'Tidak','tidak_tahu'=>'Tidak Tahu'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('keinginan_punya_anak_lagi', $acceptor->keinginan_punya_anak_lagi)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">3. Kapan Ingin Anak Lagi</label>
                        <input type="text" name="kapan_ingin_anak_lagi" value="{{ old('kapan_ingin_anak_lagi', $acceptor->kapan_ingin_anak_lagi) }}"
                               class="form-control form-control-solid" placeholder="mis. 2 tahun lagi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">4. Status Kehamilan Saat Ini</label>
                        <select name="status_kehamilan_saat_ini" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-minimum-results-for-search="-1" data-placeholder="—">
                            <option></option>
                            @foreach(['hamil'=>'Hamil','tidak_hamil'=>'Tidak Hamil','tidak_tahu'=>'Tidak Tahu'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('status_kehamilan_saat_ini', $acceptor->status_kehamilan_saat_ini)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">5. Riwayat Komplikasi Kehamilan</label>
                        <input type="text" name="riwayat_komplikasi_kehamilan" value="{{ old('riwayat_komplikasi_kehamilan', $acceptor->riwayat_komplikasi_kehamilan) }}"
                               class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">6. Sikap Pasangan Terhadap KB</label>
                        <select name="sikap_pasangan_terhadap_kb" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-minimum-results-for-search="-1" data-placeholder="—">
                            <option></option>
                            @foreach(['setuju'=>'Setuju','tidak_setuju'=>'Tidak Setuju','netral'=>'Netral'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('sikap_pasangan_terhadap_kb', $acceptor->sikap_pasangan_terhadap_kb)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch form-check-custom mt-7">
                            <input class="form-check-input" type="checkbox" name="edukasi_hiv_aids_pms" value="1" id="ed_hiv" @checked(old('edukasi_hiv_aids_pms', $acceptor->edukasi_hiv_aids_pms))>
                            <label class="form-check-label fw-semibold ms-3" for="ed_hiv">7. Sudah Diberi Edukasi HIV/AIDS/PMS</label>
                        </div>
                        <div class="form-check form-switch form-check-custom mt-3">
                            <input class="form-check-input" type="checkbox" name="metode_ganda_pakai_kondom" value="1" id="mg_kondom" @checked(old('metode_ganda_pakai_kondom', $acceptor->metode_ganda_pakai_kondom))>
                            <label class="form-check-label fw-semibold ms-3" for="mg_kondom">8. Metode Ganda (Pakai Kondom)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SECTION B — Pemeriksaan Awal (9 items) ===== --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">B. Pemeriksaan Awal</h3></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <label class="form-label fs-7">Tekanan Darah</label>
                        <div class="input-group">
                            <input type="number" id="td_sistol" min="50" max="300" value="{{ $tdSistol }}"
                                   class="form-control form-control-solid text-center" placeholder="Sistol">
                            <span class="input-group-text fw-bold">/</span>
                            <input type="number" id="td_diastol" min="30" max="200" value="{{ $tdDiastol }}"
                                   class="form-control form-control-solid text-center" placeholder="Diastol">
                            <span class="input-group-text fw-semibold">mmHg</span>
                        </div>
                        <input type="hidden" name="tekanan_darah" id="tekanan_darah" value="{{ $tdCurrent }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="berat_badan" value="{{ old('berat_badan', $acceptor->berat_badan) }}"
                               class="form-control form-control-solid">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">Haid Terakhir</label>
                        <input type="date" name="haid_terakhir" value="{{ old('haid_terakhir', optional($acceptor->haid_terakhir)->format('Y-m-d')) }}"
                               class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">Tanggal Persalinan Terakhir</label>
                        <input type="date" name="tanggal_persalinan_terakhir" value="{{ old('tanggal_persalinan_terakhir', optional($acceptor->tanggal_persalinan_terakhir)->format('Y-m-d')) }}"
                               class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch form-check-custom mt-7">
                            <input class="form-check-input" type="checkbox" name="kebiasaan_merokok" value="1" id="merokok" @checked(old('kebiasaan_merokok', $acceptor->kebiasaan_merokok))>
                            <label class="form-check-label fw-semibold ms-3" for="merokok">Kebiasaan Merokok</label>
                        </div>
                        <div class="form-check form-switch form-check-custom mt-3">
                            <input class="form-check-input" type="checkbox" name="sedang_menyusui" value="1" id="menyusui" @checked(old('sedang_menyusui', $acceptor->sedang_menyusui))>
                            <label class="form-check-label fw-semibold ms-3" for="menyusui">Sedang Menyusui</label>
                        </div>
                    </div>
                </div>

                <div class="separator my-4"></div>
                <div class="fw-bold mb-3">Keadaan Calon Peserta</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input" type="checkbox" name="sakit_kuning" value="1" id="sk" @checked(old('sakit_kuning', $acceptor->sakit_kuning))>
                            <label class="form-check-label ms-3" for="sk">Sakit Kuning</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input" type="checkbox" name="perdarahan_per_vaginam" value="1" id="ppv" @checked(old('perdarahan_per_vaginam', $acceptor->perdarahan_per_vaginam))>
                            <label class="form-check-label ms-3" for="ppv">Perdarahan Pervaginam</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input" type="checkbox" name="tumor_payudara" value="1" id="tp" @checked(old('tumor_payudara', $acceptor->tumor_payudara))>
                            <label class="form-check-label ms-3" for="tp">Tumor Payudara</label>
                        </div>
                    </div>
                </div>

                <div class="separator my-4"></div>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label fs-7">Keluhan</label>
                        <textarea name="keluhan" rows="2" class="form-control form-control-solid">{{ old('keluhan', $acceptor->keluhan) }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fs-7">Fluoralbus (Keputihan)</label>
                        <div class="d-flex flex-wrap gap-4">
                            @foreach(['fluoralbus_gatal'=>'Gatal','fluoralbus_seperti_susu'=>'Seperti Susu','fluoralbus_busa'=>'Busa','fluoralbus_cair'=>'Cair'] as $f=>$label)
                                <div class="form-check form-check-custom">
                                    <input class="form-check-input" type="checkbox" name="{{ $f }}" value="1" id="f_{{ $f }}" @checked(old($f, $acceptor->{$f}))>
                                    <label class="form-check-label ms-2" for="f_{{ $f }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SECTION C — IUD specific (toggle by alat) ===== --}}
        <div class="card mb-5 d-none" id="iud_section">
            <div class="card-header"><h3 class="card-title">C. Pemeriksaan Khusus IUD</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-check-custom mb-2">
                            <input class="form-check-input" type="checkbox" name="iud_tanda_radang" value="1" id="iud_tr" @checked(old('iud_tanda_radang', $acceptor->iud_tanda_radang))>
                            <label class="form-check-label ms-3" for="iud_tr">Tanda Radang</label>
                        </div>
                        <div class="form-check form-check-custom mb-2">
                            <input class="form-check-input" type="checkbox" name="iud_tumor" value="1" id="iud_t" @checked(old('iud_tumor', $acceptor->iud_tumor))>
                            <label class="form-check-label ms-3" for="iud_t">Tumor</label>
                        </div>
                        <label class="form-label fs-7 mt-3">Posisi Rahim</label>
                        <select name="iud_posisi_rahim" class="form-select form-select-solid w-200px" data-control="select2" data-allow-clear="true" data-minimum-results-for-search="-1" data-placeholder="—">
                            <option></option>
                            @foreach(['retro'=>'Retro','antefleksi'=>'Antefleksi','normal'=>'Normal'] as $k=>$v)
                                <option value="{{ $k }}" @selected(old('iud_posisi_rahim', $acceptor->iud_posisi_rahim)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">Genetalia Luar/Dalam</label>
                        @foreach(['iud_genetalia_varices'=>'Varices','iud_genetalia_jengger'=>'Jengger','iud_genetalia_condilo'=>'Condilo','iud_genetalia_bartholinitis'=>'Bartholinitis'] as $f=>$label)
                            <div class="form-check form-check-custom mb-2">
                                <input class="form-check-input" type="checkbox" name="{{ $f }}" value="1" id="g_{{ $f }}" @checked(old($f, $acceptor->{$f}))>
                                <label class="form-check-label ms-3" for="g_{{ $f }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Alat Kontrasepsi --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title required">Alat Kontrasepsi</h3></div>
            <div class="card-body">
                <select name="kontrasepsi_id" id="kontrasepsi_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih alat..." required>
                    <option></option>
                    @foreach($kontrasepsi as $k)
                        <option value="{{ $k->id }}" data-code="{{ $k->code }}" @selected((int)old('kontrasepsi_id', $acceptor->kontrasepsi_id) === $k->id)>{{ $k->name }}</option>
                    @endforeach
                </select>
                @error('kontrasepsi_id')<div class="text-danger fs-7 mt-2">{{ $message }}</div>@enderror
                <div class="form-text fs-8">
                    📋 Diambil dari <b>master data</b> <code>tbm_kontrasepsi_methods</code> (KONDOM/PIL/SUNTIK/IUD/IMPLAN). Jika IUD, section pemeriksaan IUD muncul otomatis.
                </div>

                <div class="separator my-4"></div>

                <label class="form-label fs-7">Tanggal Dilayani <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_dilayani" value="{{ old('tanggal_dilayani', optional($acceptor->tanggal_dilayani)->format('Y-m-d') ?? today()->format('Y-m-d')) }}" class="form-control form-control-solid" required>

                <label class="form-label fs-7 mt-3">Tanggal Pesan Kontrol</label>
                <input type="date" name="tanggal_pesan_kontrol" value="{{ old('tanggal_pesan_kontrol', optional($acceptor->tanggal_pesan_kontrol)->format('Y-m-d')) }}" class="form-control form-control-solid">

                <label class="form-label fs-7 mt-3">
                    Tanggal Dilepas
                    <span class="text-muted fs-9">(jika sudah lepas / selesai / ganti metode)</span>
                </label>
                <input type="date" name="tanggal_dilepas" value="{{ old('tanggal_dilepas', optional($acceptor->tanggal_dilepas)->format('Y-m-d')) }}" class="form-control form-control-solid">
                <div class="form-text fs-9">Biasanya untuk IUD/Implan saat dilepas, atau saat ganti ke metode lain. Kosongkan kalau masih aktif.</div>

                @if($isEdit)
                    <label class="form-label fs-7 mt-3">Status</label>
                    <select name="status" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="-1">
                        @foreach($statuses as $code => $s)
                            <option value="{{ $code }}" @selected(old('status', $acceptor->status)===$code)>{{ $s['label'] }}</option>
                        @endforeach
                    </select>

                    <label class="form-label fs-7 mt-3">Alasan Drop / Ganti</label>
                    <textarea name="drop_reason" rows="2" class="form-control form-control-solid">{{ old('drop_reason', $acceptor->drop_reason) }}</textarea>
                @endif
            </div>
        </div>

        {{-- Identitas Suami --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Identitas Suami</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fs-7">Nama Suami</label>
                    <input type="text" name="suami_name" value="{{ old('suami_name', $acceptor->suami_name) }}" class="form-control form-control-solid">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fs-7">Umur</label>
                        <input type="number" name="suami_age" value="{{ old('suami_age', $acceptor->suami_age) }}" class="form-control form-control-solid" min="10" max="120">
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-7">Kawin ke</label>
                        <input type="number" name="suami_kawin_ke" value="{{ old('suami_kawin_ke', $acceptor->suami_kawin_ke) }}" class="form-control form-control-solid" min="1" max="20">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fs-7">Pendidikan</label>
                    <select name="suami_education_id" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—">
                        <option></option>
                        @foreach($educations as $e)
                            <option value="{{ $e->id }}" @selected((int)old('suami_education_id', $acceptor->suami_education_id) === $e->id)>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label fs-7">Pekerjaan</label>
                    <input type="text" name="suami_occupation" value="{{ old('suami_occupation', $acceptor->suami_occupation) }}" class="form-control form-control-solid">
                </div>
            </div>
        </div>

        {{-- Informed Consent: auto-default (consent_signed=true, witness=current admin) di-handle di controller --}}
        <input type="hidden" name="consent_signed" value="1">
        <input type="hidden" name="consent_witness" value="{{ old('consent_witness', $acceptor->consent_witness ?? auth()->user()->full_name) }}">

        {{-- Notes --}}
        <div class="card mb-5">
            <div class="card-body">
                <label class="form-label fs-7">Catatan</label>
                <textarea name="notes" rows="3" class="form-control form-control-solid">{{ old('notes', $acceptor->notes) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('admin.kb.index') }}" class="btn btn-light">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="ki-outline ki-check fs-3"></i> {{ $isEdit ? 'Update' : 'Daftarkan' }}
    </button>
</div>

@push('scripts')
<script>
$(function() {
    // Toggle section IUD berdasar alat dipilih
    function toggleIud() {
        const code = $('#kontrasepsi_id option:selected').data('code');
        $('#iud_section').toggleClass('d-none', code !== 'KTR-IUD');
    }
    $('#kontrasepsi_id').on('change', toggleIud);
    toggleIud();

    // Combine tekanan darah sistol/diastol → hidden field tekanan_darah
    function combineTd() {
        const s = $('#td_sistol').val();
        const d = $('#td_diastol').val();
        $('#tekanan_darah').val(s && d ? s + '/' + d : '');
    }
    $('#td_sistol, #td_diastol').on('input', combineTd);
});
</script>
@endpush
