@php $isEdit = $isEdit ?? false; @endphp

<input type="hidden" name="patient_id" value="{{ $patient?->id }}">
<input type="hidden" name="patient_visit_id" value="{{ $visit?->id ?? $pregnancy->patient_visit_id }}">

<div class="row">
    <div class="col-md-8">
        {{-- Pasien Header --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Ibu Hamil</h3></div>
            <div class="card-body">
                @if($patient)
                    <div class="d-flex align-items-center gap-4 p-3 bg-light-success rounded">
                        <span class="symbol symbol-50px"><span class="symbol-label bg-success text-white fs-2 fw-bold">{{ mb_substr($patient->name, 0, 1) }}</span></span>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5">{{ $patient->name }}</div>
                            <div class="text-muted fs-7">
                                <span class="badge badge-light-primary">{{ $patient->no_rm }}</span> · {{ $patient->gender_label }} · {{ $patient->age }}
                                @if($patient->phone) · 📞 {{ $patient->phone }} @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">Tidak ada data pasien. Akses via Visit kategori I atau pilih pasien dulu.</div>
                @endif
            </div>
        </div>

        {{-- Section B: Riwayat Obstetri G P A --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">B. Riwayat Obstetri (GPA)</h3></div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fs-7 required">G (Gravida)</label>
                        <input type="number" name="gravida" value="{{ old('gravida', $pregnancy->gravida ?? 1) }}"
                               min="1" max="30" class="form-control form-control-solid" required>
                        <div class="form-text fs-9">Jumlah kehamilan (termasuk ini)</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 required">P (Partus)</label>
                        <input type="number" name="partus" value="{{ old('partus', $pregnancy->partus ?? 0) }}"
                               min="0" max="30" class="form-control form-control-solid" required>
                        <div class="form-text fs-9">Jumlah lahir hidup/mati sebelumnya</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 required">A (Abortus)</label>
                        <input type="number" name="abortus" value="{{ old('abortus', $pregnancy->abortus ?? 0) }}"
                               min="0" max="30" class="form-control form-control-solid" required>
                        <div class="form-text fs-9">Jumlah keguguran</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Hamil ke-</label>
                        <input type="number" name="hamil_ke" value="{{ old('hamil_ke', $pregnancy->hamil_ke) }}"
                               min="1" max="30" class="form-control form-control-solid">
                    </div>
                </div>

                <div class="separator separator-dashed my-4"></div>
                <div class="d-flex justify-content-between align-items-center mb-3 sticky-top bg-white py-2" style="z-index: 2;">
                    <h4 class="mb-0 fs-6">Tabel Anak Sebelumnya</h4>
                    <button type="button" class="btn btn-sm btn-light-primary" id="btn_add_history">
                        <i class="ki-outline ki-plus fs-4"></i> Tambah Baris
                    </button>
                </div>
                <div class="table-responsive border rounded" style="max-width:100%;">
                    <table class="table table-row-bordered align-middle gs-0 gy-2 mb-0 fs-7" id="tbl_histories" style="min-width: 1280px;">
                        <thead>
                            <tr class="fw-bold text-muted bg-light-primary">
                                <th class="ps-3" style="min-width:80px; white-space:nowrap;">Hamil ke</th>
                                <th style="min-width:90px; white-space:nowrap;">Tahun</th>
                                <th style="min-width:80px; white-space:nowrap;">JK</th>
                                <th style="min-width:170px; white-space:nowrap;">Cara Lahir</th>
                                <th style="min-width:100px; white-space:nowrap;">BB (gr)</th>
                                <th style="min-width:90px; white-space:nowrap;">PB (cm)</th>
                                <th style="min-width:180px; white-space:nowrap;">Tempat Bersalin</th>
                                <th style="min-width:140px; white-space:nowrap;">Penolong</th>
                                <th style="min-width:170px; white-space:nowrap;">Kondisi Anak</th>
                                <th style="min-width:200px; white-space:nowrap;">Komplikasi</th>
                                <th class="text-center pe-3" style="min-width:60px; white-space:nowrap;">×</th>
                            </tr>
                        </thead>
                        <tbody id="histories_rows">
                            @php
                                $histories = old('histories', $pregnancy->histories?->toArray() ?? []);
                            @endphp
                            @foreach($histories as $i => $h)
                                <tr class="history-row">
                                    <td class="ps-3"><input type="number" name="histories[{{ $i }}][hamil_ke]" value="{{ $h['hamil_ke'] ?? '' }}" min="1" class="form-control form-control-sm form-control-solid"></td>
                                    <td><input type="number" name="histories[{{ $i }}][tahun]" value="{{ $h['tahun'] ?? '' }}" placeholder="YYYY" class="form-control form-control-sm form-control-solid"></td>
                                    <td>
                                        <select name="histories[{{ $i }}][jenis_kelamin]" class="form-select form-select-sm form-select-solid">
                                            <option value="">-</option>
                                            <option value="L" @selected(($h['jenis_kelamin'] ?? '') === 'L')>L</option>
                                            <option value="P" @selected(($h['jenis_kelamin'] ?? '') === 'P')>P</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="histories[{{ $i }}][cara_lahir]" class="form-select form-select-sm form-select-solid">
                                            <option value="">-</option>
                                            @foreach($caraLahirOptions as $k => $v)
                                                <option value="{{ $k }}" @selected(($h['cara_lahir'] ?? '') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="histories[{{ $i }}][bb_lahir_gram]" value="{{ $h['bb_lahir_gram'] ?? '' }}" placeholder="gram" class="form-control form-control-sm form-control-solid"></td>
                                    <td><input type="number" step="0.1" name="histories[{{ $i }}][pb_lahir_cm]" value="{{ $h['pb_lahir_cm'] ?? '' }}" placeholder="cm" class="form-control form-control-sm form-control-solid"></td>
                                    <td>
                                        <select name="histories[{{ $i }}][tempat_bersalin]" class="form-select form-select-sm form-select-solid">
                                            <option value="">-</option>
                                            @foreach($tempatBersalinOptions as $k => $v)
                                                <option value="{{ $k }}" @selected(($h['tempat_bersalin'] ?? '') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="histories[{{ $i }}][penolong]" class="form-select form-select-sm form-select-solid">
                                            <option value="">-</option>
                                            @foreach($penolongOptions as $k => $v)
                                                <option value="{{ $k }}" @selected(($h['penolong'] ?? '') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="histories[{{ $i }}][kondisi_anak]" class="form-select form-select-sm form-select-solid">
                                            <option value="">-</option>
                                            @foreach($kondisiAnakOptions as $k => $v)
                                                <option value="{{ $k }}" @selected(($h['kondisi_anak'] ?? '') === $k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="histories[{{ $i }}][komplikasi]" value="{{ $h['komplikasi'] ?? '' }}" placeholder="(jika ada)" class="form-control form-control-sm form-control-solid"></td>
                                    <td class="text-center pe-3"><button type="button" class="btn btn-sm btn-icon btn-light-danger btn-del-row" title="Hapus baris"><i class="ki-outline ki-cross fs-3"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="form-text fs-8">💡 Isi tabel anak sebelumnya untuk membantu deteksi risiko kehamilan.</div>
            </div>
        </div>

        {{-- Section C: Pemeriksaan K1 --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">C. Pemeriksaan K1 (Kunjungan Pertama)</h3></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fs-7 required">Tanggal K1</label>
                        <input type="date" name="tanggal_k1" value="{{ old('tanggal_k1', optional($pregnancy->tanggal_k1)->format('Y-m-d') ?? today()->format('Y-m-d')) }}" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">HPHT</label>
                        <input type="date" name="hpht" id="hpht" value="{{ old('hpht', optional($pregnancy->hpht)->format('Y-m-d')) }}" class="form-control form-control-solid">
                        <div class="form-text fs-9">Hari Pertama Haid Terakhir → HPL auto-calc</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7">HPL</label>
                        <input type="date" name="hpl" id="hpl" value="{{ old('hpl', optional($pregnancy->hpl)->format('Y-m-d')) }}" class="form-control form-control-solid">
                        <div class="form-text fs-9" id="uk_info">Hari Perkiraan Lahir (HPHT+280 hari)</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="tinggi_badan_cm" id="tb" value="{{ old('tinggi_badan_cm', $pregnancy->tinggi_badan_cm) }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">BB Awal (kg)</label>
                        <input type="number" step="0.1" name="berat_badan_awal" id="bb" value="{{ old('berat_badan_awal', $pregnancy->berat_badan_awal) }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">LILA (cm)</label>
                        <input type="number" step="0.1" name="lila_cm" value="{{ old('lila_cm', $pregnancy->lila_cm) }}" class="form-control form-control-solid">
                        <div class="form-text fs-9">LILA < 23.5 = KEK</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">IMT</label>
                        <input type="number" step="0.1" name="imt" id="imt" value="{{ old('imt', $pregnancy->imt) }}" class="form-control form-control-solid">
                        <div class="form-text fs-9" id="imt_info">Auto-calc dari TB & BB</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7">Tekanan Darah K1</label>
                        <div class="input-group">
                            <input type="number" id="vs_sistol" min="50" max="300" class="form-control form-control-solid text-center" placeholder="Sistol">
                            <span class="input-group-text fw-bold">/</span>
                            <input type="number" id="vs_diastol" min="30" max="200" class="form-control form-control-solid text-center" placeholder="Diastol">
                            <span class="input-group-text">mmHg</span>
                        </div>
                        <input type="hidden" name="vital_sign_td" id="vital_sign_td" value="{{ old('vital_sign_td', $pregnancy->vital_sign_td) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">Rekomendasi Kenaikan BB</label>
                        <input type="text" name="recom_kenaikan_bb" id="recom_kenaikan_bb" value="{{ old('recom_kenaikan_bb', $pregnancy->recom_kenaikan_bb) }}" class="form-control form-control-solid" placeholder="mis. 11.5-16 kg">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fs-7">Riwayat Alergi</label>
                        <input type="text" name="riwayat_alergi" value="{{ old('riwayat_alergi', $pregnancy->riwayat_alergi) }}" class="form-control form-control-solid" placeholder="Obat / makanan / lainnya">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fs-7">Riwayat Penyakit</label>
                        <input type="text" name="riwayat_penyakit" value="{{ old('riwayat_penyakit', $pregnancy->riwayat_penyakit) }}" class="form-control form-control-solid" placeholder="Hipertensi, DM, asma, dll">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fs-7">Keluhan Awal</label>
                        <textarea name="keluhan_awal" rows="2" class="form-control form-control-solid">{{ old('keluhan_awal', $pregnancy->keluhan_awal) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        @if(! $isEdit)
        {{-- ============ PEMERIKSAAN OBSTETRI K1 ============ --}}
        <div class="card mb-5 border border-2 border-primary">
            <div class="card-header bg-light-primary">
                <h3 class="card-title text-primary">
                    <i class="ki-outline ki-pulse fs-2 me-1"></i>
                    Pemeriksaan Obstetri K1 (Janin)
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 fs-8 mb-3">
                    <i class="ki-outline ki-information-5 fs-3 me-1"></i>
                    <b>Opsional</b> — tergantung UK saat K1.
                    TFU palpable mulai UK ~16 mg · DJJ Doppler bisa mulai ~10 mg · Letak relevan mulai UK ~28 mg.
                    Kosongkan kalau belum bisa diperiksa.
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fs-7">TFU (cm)</label>
                        <input type="number" step="0.1" name="tfu_k1" id="tfu_k1"
                               value="{{ old('tfu_k1') }}" class="form-control form-control-solid"
                               placeholder="Tinggi Fundus Uteri">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">DJJ /menit</label>
                        <input type="number" name="djj_k1" min="60" max="220"
                               value="{{ old('djj_k1') }}" class="form-control form-control-solid"
                               placeholder="120-160 normal">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Letak Janin</label>
                        <select name="letak_janin_k1" class="form-select form-select-solid"
                                data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                            <option></option>
                            @foreach(\App\Models\AncVisit::letakOptions() as $k => $v)
                                <option value="{{ $k }}" @selected(old('letak_janin_k1') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">MAP</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="map_k1" id="map_k1"
                                   value="{{ old('map_k1') }}" class="form-control form-control-solid text-center fw-bolder"
                                   placeholder="auto">
                            <span class="input-group-text fs-9">mmHg</span>
                        </div>
                        <div id="map_k1_info" class="form-text fs-9">Auto dari TD K1 di section C</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ TINDAKAN & JADWAL KONTROL K1 (auto-create ANC visit #1) ============ --}}
        <div class="card mb-5 border border-2 border-success">
            <div class="card-header bg-light-success">
                <h3 class="card-title text-success">
                    <i class="ki-outline ki-syringe fs-2 me-1"></i>
                    Tindakan & Jadwal Kontrol K1
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2 fs-8 mb-3">
                    <i class="ki-outline ki-information-5 fs-3 me-1"></i>
                    <b>K1 = Kunjungan ANC Pertama.</b> Data ini akan otomatis tercatat sebagai kunjungan ANC #1 di tabel kunjungan kontrol.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fs-7">Tempat Periksa K1</label>
                        <select name="tempat_periksa_k1" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="0">
                            @foreach(\App\Models\AncVisit::tempatPeriksaOptions() as $k => $v)
                                <option value="{{ $k }}" @selected(old('tempat_periksa_k1', 'klinik') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7">Status TT</label>
                        <select name="status_tt_k1" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—" data-minimum-results-for-search="-1">
                            <option></option>
                            @foreach(\App\Models\AncVisit::statusTtOptions() as $tt)
                                <option value="{{ $tt }}" @selected(old('status_tt_k1') === $tt)>{{ $tt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch form-check-custom pb-2">
                            <input class="form-check-input" type="checkbox" name="pemberian_tt_k1" value="1" id="pemberian_tt_k1" @checked(old('pemberian_tt_k1'))>
                            <label class="form-check-label fw-semibold ms-2 fs-7" for="pemberian_tt_k1">TT Diberi di K1</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7">Tindakan / Terapi Awal</label>
                        <textarea name="tindakan_k1" rows="3" class="form-control form-control-solid"
                                  placeholder="Mis. Fe 1×1, Asam Folat 1×1, Multivitamin">{{ old('tindakan_k1') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7">Penatalaksanaan / Edukasi</label>
                        <textarea name="penatalaksanaan_k1" rows="3" class="form-control form-control-solid"
                                  placeholder="Mis. Edukasi nutrisi ibu hamil, tanda bahaya kehamilan, persiapan persalinan...">{{ old('penatalaksanaan_k1') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 required">Tanggal Kontrol Berikutnya</label>
                        <input type="date" name="tanggal_kontrol_k1" id="tgl_kontrol_k1"
                               value="{{ old('tanggal_kontrol_k1', today()->addDays(28)->format('Y-m-d')) }}"
                               class="form-control form-control-solid" required>
                        <div class="form-text fs-9" id="tgl_kontrol_info">
                            🗓 Trim I-II (UK <28 mg) → +28 hari · Trim III → +14 hari · Late (≥36 mg) → +7 hari
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        {{-- Identitas Suami --}}
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Identitas Suami</h3></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fs-7">Nama Suami</label>
                    <input type="text" name="suami_nama" value="{{ old('suami_nama', $pregnancy->suami_nama) }}" class="form-control form-control-solid">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fs-7">Umur</label>
                        <input type="number" name="suami_umur" value="{{ old('suami_umur', $pregnancy->suami_umur) }}" class="form-control form-control-solid">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fs-7">Pendidikan</label>
                    <select name="suami_pendidikan_id" class="form-select form-select-solid" data-control="select2" data-allow-clear="true" data-placeholder="—">
                        <option></option>
                        @foreach($educations as $e)
                            <option value="{{ $e->id }}" @selected((int)old('suami_pendidikan_id', $pregnancy->suami_pendidikan_id) === $e->id)>{{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label fs-7">Pekerjaan</label>
                    <input type="text" name="suami_pekerjaan" value="{{ old('suami_pekerjaan', $pregnancy->suami_pekerjaan) }}" class="form-control form-control-solid">
                </div>
            </div>
        </div>

        @if($isEdit && isset($statuses))
        <div class="card mb-5">
            <div class="card-header"><h3 class="card-title">Status & Akhir</h3></div>
            <div class="card-body">
                <label class="form-label fs-7">Status</label>
                <select name="status" class="form-select form-select-solid" data-control="select2" data-minimum-results-for-search="-1">
                    @foreach($statuses as $code => $s)
                        <option value="{{ $code }}" @selected(old('status', $pregnancy->status)===$code)>{{ $s['label'] }}</option>
                    @endforeach
                </select>

                <label class="form-label fs-7 mt-3">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($pregnancy->tanggal_selesai)->format('Y-m-d')) }}" class="form-control form-control-solid">

                <label class="form-label fs-7 mt-3">Keterangan Akhir</label>
                <textarea name="keterangan_akhir" rows="2" class="form-control form-control-solid">{{ old('keterangan_akhir', $pregnancy->keterangan_akhir) }}</textarea>
            </div>
        </div>
        @endif

        <div class="card mb-5">
            <div class="card-body">
                <label class="form-label fs-7">Catatan</label>
                <textarea name="notes" rows="3" class="form-control form-control-solid">{{ old('notes', $pregnancy->notes) }}</textarea>
            </div>
        </div>

        {{-- ============ RINGKASAN & REKOMENDASI (real-time risk assessment) ============ --}}
        <div class="card mb-5 border border-2" style="background: linear-gradient(135deg, #f0fdf4 0%, #fefce8 100%); border-color:#22c55e !important;">
            <div class="card-header border-0 pt-4 pb-2 d-flex justify-content-between align-items-center" style="background: transparent;">
                <h3 class="card-title mb-0">
                    <i class="ki-outline ki-information-5 fs-2 text-success me-1"></i>
                    Ringkasan & Rekomendasi
                </h3>
                <span class="badge badge-light-success fs-9" id="riskBadgeCount">0 item</span>
            </div>
            <div class="card-body pt-2">
                {{-- Scroll container: max-height ~520px, scroll otomatis kalau konten lebih --}}
                <div id="riskSummary" style="max-height: 520px; overflow-y: auto; overflow-x: hidden; padding-right: 8px;">
                    <div class="text-muted fs-7 text-center py-5">
                        <i class="ki-outline ki-information fs-2x text-muted opacity-50"></i>
                        <div class="mt-2">Mulai isi data K1 — ringkasan & rekomendasi akan muncul otomatis di sini.</div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Custom scrollbar untuk #riskSummary supaya tidak terlalu tebal */
            #riskSummary::-webkit-scrollbar { width: 6px; }
            #riskSummary::-webkit-scrollbar-track { background: rgba(0,0,0,.03); border-radius: 3px; }
            #riskSummary::-webkit-scrollbar-thumb { background: rgba(34,197,94,.4); border-radius: 3px; }
            #riskSummary::-webkit-scrollbar-thumb:hover { background: rgba(34,197,94,.7); }
        </style>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('admin.anc.index') }}" class="btn btn-light">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="ki-outline ki-check fs-3"></i> {{ $isEdit ? 'Update' : 'Daftarkan K1' }}
    </button>
</div>

@push('scripts')
<script>
$(function() {
    // ===== Auto-calc HPL dari HPHT (HPHT + 280 hari) + UK sekarang =====
    // HPL selalu di-update saat HPHT berubah. User bisa override manual setelah itu,
    // tapi setiap HPHT change akan reset HPL ke perhitungan otomatis.

    function addDays(dateStr, days) {
        const d = new Date(dateStr + 'T00:00:00');
        d.setDate(d.getDate() + days);
        return d.toISOString().slice(0, 10);
    }
    function weeksFromNow(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        const now = new Date(); now.setHours(0,0,0,0);
        const diffDays = Math.round((now - d) / 86400000);
        return Math.round(diffDays / 7 * 10) / 10;  // 1 decimal
    }

    function calcHpl() {
        const hpht = $('#hpht').val();
        if (! hpht) {
            $('#hpl').val('');
            $('#uk_info').text('Hari Perkiraan Lahir (HPHT+280 hari) — auto-calc saat HPHT diisi');
            return;
        }

        // Client-side instant calculation (no wait for AJAX)
        const hplLocal = addDays(hpht, 280);
        const ukLocal  = weeksFromNow(hpht);

        // Update HPL field selalu (auto-update setiap HPHT berubah)
        $('#hpl').val(hplLocal);
        $('#uk_info').html('🤰 UK sekarang: <b>' + ukLocal + ' minggu</b> · HPL: <b>' + hplLocal + '</b>');

        // Backend confirmation (optional double-check via AJAX)
        $.get('{{ route("admin.anc.ajax.calc-hpl") }}', { hpht })
            .done(res => {
                const d = res.data;
                if (! d) return;
                $('#hpl').val(d.hpl);
                $('#uk_info').html('🤰 UK sekarang: <b>' + d.uk_sekarang + ' minggu</b> · HPL: <b>' + d.hpl + '</b>');
            })
            .fail(() => { /* fallback ke client-side calc (sudah di-set di atas) */ });
    }
    $('#hpht').on('change input', calcHpl);
    if ($('#hpht').val()) calcHpl();

    // ===== Auto-suggest Tgl Kontrol K1 berdasar trimester =====
    function suggestTglKontrolK1() {
        const $field = $('#tgl_kontrol_k1');
        if (! $field.length) return;
        const hpht = $('#hpht').val();
        const tanggalK1 = $('input[name=tanggal_k1]').val() || new Date().toISOString().slice(0, 10);
        let intervalDays = 28, label = 'Trim I-II → +28 hari (4 minggu)';

        if (hpht) {
            const days = Math.round((new Date(tanggalK1 + 'T00:00:00') - new Date(hpht + 'T00:00:00')) / 86400000);
            const uk = days / 7;
            if (uk >= 36)      { intervalDays = 7;  label = 'Trim III akhir → +7 hari (1 minggu)'; }
            else if (uk >= 28) { intervalDays = 14; label = 'Trim III → +14 hari (2 minggu)'; }
            else if (uk >= 13) { label = 'Trim II → +28 hari (4 minggu)'; }
            else               { label = 'Trim I → +28 hari (4 minggu)'; }
        }

        const d = new Date(tanggalK1 + 'T00:00:00');
        d.setDate(d.getDate() + intervalDays);
        $field.val(d.toISOString().slice(0, 10));
        $('#tgl_kontrol_info').html('🗓 ' + label);
    }
    $('#hpht, input[name=tanggal_k1]').on('change', suggestTglKontrolK1);
    setTimeout(suggestTglKontrolK1, 300);

    // ===== Auto-calc IMT dari TB & BB + rekomendasi kenaikan BB =====
    function calcImt() {
        const tb = $('#tb').val(), bb = $('#bb').val();
        if (! tb || ! bb) return;
        $.get('{{ route("admin.anc.ajax.calc-imt") }}', { tinggi_badan_cm: tb, berat_badan: bb })
            .done(res => {
                const d = res.data;
                if (! d) return;
                $('#imt').val(d.imt);
                $('#imt_info').html('IMT: <b>' + d.imt + '</b> (' + d.category + ')');
                if (! $('#recom_kenaikan_bb').val()) $('#recom_kenaikan_bb').val(d.recom);
            });
    }
    $('#tb, #bb').on('input', () => setTimeout(calcImt, 200));

    // ===== Tekanan Darah K1 — split sistol/diastol =====
    const existingTd = $('#vital_sign_td').val();
    if (existingTd && existingTd.includes('/')) {
        const parts = existingTd.split('/');
        $('#vs_sistol').val(parts[0].trim());
        $('#vs_diastol').val(parts[1].trim());
    }
    function combineTd() {
        const s = $('#vs_sistol').val(), d = $('#vs_diastol').val();
        $('#vital_sign_td').val(s && d ? s + '/' + d : '');
        calcMapK1();
    }
    $('#vs_sistol, #vs_diastol').on('input', combineTd);

    // ===== Auto-calc MAP K1 dari Sistol + Diastol section C =====
    function calcMapK1() {
        const $field = $('#map_k1');
        const $info  = $('#map_k1_info');
        if (! $field.length) return;
        const s = parseInt($('#vs_sistol').val());
        const d = parseInt($('#vs_diastol').val());
        if (isNaN(s) || isNaN(d)) {
            $field.val('');
            $info.html('Auto dari TD K1 di section C');
            return;
        }
        const map = Math.round(((s + 2 * d) / 3) * 10) / 10;
        $field.val(map);
        if (map < 90)        $info.html('<span class="text-success">✅ <b>Normal</b> (MAP ' + map + ')</span>');
        else if (map < 105)  $info.html('<span class="text-warning">⚠ <b>Curiga preeklampsia</b> (MAP ' + map + ')</span>');
        else                 $info.html('<span class="text-danger">🚨 <b>Preeklampsia berat — RUJUK</b> (MAP ' + map + ')</span>');
    }
    setTimeout(calcMapK1, 300);

    // ===== Tabel Riwayat Anak: tambah/hapus baris =====
    let rowIdx = {{ count($histories ?? []) }};
    const templateRow = `
        <tr class="history-row">
            <td class="ps-3"><input type="number" name="histories[{idx}][hamil_ke]" min="1" class="form-control form-control-sm form-control-solid"></td>
            <td><input type="number" name="histories[{idx}][tahun]" placeholder="YYYY" class="form-control form-control-sm form-control-solid"></td>
            <td>
                <select name="histories[{idx}][jenis_kelamin]" class="form-select form-select-sm form-select-solid">
                    <option value="">-</option><option value="L">L</option><option value="P">P</option>
                </select>
            </td>
            <td>
                <select name="histories[{idx}][cara_lahir]" class="form-select form-select-sm form-select-solid">
                    <option value="">-</option>
                    @foreach($caraLahirOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </td>
            <td><input type="number" name="histories[{idx}][bb_lahir_gram]" placeholder="gram" class="form-control form-control-sm form-control-solid"></td>
            <td><input type="number" step="0.1" name="histories[{idx}][pb_lahir_cm]" placeholder="cm" class="form-control form-control-sm form-control-solid"></td>
            <td>
                <select name="histories[{idx}][tempat_bersalin]" class="form-select form-select-sm form-select-solid">
                    <option value="">-</option>
                    @foreach($tempatBersalinOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </td>
            <td>
                <select name="histories[{idx}][penolong]" class="form-select form-select-sm form-select-solid">
                    <option value="">-</option>
                    @foreach($penolongOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </td>
            <td>
                <select name="histories[{idx}][kondisi_anak]" class="form-select form-select-sm form-select-solid">
                    <option value="">-</option>
                    @foreach($kondisiAnakOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </td>
            <td><input type="text" name="histories[{idx}][komplikasi]" placeholder="(jika ada)" class="form-control form-control-sm form-control-solid"></td>
            <td class="text-center pe-3"><button type="button" class="btn btn-sm btn-icon btn-light-danger btn-del-row" title="Hapus baris"><i class="ki-outline ki-cross fs-3"></i></button></td>
        </tr>
    `;
    $('#btn_add_history').on('click', function() {
        $('#histories_rows').append(templateRow.replace(/\{idx\}/g, rowIdx));
        rowIdx++;
        evaluateRisks();
    });
    $(document).on('click', '.btn-del-row', function() { $(this).closest('tr').remove(); evaluateRisks(); });

    // ===========================================================================
    // 🩺 RISK ASSESSMENT ENGINE — Real-time evaluation berdasar data K1
    // ===========================================================================
    const PATIENT_AGE = {{ $patient && $patient->birth_date ? (int) floor($patient->birth_date->diffInYears(now())) : 'null' }};
    const PATIENT_NAME = @json($patient?->name ?? 'Ibu');

    function getFloat(sel) { const v = parseFloat($(sel).val()); return isNaN(v) ? null : v; }
    function getInt(sel)   { const v = parseInt($(sel).val()); return isNaN(v) ? null : v; }

    function evaluateRisks() {
        const tb       = getFloat('#tb');
        const bb       = getFloat('#bb');
        const lila     = getFloat('input[name=lila_cm]');
        const imt      = getFloat('#imt');
        const sistol   = getInt('#vs_sistol');
        const diastol  = getInt('#vs_diastol');
        const hpht     = $('#hpht').val();
        const gravida  = getInt('input[name=gravida]') || 0;
        const partus   = getInt('input[name=partus]') || 0;
        const abortus  = getInt('input[name=abortus]') || 0;
        const riwAlergi = ($('input[name=riwayat_alergi]').val() || '').trim();
        const riwSakit  = ($('input[name=riwayat_penyakit]').val() || '').trim();

        // Get histories data
        const histories = [];
        let lastDeliveryYear = null;
        let hasSC = false, hasKomplikasi = false;
        $('.history-row').each(function() {
            const $r = $(this);
            const tahun = parseInt($r.find('input[name*="[tahun]"]').val());
            const cara  = $r.find('select[name*="[cara_lahir]"]').val();
            const komp  = ($r.find('input[name*="[komplikasi]"]').val() || '').trim();
            histories.push({ tahun, cara, komp });
            if (tahun && (!lastDeliveryYear || tahun > lastDeliveryYear)) lastDeliveryYear = tahun;
            if (cara === 'sc') hasSC = true;
            if (komp) hasKomplikasi = true;
        });

        // ===== Risk Rules =====
        const risks = [];
        const stats = []; // info informatif (bukan risiko)

        // Stat: UK + Trimester
        if (hpht) {
            const days = Math.round((new Date() - new Date(hpht + 'T00:00:00')) / 86400000);
            const uk = Math.round(days / 7 * 10) / 10;
            const trim = uk < 13 ? 'I' : uk < 28 ? 'II' : 'III';
            stats.push({
                icon: 'ki-heart-circle', color: 'success',
                title: `🤰 Usia Kehamilan: ${uk} minggu (Trimester ${trim})`,
                detail: `Berdasarkan HPHT ${hpht}. HPL: ${$('#hpl').val() || '-'}`
            });
        }

        // Stat: GPA
        if (gravida > 0) {
            stats.push({
                icon: 'ki-tablet-text', color: 'info',
                title: `📊 GPA: G${gravida} P${partus} A${abortus}`,
                detail: `Kehamilan ke-${gravida}` + (partus > 0 ? `, sudah ${partus} kali bersalin` : '') + (abortus > 0 ? `, ${abortus} kali abortus` : '.')
            });
        }

        // Risk 1: KEK (LILA < 23.5)
        if (lila !== null && lila < 23.5) {
            risks.push({
                severity: 'danger',
                icon: 'ki-shield-cross',
                title: `🚨 KEK (Kurang Energi Kronis) — LILA ${lila} cm`,
                risk: 'Risiko: BBLR (Berat Bayi Lahir Rendah), prematur, stunting pada anak, anemia ibu, dan perdarahan persalinan.',
                recom: '✅ Edukasi nutrisi ibu hamil, suplementasi makanan tambahan (PMT), tablet Fe 1×1, target kenaikan BB 12.5-18 kg, pantau LILA tiap kunjungan.'
            });
        }

        // Risk 2: IMT — Underweight
        if (imt !== null && imt < 18.5) {
            risks.push({
                severity: 'warning',
                icon: 'ki-pulse',
                title: `⚠ Underweight — IMT ${imt}`,
                risk: 'Risiko: BBLR, anemia, defisiensi gizi mikro (Fe, folat), persalinan prematur, gangguan tumbuh kembang janin.',
                recom: '✅ Target kenaikan BB 13–18 kg selama hamil. Edukasi gizi seimbang 2300 kcal/hari, suplementasi Fe+asam folat, kontrol BB tiap kunjungan.'
            });
        }

        // Risk 3: IMT — Overweight/Obese
        if (imt !== null && imt >= 25 && imt < 30) {
            risks.push({
                severity: 'warning',
                icon: 'ki-pulse',
                title: `⚠ Overweight — IMT ${imt}`,
                risk: 'Risiko: Diabetes gestasional, preeklampsia, makrosomia (bayi besar), distosia bahu, operasi caesar.',
                recom: '✅ Target kenaikan BB hanya 7–11.5 kg. Diet rendah kalori-tinggi nutrisi, aktivitas fisik aman (jalan kaki 30 mnt/hari), skrining gula darah & TD ketat.'
            });
        }
        if (imt !== null && imt >= 30) {
            risks.push({
                severity: 'danger',
                icon: 'ki-pulse-stop',
                title: `🚨 Obesitas — IMT ${imt}`,
                risk: 'Risiko TINGGI: Diabetes gestasional, preeklampsia berat, makrosomia, distosia bahu, kematian janin dalam rahim, infeksi luka pasca SC.',
                recom: '✅ Konsultasi gizi spesialis. Target kenaikan BB hanya 5–9 kg. Skrining DM via OGTT minggu 24-28. Pantau TD tiap kunjungan. Pertimbangkan rujukan ke RS.'
            });
        }

        // Risk 4: Hipertensi
        if (sistol !== null && diastol !== null) {
            if (sistol >= 160 || diastol >= 110) {
                risks.push({
                    severity: 'danger',
                    icon: 'ki-heart-broken',
                    title: `🚨 Hipertensi Berat — TD ${sistol}/${diastol} mmHg`,
                    risk: 'Risiko TINGGI: Preeklampsia berat, eklampsia, sindrom HELLP, abruptio plasenta, kematian ibu & janin.',
                    recom: '⛔ RUJUK SEGERA ke RS dengan fasilitas SpOG + ICU. Periksa proteinuria, edema, refleks. Tirah baring, MgSO4 jika perlu (per protokol).'
                });
            } else if (sistol >= 140 || diastol >= 90) {
                risks.push({
                    severity: 'warning',
                    icon: 'ki-heart-broken',
                    title: `⚠ Hipertensi — TD ${sistol}/${diastol} mmHg`,
                    risk: 'Risiko: Preeklampsia, IUGR (pertumbuhan janin terhambat), bayi prematur.',
                    recom: '✅ Cek proteinuria, edema, pusing/pandangan kabur. Diet rendah garam, istirahat cukup. Kontrol ulang 1 minggu. Rujuk kalau memburuk.'
                });
            }
        }

        // Risk 5: Usia ibu — 4T (Terlalu Muda/Tua)
        if (PATIENT_AGE !== null) {
            if (PATIENT_AGE < 20) {
                risks.push({
                    severity: 'warning',
                    icon: 'ki-user-tick',
                    title: `⚠ Terlalu Muda — ${PATIENT_AGE} tahun`,
                    risk: 'Risiko 4T: Panggul belum matang → CPD/persalinan macet, anemia, BBLR, preeklampsia, mental tidak siap.',
                    recom: '✅ Edukasi kesehatan reproduksi, dukungan psikososial, suplementasi Fe+kalsium, pertimbangkan rujukan persalinan ke RS.'
                });
            } else if (PATIENT_AGE > 35) {
                risks.push({
                    severity: 'warning',
                    icon: 'ki-user-tick',
                    title: `⚠ Terlalu Tua — ${PATIENT_AGE} tahun`,
                    risk: 'Risiko 4T: Hipertensi, DM gestasional, kelainan kromosom janin, perdarahan, persalinan lama.',
                    recom: '✅ Skrining trisomi (NIPT/USG NT), OGTT, TD ketat. Pertimbangkan rujukan persalinan ke RS dengan SpOG.'
                });
            }
        }

        // Risk 6: Grande Multipara (terlalu sering)
        if (partus >= 4) {
            risks.push({
                severity: 'warning',
                icon: 'ki-graph',
                title: `⚠ Grande Multipara — sudah ${partus} kali bersalin`,
                risk: 'Risiko 4T (Terlalu Sering): Atonia uteri, perdarahan postpartum, plasenta previa, ruptur uteri, anemia kronis.',
                recom: '✅ Konseling KB pasca salin, suplementasi Fe, persiapan persalinan di fasilitas dengan kemampuan transfusi darah.'
            });
        }

        // Risk 7: Jarak Kehamilan Dekat (< 2 tahun)
        if (lastDeliveryYear && (new Date().getFullYear() - lastDeliveryYear) < 2) {
            const jarak = new Date().getFullYear() - lastDeliveryYear;
            risks.push({
                severity: 'warning',
                icon: 'ki-time',
                title: `⚠ Terlalu Dekat — jarak ${jarak} tahun dari kelahiran terakhir`,
                risk: 'Risiko 4T: Anemia, BBLR, prematur, ibu kelelahan, recovery jaringan belum optimal.',
                recom: '✅ Suplementasi Fe+asam folat agresif, ASI eksklusif anak sebelumnya tetap dilanjut, edukasi KB jangka panjang pasca salin.'
            });
        }

        // Risk 8: Riwayat SC
        if (hasSC) {
            risks.push({
                severity: 'warning',
                icon: 'ki-scissors',
                title: '⚠ Riwayat SC (Sectio Caesarea)',
                risk: 'Risiko: Ruptur uteri pada VBAC, plasenta previa/akreta, adhesi.',
                recom: '✅ Pertimbangkan SC elektif (kecuali VBAC ideal). Persalinan WAJIB di RS dengan fasilitas operasi 24/7. USG plasenta detail.'
            });
        }

        // Risk 9: Riwayat Komplikasi
        if (hasKomplikasi) {
            risks.push({
                severity: 'danger',
                icon: 'ki-information-3',
                title: '🚨 Riwayat Komplikasi Kehamilan/Persalinan Sebelumnya',
                risk: 'Risiko: Pola komplikasi cenderung berulang (preeklampsia, perdarahan, prematur).',
                recom: '✅ Lihat detail komplikasi sebelumnya di tabel riwayat. Antisipasi spesifik sesuai jenis komplikasi. Pantau ketat.'
            });
        }

        // Risk 10: TB pendek
        if (tb !== null && tb < 145) {
            risks.push({
                severity: 'warning',
                icon: 'ki-arrow-down',
                title: `⚠ Tinggi Badan Pendek — ${tb} cm`,
                risk: 'Risiko CPD (Cephalopelvic Disproportion): panggul mungkin sempit → persalinan macet.',
                recom: '✅ Antisipasi rujukan persalinan ke RS. USG pelvimetri jika perlu. Konseling kemungkinan SC.'
            });
        }

        // Risk 11: Riwayat penyakit
        if (riwSakit && riwSakit.length > 2) {
            risks.push({
                severity: 'info',
                icon: 'ki-document-down',
                title: `ℹ Riwayat Penyakit — "${riwSakit}"`,
                risk: 'Penyakit kronis ibu dapat mempengaruhi kehamilan (hipertensi → preeklampsia, DM → makrosomia, asma → distres, dll).',
                recom: '✅ Lanjutkan terapi penyakit dasar yang aman untuk kehamilan, konsultasi dokter spesialis terkait, pantau ketat tanda perburukan.'
            });
        }

        // ===== Render =====
        const $sum = $('#riskSummary');
        const $badge = $('#riskBadgeCount');
        let html = '';

        // Update badge counter (jumlah stats + risks) + color sesuai severity
        const dangerN = risks.filter(r => r.severity === 'danger').length;
        const warningN = risks.filter(r => r.severity === 'warning').length;
        const totalItems = stats.length + risks.length;
        $badge.text(totalItems + ' item');
        $badge.removeClass(function(_, c) { return (c.match(/badge-light-\S+/g) || []).join(' '); });
        if (dangerN > 0)      $badge.addClass('badge-light-danger');
        else if (warningN > 0) $badge.addClass('badge-light-warning');
        else                   $badge.addClass('badge-light-success');

        if (stats.length === 0 && risks.length === 0) {
            html = `<div class="text-muted fs-7 text-center py-5">
                        <i class="ki-outline ki-information fs-2x text-muted opacity-50"></i>
                        <div class="mt-2">Mulai isi data K1 — ringkasan & rekomendasi akan muncul otomatis di sini.</div>
                    </div>`;
        } else {
            // Stats banner
            if (stats.length) {
                html += '<div class="mb-3">';
                stats.forEach(s => {
                    html += `<div class="d-flex align-items-start gap-2 mb-2 p-2 rounded bg-light-${s.color}">
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-7">${s.title}</div>
                            <div class="text-muted fs-8">${s.detail}</div>
                        </div>
                    </div>`;
                });
                html += '</div>';
            }

            // Overall status
            const dangerCount = risks.filter(r => r.severity === 'danger').length;
            const warningCount = risks.filter(r => r.severity === 'warning').length;
            const overallColor = dangerCount > 0 ? 'danger' : warningCount > 0 ? 'warning' : 'success';
            const overallLabel = dangerCount > 0
                ? `🚨 Risiko TINGGI — ${dangerCount} faktor kritis, ${warningCount} faktor risiko`
                : warningCount > 0
                    ? `⚠ Risiko Sedang — ${warningCount} faktor risiko terdeteksi`
                    : '✅ Tidak ada faktor risiko terdeteksi';

            html += `<div class="alert alert-${overallColor} d-flex align-items-center gap-2 py-2 px-3 mb-3">
                        <i class="ki-outline ki-shield-tick fs-2 text-${overallColor}"></i>
                        <div class="fw-bold fs-7">${overallLabel}</div>
                    </div>`;

            // Risk cards
            if (risks.length) {
                html += '<div class="text-muted fs-8 fw-semibold text-uppercase mb-2">⚠ Faktor Risiko & Rekomendasi</div>';
                risks.forEach(r => {
                    html += `<div class="border-start border-${r.severity} border-3 ps-3 mb-3">
                        <div class="fw-bold text-${r.severity} fs-7 mb-1">
                            <i class="ki-outline ${r.icon} fs-3 me-1"></i>${r.title}
                        </div>
                        <div class="fs-8 text-muted mb-1"><b>Risiko:</b> ${r.risk}</div>
                        <div class="fs-8 text-success"><b>Rekomendasi:</b> ${r.recom}</div>
                    </div>`;
                });
            } else {
                html += '<div class="text-center text-success py-3 fs-7"><i class="ki-outline ki-check-circle fs-2 me-1"></i> Tidak ada faktor risiko terdeteksi pada data K1.</div>';
            }

            // Footer disclaimer
            html += `<div class="separator separator-dashed my-3"></div>
                <div class="fs-9 text-muted text-italic">
                    <i class="ki-outline ki-information-5 me-1"></i>
                    Ringkasan ini berdasarkan data K1 yang sudah diinputkan. <b>Bukan pengganti penilaian klinis bidan/dokter.</b>
                    Selalu konfirmasi via pemeriksaan langsung & USG.
                </div>`;
        }
        $sum.html(html);
    }

    // Hook semua field yang relevan untuk re-evaluate
    $('#tb, #bb, input[name=lila_cm], #imt, #vs_sistol, #vs_diastol, #hpht, ' +
      'input[name=gravida], input[name=partus], input[name=abortus], ' +
      'input[name=riwayat_alergi], input[name=riwayat_penyakit]').on('input change', () => setTimeout(evaluateRisks, 100));

    // Re-evaluate saat input tabel history berubah
    $(document).on('input change', '.history-row input, .history-row select', () => setTimeout(evaluateRisks, 100));

    // Initial evaluation (untuk edit mode atau old() data)
    setTimeout(evaluateRisks, 500);
});
</script>
@endpush
