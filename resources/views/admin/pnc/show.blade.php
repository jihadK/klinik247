@extends('admin.layouts.app')
@section('title', 'Nifas Ibu')
@section('page_title', 'Detail Nifas — '.$delivery->patient?->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        {{-- Card Identitas Ibu Nifas --}}
        <div class="card mb-5">
            <div class="card-body text-center">
                <h3 class="mb-1">{{ $delivery->patient?->name }}</h3>
                <div class="text-muted fs-7">{{ $delivery->patient?->no_rm }} · {{ $delivery->patient?->age }}</div>
                <div class="my-3 p-3 rounded text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <div class="text-white-50 fs-8 text-uppercase">No. Persalinan</div>
                    <div class="fs-3 fw-bolder font-monospace">{{ $delivery->no_persalinan }}</div>
                </div>
                @if($delivery->bayi_lahir_at)
                    @php $hari = (int) $delivery->bayi_lahir_at->diffInDays(now()); @endphp
                    <div class="text-muted fs-7">👶 Lahir: <b>{{ $delivery->bayi_lahir_at->isoFormat('D MMM YYYY HH:mm') }}</b></div>
                    <div class="text-muted fs-7 mt-1">⏱ Nifas hari ke: <b class="text-warning">{{ $hari }}</b></div>
                @endif

                <a href="{{ route('admin.inc.show', $delivery) }}" class="btn btn-sm btn-light-info w-100 mt-3">
                    <i class="ki-outline ki-pulse fs-3"></i> Lihat Persalinan
                </a>
                <a href="{{ route('admin.pnc.index') }}" class="btn btn-sm btn-light w-100 mt-2">← Daftar Nifas</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- 4 KF Timeline Cards --}}
        @foreach($kfPeriods as $kfNum => $info)
            @php $visit = $visits->get($kfNum); @endphp
            <div class="card mb-4 border border-2 border-{{ $info['color'] }}">
                <div class="card-header bg-light-{{ $info['color'] }} py-3">
                    <h4 class="card-title text-{{ $info['color'] }} mb-0">
                        @if($visit)<i class="ki-outline ki-check-circle fs-3 text-success me-1"></i>@endif
                        {{ $info['label'] }} — <span class="fs-7 text-muted">{{ $info['periode'] }}</span>
                    </h4>
                    <div class="card-toolbar">
                        @if($visit)
                            <span class="badge badge-light-success">✓ {{ $visit->visit_date->isoFormat('D MMM YY') }}</span>
                            @if(auth()->user()->hasPermission('pnc.create'))
                                <button class="btn btn-sm btn-light-warning ms-2 btn-edit-kf"
                                        data-bs-toggle="modal" data-bs-target="#modal_kf_{{ $kfNum }}">
                                    <i class="ki-outline ki-pencil fs-3"></i> Edit
                                </button>
                            @endif
                        @else
                            @if(auth()->user()->hasPermission('pnc.create'))
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_kf_{{ $kfNum }}">
                                    <i class="ki-outline ki-plus fs-3"></i> Catat KF{{ $kfNum }}
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
                @if($visit)
                <div class="card-body">
                    <div class="row g-2 fs-8">
                        <div class="col-md-2"><div class="text-muted">TD</div><b>{{ $visit->ttv_td ?? '-' }}</b></div>
                        <div class="col-md-2"><div class="text-muted">N</div><b>{{ $visit->ttv_nadi ?? '-' }}</b></div>
                        <div class="col-md-2"><div class="text-muted">S</div><b>{{ $visit->ttv_suhu ?? '-' }}°C</b></div>
                        <div class="col-md-2"><div class="text-muted">Kondisi</div><b>{{ ucfirst($visit->kondisi_umum ?? '-') }}</b></div>
                        <div class="col-md-2"><div class="text-muted">Lokhia</div><b>{{ ucfirst($visit->lokhia ?? '-') }}</b></div>
                        <div class="col-md-2"><div class="text-muted">TFU</div><b>{{ $visit->tfu_cm ?? '-' }} cm</b></div>

                        <div class="col-md-3"><div class="text-muted">Jalan Lahir</div><b>{{ str_replace('_',' ',$visit->jalan_lahir ?? '-') }}</b></div>
                        <div class="col-md-3"><div class="text-muted">Kontraksi</div><b>{{ ucfirst($visit->kontraksi ?? '-') }}</b></div>
                        <div class="col-md-3"><div class="text-muted">Payudara/ASI</div><b>{{ ucfirst($visit->payudara ?? '-') }} / {{ ucfirst($visit->asi ?? '-') }}</b></div>
                        <div class="col-md-3"><div class="text-muted">Vit A Dose</div><b>{{ $visit->vit_a_dose ?? 0 }} / 2</b></div>

                        @if($visit->keluhan)
                            <div class="col-md-6"><div class="text-muted">Keluhan</div><b>{{ $visit->keluhan }}</b></div>
                        @endif
                        @if($visit->terapi)
                            <div class="col-md-6"><div class="text-muted">Terapi</div><b>{{ $visit->terapi }}</b></div>
                        @endif

                        @if($visit->nasehat_diberikan && count($visit->nasehat_diberikan) > 0)
                            <div class="col-md-12 mt-2">
                                <div class="text-muted fs-9">📝 NASEHAT DIBERIKAN ({{ count($visit->nasehat_diberikan) }} item):</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($visit->nasehat_diberikan as $key)
                                        <span class="badge badge-light-success fs-9">✓ {{ $nasehatItems[$key] ?? $key }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($visit->tanggal_kembali)
                            <div class="col-md-12 mt-2">
                                <span class="badge badge-light-info">🗓 Kembali: {{ $visit->tanggal_kembali->isoFormat('D MMM YY') }}</span>
                            </div>
                        @endif

                        @if($visit->status && $visit->status !== 'sehat')
                            <div class="col-md-12 mt-2">
                                <div class="alert alert-{{ $visit->status === 'dirujuk' ? 'danger' : 'warning' }} py-2 fs-8 mb-0">
                                    ⚠ <b>Status: {{ ucfirst($visit->status) }}</b>
                                    @if($visit->rujukan_alasan) — {{ $visit->rujukan_alasan }} @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Modal Form KF{{ $kfNum }} --}}
            <div class="modal fade" id="modal_kf_{{ $kfNum }}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <form action="{{ route('admin.pnc.visit.store', $delivery) }}" method="POST" class="modal-content">
                        @csrf
                        <input type="hidden" name="kf_number" value="{{ $kfNum }}">
                        <div class="modal-header bg-light-{{ $info['color'] }}">
                            <h3 class="text-{{ $info['color'] }}">{{ $info['label'] }} — {{ $info['periode'] }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2 mb-3">
                                <div class="col-md-4"><label class="form-label fs-7 required">Tgl Kunjungan</label>
                                    <input type="date" name="visit_date" value="{{ optional($visit?->visit_date)->format('Y-m-d') ?? today()->format('Y-m-d') }}" class="form-control form-control-solid" required>
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">TD</label>
                                    <input type="text" name="ttv_td" value="{{ $visit?->ttv_td }}" placeholder="120/80" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">N</label>
                                    <input type="number" name="ttv_nadi" value="{{ $visit?->ttv_nadi }}" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">S °C</label>
                                    <input type="number" step="0.1" name="ttv_suhu" value="{{ $visit?->ttv_suhu }}" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">RR</label>
                                    <input type="number" name="ttv_rr" value="{{ $visit?->ttv_rr }}" class="form-control form-control-solid">
                                </div>
                            </div>

                            <div class="separator separator-dashed my-3"></div>
                            <h6 class="text-muted text-uppercase fs-8 mb-2">Pemantauan Ibu</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-3"><label class="form-label fs-7">Kondisi Umum</label>
                                    <select name="kondisi_umum" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['sehat','sakit','komplikasi'] as $opt)
                                            <option value="{{ $opt }}" @selected($visit?->kondisi_umum === $opt)>{{ ucfirst($opt) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">Lokhia</label>
                                    <select name="lokhia" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach($lokhiaOptions as $k => $v)<option value="{{ $k }}" @selected($visit?->lokhia === $k)>{{ $v }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">Lokhia Jumlah</label>
                                    <select name="lokhia_jumlah" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['sedikit','sedang','banyak'] as $opt)<option value="{{ $opt }}" @selected($visit?->lokhia_jumlah === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">Lokhia Bau</label>
                                    <select name="lokhia_bau" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['normal','busuk'] as $opt)<option value="{{ $opt }}" @selected($visit?->lokhia_bau === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">TFU (cm)</label>
                                    <input type="number" step="0.1" name="tfu_cm" value="{{ $visit?->tfu_cm }}" class="form-control form-control-solid">
                                </div>

                                <div class="col-md-3"><label class="form-label fs-7">Jalan Lahir</label>
                                    <select name="jalan_lahir" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['sehat','luka_basah','luka_kering','infeksi'] as $opt)<option value="{{ $opt }}" @selected($visit?->jalan_lahir === $opt)>{{ str_replace('_',' ', ucfirst($opt)) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">Kontraksi</label>
                                    <select name="kontraksi" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['kuat','lemah','atonia'] as $opt)<option value="{{ $opt }}" @selected($visit?->kontraksi === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">Payudara</label>
                                    <select name="payudara" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['sehat','bengkak','lecet','infeksi','abses'] as $opt)<option value="{{ $opt }}" @selected($visit?->payudara === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">ASI</label>
                                    <select name="asi" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['lancar','sedikit','tidak'] as $opt)<option value="{{ $opt }}" @selected($visit?->asi === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>

                                <div class="col-md-2"><label class="form-label fs-7">Vit A Dose</label>
                                    <select name="vit_a_dose" class="form-select form-select-solid">
                                        @for($i=0; $i<=2; $i++)<option value="{{ $i }}" @selected((int)$visit?->vit_a_dose === $i)>{{ $i }}</option>@endfor
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">BAK</label>
                                    <select name="eliminasi_bak" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['lancar','sulit','tidak'] as $opt)<option value="{{ $opt }}" @selected($visit?->eliminasi_bak === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">BAB</label>
                                    <select name="eliminasi_bab" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach(['lancar','sulit','tidak'] as $opt)<option value="{{ $opt }}" @selected($visit?->eliminasi_bab === $opt)>{{ ucfirst($opt) }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-4"><label class="form-label fs-7">Tanggal Kembali</label>
                                    <input type="date" name="tanggal_kembali" value="{{ optional($visit?->tanggal_kembali)->format('Y-m-d') }}" class="form-control form-control-solid">
                                </div>
                            </div>

                            <div class="separator separator-dashed my-3"></div>
                            <div class="row g-2">
                                <div class="col-md-6"><label class="form-label fs-7">Keluhan</label>
                                    <textarea name="keluhan" rows="2" class="form-control form-control-solid">{{ $visit?->keluhan }}</textarea>
                                </div>
                                <div class="col-md-6"><label class="form-label fs-7">Terapi</label>
                                    <textarea name="terapi" rows="2" class="form-control form-control-solid">{{ $visit?->terapi }}</textarea>
                                </div>
                            </div>

                            {{-- Nasehat Checklist --}}
                            <div class="separator separator-dashed my-3"></div>
                            <h6 class="text-muted text-uppercase fs-8 mb-2">📝 Nasehat Diberikan (centang yang sudah dijelaskan)</h6>
                            <div class="row g-2 mb-3">
                                @php $given = $visit?->nasehat_diberikan ?? []; @endphp
                                @foreach($nasehatItems as $key => $label)
                                    <div class="col-md-6">
                                        <div class="form-check form-check-custom p-2 border rounded">
                                            <input class="form-check-input" type="checkbox" name="nasehat_diberikan[]" value="{{ $key }}" id="n_{{ $kfNum }}_{{ $key }}" @checked(in_array($key, $given))>
                                            <label class="form-check-label ms-2 fs-8" for="n_{{ $kfNum }}_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-2">
                                <div class="col-md-3"><label class="form-label fs-7">Status</label>
                                    <select name="status" class="form-select form-select-solid">
                                        @foreach($statusOptions as $k => $v)<option value="{{ $k }}" @selected(($visit?->status ?? 'sehat') === $k)>{{ $v }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-9"><label class="form-label fs-7">Alasan Rujukan (kalau dirujuk)</label>
                                    <input type="text" name="rujukan_alasan" value="{{ $visit?->rujukan_alasan }}" class="form-control form-control-solid">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">{{ $visit ? '💾 Update KF'.$kfNum : '✓ Simpan KF'.$kfNum }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')<x-sweet-flash />@endpush
