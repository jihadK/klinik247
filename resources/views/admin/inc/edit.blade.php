@extends('admin.layouts.app')
@section('title', 'Edit Persalinan')
@section('page_title', 'Kelola Persalinan — '.$delivery->no_persalinan)

@section('content')
<form action="{{ route('admin.inc.update', $delivery) }}" method="POST">
    @csrf @method('PUT')
    <input type="hidden" name="pregnancy_id" value="{{ $delivery->pregnancy_id }}">

    {{-- Tabs untuk 4 Kala + Outcome + Terapi --}}
    <ul class="nav nav-tabs nav-line-tabs fs-6 mb-5" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab_kala1">Kala I</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_kala2">Kala II (Bayi)</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_kala3">Kala III (Plasenta)</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_kala4">Kala IV</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_terapi">Terapi</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_outcome">Outcome & Status</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_rujukan">🔄 Siklus Rujukan</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab_penapisan">Penapisan / Masuk</a></li>
    </ul>

    <div class="tab-content">
        {{-- Tab Kala I --}}
        <div class="tab-pane fade show active" id="tab_kala1">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fs-7">Kala I Mulai</label>
                            <input type="datetime-local" name="kala1_mulai_at" value="{{ optional($delivery->kala1_mulai_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7">Kala I Selesai</label>
                            <input type="datetime-local" name="kala1_selesai_at" value="{{ optional($delivery->kala1_selesai_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-12"><label class="form-label fs-7">Keterangan Kala I</label>
                            <textarea name="kala1_keterangan" rows="3" class="form-control form-control-solid">{{ old('kala1_keterangan', $delivery->kala1_keterangan) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Kala II --}}
        <div class="tab-pane fade" id="tab_kala2">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-warning mb-3">👶 Kala II — Bayi Lahir</h4>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fs-7">Kala II Mulai</label>
                            <input type="datetime-local" name="kala2_mulai_at" value="{{ optional($delivery->kala2_mulai_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7 required">Bayi Lahir (Tgl/Jam)</label>
                            <input type="datetime-local" name="bayi_lahir_at" value="{{ optional($delivery->bayi_lahir_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7">Jenis Kelamin</label>
                            <select name="bayi_jenis_kelamin" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                                <option></option>
                                <option value="L" @selected($delivery->bayi_jenis_kelamin === 'L')>Laki-laki</option>
                                <option value="P" @selected($delivery->bayi_jenis_kelamin === 'P')>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-3"><label class="form-label fs-7">BB Bayi (gram)</label>
                            <input type="number" name="bayi_bb_gram" value="{{ $delivery->bayi_bb_gram }}" class="form-control form-control-solid" placeholder="2500-4000 normal">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">PB (cm)</label>
                            <input type="number" step="0.1" name="bayi_pb_cm" value="{{ $delivery->bayi_pb_cm }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">APGAR 1 menit</label>
                            <select name="bayi_apgar_1" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="0-10" data-minimum-results-for-search="-1">
                                <option></option>
                                @for($i=0; $i<=10; $i++)
                                    <option value="{{ $i }}" @selected((int) $delivery->bayi_apgar_1 === $i)>
                                        {{ $i }} — {{ $i >= 7 ? 'Normal' : ($i >= 4 ? 'Asfiksia Ringan-Sedang' : 'Asfiksia Berat') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">APGAR 5 menit</label>
                            <select name="bayi_apgar_5" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="0-10" data-minimum-results-for-search="-1">
                                <option></option>
                                @for($i=0; $i<=10; $i++)
                                    <option value="{{ $i }}" @selected((int) $delivery->bayi_apgar_5 === $i)>
                                        {{ $i }} — {{ $i >= 7 ? 'Normal' : ($i >= 4 ? 'Asfiksia Ringan-Sedang' : 'Asfiksia Berat') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch form-check-custom mt-3">
                                <input class="form-check-input" type="checkbox" name="bayi_lahir_spontan" value="1" id="bls" @checked($delivery->bayi_lahir_spontan)>
                                <label class="form-check-label fw-semibold ms-2" for="bls">Bayi Lahir Spontan</label>
                            </div>
                            <div class="form-check form-switch form-check-custom mt-2">
                                <input class="form-check-input" type="checkbox" name="bayi_lgs_menangis" value="1" id="blm" @checked($delivery->bayi_lgs_menangis)>
                                <label class="form-check-label fw-semibold ms-2" for="blm">Langsung Menangis</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Kala III --}}
        <div class="tab-pane fade" id="tab_kala3">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-info mb-3">🩺 Kala III — Plasenta</h4>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fs-7">Kala III Mulai</label>
                            <input type="datetime-local" name="kala3_mulai_at" value="{{ optional($delivery->kala3_mulai_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7">Plasenta Lahir (Tgl/Jam)</label>
                            <input type="datetime-local" name="plasenta_lahir_at" value="{{ optional($delivery->plasenta_lahir_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                    </div>

                    <div class="row g-2 mt-3">
                        @php $checks = [
                            'plasenta_lahir_spontan' => 'Plasenta Lahir Spontan',
                            'mak_iii_dilakukan'      => 'MAK III Dilakukan',
                            'amniotomi'              => 'Amniotomi',
                            'tfu_sepusat'            => 'TFU Sepusat',
                            'uc_kuat'                => 'UC Kuat',
                            'eksplorasi_dilakukan'   => 'Eksplorasi Dilakukan',
                            'sisa_plasenta'          => 'Sisa Plasenta (+)',
                            'selaput_lengkap'        => 'Selaput Lengkap',
                        ]; @endphp
                        @foreach($checks as $f => $l)
                            <div class="col-md-3">
                                <div class="form-check form-check-custom p-2 border rounded">
                                    <input class="form-check-input" type="checkbox" name="{{ $f }}" value="1" id="c_{{ $f }}" @checked($delivery->{$f})>
                                    <label class="form-check-label ms-2 fs-7" for="c_{{ $f }}">{{ $l }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Kala IV --}}
        <div class="tab-pane fade" id="tab_kala4">
            <div class="card">
                <div class="card-body">
                    <h4 class="text-success mb-3">⏱ Kala IV — Observasi 2 jam</h4>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fs-7">Kala IV Mulai</label>
                            <input type="datetime-local" name="kala4_mulai_at" value="{{ optional($delivery->kala4_mulai_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7">Kala IV Selesai</label>
                            <input type="datetime-local" name="kala4_selesai_at" value="{{ optional($delivery->kala4_selesai_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>

                        <div class="col-md-4"><label class="form-label fs-7">Perineum Laserasi</label>
                            <select name="perineum_laserasi" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                                <option></option>
                                @foreach($laserasiOptions as $k => $v)<option value="{{ $k }}" @selected($delivery->perineum_laserasi === $k)>{{ $v }}</option>@endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch form-check-custom mt-3">
                                <input class="form-check-input" type="checkbox" name="heckting_dilakukan" value="1" id="hd" @checked($delivery->heckting_dilakukan)>
                                <label class="form-check-label fw-semibold ms-2" for="hd">Heckting Dilakukan</label>
                            </div>
                            <div class="form-check form-switch form-check-custom mt-2">
                                <input class="form-check-input" type="checkbox" name="heckting_lidocain" value="1" id="hl" @checked($delivery->heckting_lidocain)>
                                <label class="form-check-label fw-semibold ms-2" for="hl">Pakai Lidocain</label>
                            </div>
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">Perdarahan (ml)</label>
                            <input type="number" name="perdarahan_ml" value="{{ $delivery->perdarahan_ml }}" class="form-control form-control-solid" placeholder="< 500 normal">
                        </div>
                        <div class="col-md-12"><label class="form-label fs-7">Keluhan Kala IV</label>
                            <textarea name="kala4_keluhan" rows="2" class="form-control form-control-solid">{{ old('kala4_keluhan', $delivery->kala4_keluhan) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Terapi --}}
        <div class="tab-pane fade" id="tab_terapi">
            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title text-primary">💊 Terapi untuk Ibu</h4></div>
                <div class="card-body">
                    <div class="row g-2">
                        @php $iboTerapi = [
                            'terapi_amoxicillin' => 'Amoxicillin (Amok) — 3x1',
                            'terapi_asam_mef'    => 'Asam Mefenamat — 3x1',
                            'terapi_fe'          => 'Fe (Zat Besi) — 1x1',
                            'terapi_metergin'    => 'Metergin — 3x1',
                        ]; @endphp
                        @foreach($iboTerapi as $f => $l)
                            <div class="col-md-3"><div class="form-check form-check-custom p-2 border rounded">
                                <input class="form-check-input" type="checkbox" name="{{ $f }}" value="1" id="t_{{ $f }}" @checked($delivery->{$f})>
                                <label class="form-check-label ms-2 fs-7" for="t_{{ $f }}">{{ $l }}</label>
                            </div></div>
                        @endforeach
                        <div class="col-md-6"><label class="form-label fs-7">Vit A Dose 1 (saat persalinan)</label>
                            <input type="datetime-local" name="terapi_vita_dose1_at" value="{{ optional($delivery->terapi_vita_dose1_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Vit A Dose 2 (24 jam setelah dose 1)</label>
                            <input type="datetime-local" name="terapi_vita_dose2_at" value="{{ optional($delivery->terapi_vita_dose2_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-12"><label class="form-label fs-7">Dosis Notes</label>
                            <textarea name="terapi_ibu_dosis_notes" rows="2" class="form-control form-control-solid" placeholder="Mis. Amox 3x500mg/hari × 5 hari">{{ $delivery->terapi_ibu_dosis_notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title text-warning">👶 Terapi untuk Bayi</h4></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-check-custom p-2 border rounded">
                                <input class="form-check-input" type="checkbox" name="bayi_injeksi_neo_k" value="1" id="bnk" @checked($delivery->bayi_injeksi_neo_k)>
                                <label class="form-check-label ms-2 fs-7" for="bnk">Injeksi Neo K (Vit K1)</label>
                            </div>
                            <input type="datetime-local" name="bayi_neo_k_at" value="{{ optional($delivery->bayi_neo_k_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-sm form-control-solid mt-1">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-check-custom p-2 border rounded">
                                <input class="form-check-input" type="checkbox" name="bayi_salep_mata" value="1" id="bsm" @checked($delivery->bayi_salep_mata)>
                                <label class="form-check-label ms-2 fs-7" for="bsm">Salep Mata</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-check-custom p-2 border rounded">
                                <input class="form-check-input" type="checkbox" name="bayi_imunisasi_hb0" value="1" id="bhb0" @checked($delivery->bayi_imunisasi_hb0)>
                                <label class="form-check-label ms-2 fs-7" for="bhb0">Imunisasi HB-0</label>
                            </div>
                            <input type="datetime-local" name="bayi_hb0_at" value="{{ optional($delivery->bayi_hb0_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-sm form-control-solid mt-1">
                            <input type="text" name="bayi_hb0_no_batch" value="{{ $delivery->bayi_hb0_no_batch }}" placeholder="No. Batch" class="form-control form-control-sm form-control-solid mt-1">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Outcome --}}
        <div class="tab-pane fade" id="tab_outcome">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fs-7">Kondisi Ibu</label>
                            <select name="ibu_kondisi" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                                <option></option>
                                @foreach($ibuKondisiOptions as $k => $v)<option value="{{ $k }}" @selected($delivery->ibu_kondisi === $k)>{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Kondisi Bayi</label>
                            <select name="bayi_kondisi" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                                <option></option>
                                @foreach($bayiKondisiOptions as $k => $v)<option value="{{ $k }}" @selected($delivery->bayi_kondisi === $k)>{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-12"><label class="form-label fs-7 required">Status Persalinan</label>
                            <select name="status" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="-1">
                                @foreach($statuses as $code => $s)<option value="{{ $code }}" @selected($delivery->status === $code)>{{ $s['label'] }}</option>@endforeach
                            </select>
                            <div class="form-text fs-9">Status "Selesai" akan otomatis update kehamilan jadi "Partus". Status "Rujuk" → "Dirujuk".</div>
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Rujukan Ke (kalau dirujuk)</label>
                            <input type="text" name="rujukan_ke" value="{{ $delivery->rujukan_ke }}" class="form-control form-control-solid" placeholder="Mis. RSUD Lamongan">
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Alasan Rujukan</label>
                            <input type="text" name="rujukan_alasan" value="{{ $delivery->rujukan_alasan }}" class="form-control form-control-solid">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ Tab Siklus Rujukan (4 stage) ============ --}}
        <div class="tab-pane fade" id="tab_rujukan">
            <div class="alert alert-info py-2 fs-7 mb-3">
                <i class="ki-outline ki-information-5 fs-3 me-1"></i>
                <b>Siklus Rujukan Utuh:</b> Pra-Rujuk → Kirim → Diterima RS → Surat Balasan → Selesai.
                Set status saat perpindahan tahap.
            </div>

            {{-- Status Siklus Rujukan --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <label class="form-label fs-7 fw-bold">Status Siklus Rujukan</label>
                    <select name="rujuk_siklus_status" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="-1">
                        @foreach(\App\Models\Delivery::rujukSiklusStatuses() as $code => $s)
                            <option value="{{ $code }}" @selected($delivery->rujuk_siklus_status === $code)>{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ===== 1. PENGIRIMAN ===== --}}
            <div class="card mb-3 border-start border-3 border-info">
                <div class="card-header bg-light-info py-2">
                    <h5 class="text-info mb-0"><i class="ki-outline ki-arrow-right fs-3 me-1"></i> 1. Pengiriman dari PMB</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fs-7">Tgl/Jam Kirim</label>
                            <input type="datetime-local" name="rujuk_dikirim_at" value="{{ optional($delivery->rujuk_dikirim_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Mode Transport</label>
                            <select name="rujuk_transport" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                                <option></option>
                                @foreach(\App\Models\Delivery::transportOptions() as $k => $v)
                                    <option value="{{ $k }}" @selected($delivery->rujuk_transport === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Pendamping</label>
                            <input type="text" name="rujuk_pendamping" value="{{ $delivery->rujuk_pendamping }}" placeholder="Suami/Keluarga/Bidan" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Kontak RS (telp)</label>
                            <input type="text" name="rujuk_kontak_rs" value="{{ $delivery->rujuk_kontak_rs }}" placeholder="Telp IGD/maternitas" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-12"><label class="form-label fs-7">Yang Dibawa</label>
                            <textarea name="rujuk_bawa" rows="2" class="form-control form-control-solid" placeholder="Mis. RM, surat rujukan, sample darah, obat, KTP, BPJS">{{ $delivery->rujuk_bawa }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 2. PENERIMAAN DI RS ===== --}}
            <div class="card mb-3 border-start border-3 border-primary">
                <div class="card-header bg-light-primary py-2">
                    <h5 class="text-primary mb-0"><i class="ki-outline ki-check fs-3 me-1"></i> 2. Penerimaan di RS</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fs-7">Tgl/Jam Diterima RS</label>
                            <input type="datetime-local" name="rujuk_diterima_at" value="{{ optional($delivery->rujuk_diterima_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Diterima Oleh (nama petugas)</label>
                            <input type="text" name="rujuk_diterima_by" value="{{ $delivery->rujuk_diterima_by }}" placeholder="Dr. ABC / Bidan XYZ" class="form-control form-control-solid">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 3. SURAT BALIK (Counter-referral) ===== --}}
            <div class="card mb-3 border-start border-3 border-warning">
                <div class="card-header bg-light-warning py-2">
                    <h5 class="text-warning mb-0"><i class="ki-outline ki-message-text fs-3 me-1"></i> 3. Surat Balasan dari RS</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label fs-7">No. Surat Balik</label>
                            <input type="text" name="rujuk_balik_no" value="{{ $delivery->rujuk_balik_no }}" placeholder="Mis. 123/RSU/2026" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7">Tgl Diterima Surat Balik</label>
                            <input type="datetime-local" name="rujuk_balik_diterima_at" value="{{ optional($delivery->rujuk_balik_diterima_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-4"><label class="form-label fs-7">Dokter RS (sign)</label>
                            <input type="text" name="rujuk_balik_dokter_rs" value="{{ $delivery->rujuk_balik_dokter_rs }}" placeholder="Dr. XXX, Sp.OG" class="form-control form-control-solid">
                        </div>

                        <div class="col-md-6"><label class="form-label fs-7">Diagnosis Final RS</label>
                            <textarea name="rujuk_balik_diagnosis" rows="3" class="form-control form-control-solid" placeholder="Diagnosis utama + sekunder">{{ $delivery->rujuk_balik_diagnosis }}</textarea>
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Tindakan yang Dilakukan RS</label>
                            <textarea name="rujuk_balik_tindakan" rows="3" class="form-control form-control-solid" placeholder="Mis. SC, transfusi, ICU, dll">{{ $delivery->rujuk_balik_tindakan }}</textarea>
                        </div>

                        <div class="col-md-6"><label class="form-label fs-7">Outcome Ibu</label>
                            <textarea name="rujuk_balik_outcome_ibu" rows="2" class="form-control form-control-solid" placeholder="Mis. Pulang sehat 3 hari pasca SC">{{ $delivery->rujuk_balik_outcome_ibu }}</textarea>
                        </div>
                        <div class="col-md-6"><label class="form-label fs-7">Outcome Bayi</label>
                            <textarea name="rujuk_balik_outcome_bayi" rows="2" class="form-control form-control-solid" placeholder="Mis. Hidup sehat, BB 3200g, langsung ASI">{{ $delivery->rujuk_balik_outcome_bayi }}</textarea>
                        </div>

                        <div class="col-md-12"><label class="form-label fs-7">Rekomendasi untuk PMB (follow-up)</label>
                            <textarea name="rujuk_balik_rekomendasi" rows="3" class="form-control form-control-solid" placeholder="Mis. Lanjutkan kunjungan nifas di PMB, kontrol luka SC seminggu, dll">{{ $delivery->rujuk_balik_rekomendasi }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== 4. FOLLOW-UP ===== --}}
            <div class="card mb-3 border-start border-3 border-success">
                <div class="card-header bg-light-success py-2">
                    <h5 class="text-success mb-0"><i class="ki-outline ki-check-circle fs-3 me-1"></i> 4. Follow-up di PMB</h5>
                </div>
                <div class="card-body fs-8">
                    Setelah surat balik diterima dan rekomendasi diikuti, set <b>Status Siklus Rujukan</b> ke <b>"Selesai"</b>.
                    Kunjungan nifas (KF1-KF4) dan neonatus (KN1-KN3) akan dicatat di modul <b>PNC</b> (Phase 1.6).
                </div>
            </div>
        </div>

        {{-- Tab Penapisan / Masuk (re-edit) --}}
        <div class="tab-pane fade" id="tab_penapisan">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info fs-8">Edit ulang penapisan + data masuk PMB. Skor akan recalculate otomatis saat save.</div>

                    <h4 class="text-danger mb-3 mt-3">A. Penapisan 18 Item</h4>
                    <div class="row g-2 mb-4">
                        @foreach($penapisan as $field => $label)
                            <div class="col-md-6">
                                <div class="form-check form-check-custom p-2 border rounded">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="ep_{{ $field }}" @checked($delivery->{$field})>
                                    <label class="form-check-label ms-2 fs-8" for="ep_{{ $field }}">{{ $loop->iteration }}. {{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fs-7">Keputusan Penapisan</label>
                            <select name="penapisan_keputusan" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="-1">
                                @foreach($keputusanOptions as $code => $label)<option value="{{ $code }}" @selected($delivery->penapisan_keputusan === $code)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <h4 class="text-primary mb-3 mt-4">B. Pemeriksaan Masuk PMB</h4>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label fs-7">Tgl/Jam Masuk</label>
                            <input type="datetime-local" name="masuk_at" value="{{ optional($delivery->masuk_at)->format('Y-m-d\TH:i') }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">TD</label>
                            <input type="text" name="masuk_ttv_td" value="{{ $delivery->masuk_ttv_td }}" placeholder="120/80" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-2"><label class="form-label fs-7">Nadi</label>
                            <input type="number" name="masuk_ttv_nadi" value="{{ $delivery->masuk_ttv_nadi }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-2"><label class="form-label fs-7">Suhu</label>
                            <input type="number" step="0.1" name="masuk_ttv_suhu" value="{{ $delivery->masuk_ttv_suhu }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-2"><label class="form-label fs-7">RR</label>
                            <input type="number" name="masuk_ttv_rr" value="{{ $delivery->masuk_ttv_rr }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">DJJ</label>
                            <input type="number" name="masuk_djj" value="{{ $delivery->masuk_djj }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">His/10'</label>
                            <input type="number" name="masuk_his_per_10" value="{{ $delivery->masuk_his_per_10 }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">VT Pembukaan</label>
                            <input type="number" step="0.5" name="masuk_vt_pembukaan" value="{{ $delivery->masuk_vt_pembukaan }}" class="form-control form-control-solid">
                        </div>
                        <div class="col-md-3"><label class="form-label fs-7">Ketuban</label>
                            <select name="masuk_ketuban" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—">
                                <option></option>
                                @foreach($ketubanOptions as $k => $v)<option value="{{ $k }}" @selected($delivery->masuk_ketuban === $k)>{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-12"><label class="form-label fs-7">Keluhan Masuk</label>
                            <textarea name="masuk_keluhan" rows="2" class="form-control form-control-solid">{{ $delivery->masuk_keluhan }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ RINGKASAN / KESIMPULAN PERSALINAN (real-time) ============ --}}
    <div class="card mt-5 border border-2" style="background: linear-gradient(135deg,#f0fdf4 0%,#fefce8 100%); border-color:#22c55e !important;">
        <div class="card-header py-3 border-0 d-flex justify-content-between align-items-center" style="background:transparent;">
            <h3 class="card-title text-success mb-0">
                <i class="ki-outline ki-document-1 fs-2 me-1"></i>
                Ringkasan Persalinan & Rekomendasi
            </h3>
            <span class="badge badge-light-success fs-9" id="incSummaryBadge">Live</span>
        </div>
        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
            <div id="incSummary"></div>
        </div>
        <style>
            #incSummary::-webkit-scrollbar { width: 6px; }
            #incSummary::-webkit-scrollbar-thumb { background: rgba(34,197,94,.4); border-radius: 3px; }
        </style>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('admin.inc.show', $delivery) }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="ki-outline ki-check fs-3"></i> Update Persalinan</button>
    </div>
</form>

@push('scripts')
<script>
$(function() {
    // Data awal dari $delivery (PHP)
    const INITIAL = {
        no: @json($delivery->no_persalinan),
        skor: {{ $delivery->penapisan_skor ?? 0 }},
        keputusan: @json($delivery->penapisan_keputusan),
        ibu: @json($delivery->patient?->name),
        bayi_jk_initial: @json($delivery->bayi_jenis_kelamin),
        bayi_bb_initial: {{ $delivery->bayi_bb_gram ?? 'null' }},
        bayi_pb_initial: {{ $delivery->bayi_pb_cm ?? 'null' }},
    };

    function getVal(sel) { return $(sel).val() || null; }
    function getNum(sel) { const v = parseFloat($(sel).val()); return isNaN(v) ? null : v; }
    function getBool(sel) { return $(sel).is(':checked'); }

    function buildSummary() {
        // Re-count penapisan
        let skor = 0;
        $('input[name^="p_"][type=checkbox]').each(function() {
            if (this.checked) skor++;
        });

        // Kala II — bayi
        const bayiJk = getVal('select[name=bayi_jenis_kelamin]') || INITIAL.bayi_jk_initial;
        const bayiBb = getNum('input[name=bayi_bb_gram]') ?? INITIAL.bayi_bb_initial;
        const bayiPb = getNum('input[name=bayi_pb_cm]') ?? INITIAL.bayi_pb_initial;
        const apgar1 = getNum('select[name=bayi_apgar_1]');
        const apgar5 = getNum('select[name=bayi_apgar_5]');
        const lgsMenangis = getBool('input[name=bayi_lgs_menangis]');

        // Kala IV
        const perdarahan = getNum('input[name=perdarahan_ml]');
        const laserasi   = getVal('select[name=perineum_laserasi]');

        // Outcome
        const status     = getVal('select[name=status]');
        const ibuKondisi = getVal('select[name=ibu_kondisi]');
        const bayiKondisi= getVal('select[name=bayi_kondisi]');

        const items = [];

        // Header summary
        items.push({type:'header', html: `<div class="fw-bolder fs-4 mb-2">📋 ${INITIAL.no} — Ibu ${INITIAL.ibu || '-'}</div>`});

        // Penapisan summary
        if (skor > 0) {
            items.push({type:'warning', icon:'ki-shield-cross',
                title:`⚠ Penapisan: ${skor} dari 18 faktor risiko terdeteksi`,
                detail:'Persalinan dengan risiko tinggi. Pastikan keputusan rujuk sudah dipertimbangkan.'});
        } else {
            items.push({type:'success', icon:'ki-shield-tick',
                title:'✅ Penapisan: Tidak ada faktor risiko',
                detail:'Persalinan boleh dilakukan di PMB dengan supervisi standar.'});
        }

        // Bayi info
        if (bayiBb || apgar1 !== null) {
            const bbCat = bayiBb ? (bayiBb < 2500 ? 'BBLR' : (bayiBb > 4000 ? 'Makrosomia' : 'Normal')) : '-';
            const apgarCat = apgar1 !== null ? (apgar1 >= 7 ? 'Vigorous' : (apgar1 >= 4 ? 'Asfiksia Ringan-Sedang' : 'Asfiksia Berat')) : '-';
            items.push({type:'info', icon:'ki-baby',
                title:`👶 Bayi Lahir: ${bayiJk === 'L' ? 'Laki-laki' : (bayiJk === 'P' ? 'Perempuan' : '-')} · BB ${bayiBb || '-'} gr (${bbCat}) · APGAR ${apgar1 ?? '-'}/${apgar5 ?? '-'} (${apgarCat})`,
                detail: lgsMenangis ? '✓ Bayi langsung menangis (vitalitas baik)' : 'Bayi belum/tidak langsung menangis — perlu resusitasi'});

            // Warning APGAR rendah
            if (apgar1 !== null && apgar1 < 7) {
                items.push({type:'danger', icon:'ki-pulse-stop',
                    title:`🚨 APGAR Menit 1: ${apgar1} — Asfiksia ${apgar1 < 4 ? 'BERAT' : 'Ringan-Sedang'}`,
                    detail:'Resusitasi neonatus segera (ventilasi tekanan positif, kompresi dada bila perlu). Pertimbangkan rujuk ke perinatologi.'});
            }
            if (apgar5 !== null && apgar5 < 7) {
                items.push({type:'danger', icon:'ki-pulse-stop',
                    title:`🚨 APGAR Menit 5: ${apgar5} — masih rendah`,
                    detail:'Lanjutkan resusitasi, monitor saturasi O2. Rujuk untuk observasi NICU.'});
            }

            // BBLR/Makrosomia
            if (bayiBb && bayiBb < 2500) {
                items.push({type:'warning', icon:'ki-graph-down',
                    title:`⚠ BBLR (Berat Bayi Lahir Rendah) — ${bayiBb} gr`,
                    detail:'Risiko hipotermia, hipoglikemia. Tindakan: PMK (Perawatan Metode Kanguru), ASI dini, jaga suhu hangat.'});
            }
            if (bayiBb && bayiBb > 4000) {
                items.push({type:'warning', icon:'ki-graph-up',
                    title:`⚠ Makrosomia — ${bayiBb} gr`,
                    detail:'Risiko hipoglikemia, cedera kelahiran. Cek gula darah bayi, observasi shoulder dystocia.'});
            }
        }

        // Perdarahan analysis
        if (perdarahan !== null) {
            if (perdarahan >= 500) {
                items.push({type:'danger', icon:'ki-drop',
                    title:`🚨 Perdarahan Postpartum — ${perdarahan} ml`,
                    detail:'PPH! Manajemen aktif: kompresi bimanual, oksitosin/metergin, transfusi jika perlu. RUJUK kalau tidak tertangani.'});
            } else if (perdarahan >= 300) {
                items.push({type:'warning', icon:'ki-drop',
                    title:`⚠ Perdarahan ${perdarahan} ml — Borderline`,
                    detail:'Pantau ketat 2 jam pertama Kala IV. Cek kontraksi uterus, masase fundus, kandung kemih kosong.'});
            } else {
                items.push({type:'success', icon:'ki-check',
                    title:`✅ Perdarahan ${perdarahan} ml — Normal`,
                    detail:'< 500 ml = aman. Lanjut observasi Kala IV.'});
            }
        }

        // Laserasi analysis
        if (laserasi && laserasi !== 'none') {
            const laserasiInfo = {
                'derajat_1': {sev:'success', detail:'Derajat I: laserasi kulit & mukosa. Heckting opsional dengan teknik continuous.'},
                'derajat_2': {sev:'info', detail:'Derajat II: laserasi sampai otot perineum. Wajib heckting layered.'},
                'derajat_3': {sev:'warning', detail:'Derajat III: laserasi sampai sfingter ani. Perlu jahit khusus oleh dokter SpOG — pertimbangkan rujuk.'},
                'derajat_4': {sev:'danger', detail:'Derajat IV: laserasi sampai mukosa rektum. RUJUK SEGERA ke SpOG untuk repair.'},
            };
            const li = laserasiInfo[laserasi];
            if (li) {
                items.push({type:li.sev, icon:'ki-scissors',
                    title:`Perineum Laserasi: ${laserasi.replace('_',' ').toUpperCase()}`,
                    detail:li.detail});
            }
        }

        // Status final
        if (status === 'selesai') {
            items.push({type:'success', icon:'ki-check-circle',
                title:'✅ Status: Selesai',
                detail:'Saat di-Update, kehamilan otomatis berubah ke status "Partus" dan masuk fase nifas.'});
        } else if (status === 'rujuk') {
            items.push({type:'danger', icon:'ki-arrow-right-square',
                title:'🚨 Status: Dirujuk',
                detail:'Pasien akan ditandai dirujuk. Pastikan Surat Rujukan sudah dicetak & dibawa keluarga.'});
        }

        // Kondisi ibu+bayi
        if (ibuKondisi || bayiKondisi) {
            items.push({type:'info', icon:'ki-people',
                title:`Outcome: Ibu ${ibuKondisi || '-'} · Bayi ${bayiKondisi || '-'}`,
                detail:'Akan masuk pelaporan PWS Ibu (Kemenkes).'});
        }

        // Render
        const colorMap = { success:'success', warning:'warning', danger:'danger', info:'info', header:'' };
        let html = '';
        items.forEach(it => {
            if (it.type === 'header') {
                html += it.html;
            } else {
                html += `<div class="border-start border-${colorMap[it.type]} border-3 ps-3 mb-2">
                    <div class="fw-bold text-${colorMap[it.type]} fs-7"><i class="ki-outline ${it.icon} fs-3 me-1"></i>${it.title}</div>
                    <div class="fs-8 text-muted mt-1">${it.detail}</div>
                </div>`;
            }
        });

        $('#incSummary').html(html);
        $('#incSummaryBadge').text(items.length + ' insight');
    }

    // Re-evaluate setiap field klinis berubah
    $('input[type=checkbox], select, input[type=number], input[type=text]').on('input change', () => setTimeout(buildSummary, 100));
    setTimeout(buildSummary, 300);
});
</script>
@endpush
@endsection

@push('scripts')<x-sweet-flash />@endpush
