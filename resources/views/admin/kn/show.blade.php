@extends('admin.layouts.app')
@section('title', 'Bayi — '.$neonate->nama_bayi)
@section('page_title', 'Detail Bayi & Neonatus')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-5">
            <div class="card-body text-center">
                <span class="badge badge-light-{{ $neonate->status_color }} fs-6 mb-3">{{ $neonate->status_label }}</span>
                <h3 class="mb-1">{{ $neonate->nama_bayi }}</h3>
                <div class="text-muted fs-7">Bayi dari <b>{{ $neonate->patient?->name }}</b></div>
                <div class="fs-2x fw-bolder text-info mt-3 font-monospace">{{ $neonate->no_kartu_bayi }}</div>

                <div class="my-4 p-3 rounded text-white" style="background:linear-gradient(135deg,{{ $neonate->jenis_kelamin === 'L' ? '#3b82f6,#1e40af' : '#ec4899,#be185d' }});">
                    <div class="text-white-50 fs-8 text-uppercase">{{ $neonate->jenis_kelamin === 'L' ? '👦 Laki-laki' : '👧 Perempuan' }}</div>
                    <div class="fs-3 fw-bolder text-white">{{ $neonate->bb_lahir_gram ?? '-' }} gr · {{ $neonate->bb_lahir_gram >= 2500 && $neonate->bb_lahir_gram <= 4000 ? '✓ Normal' : ($neonate->bb_lahir_gram < 2500 ? '⚠ BBLR' : '⚠ Makrosomia') }}</div>
                    <div class="fs-7">{{ $neonate->pb_lahir_cm ?? '-' }} cm</div>
                </div>

                <div class="text-muted fs-7">📅 Lahir: <b class="text-dark">{{ optional($neonate->tanggal_lahir)->isoFormat('D MMM YYYY') }} {{ $neonate->jam_lahir }}</b></div>
                @if($neonate->umur_hari !== null)
                    <div class="text-muted fs-7 mt-1">⏱ Umur: <b class="text-warning">{{ $neonate->umur_hari }} hari</b></div>
                @endif

                @if(auth()->user()->hasPermission('kn.update'))
                    <a href="{{ route('admin.kn.edit', $neonate) }}" class="btn btn-sm btn-light-warning w-100 mt-3">
                        <i class="ki-outline ki-pencil fs-3"></i> Edit Data Bayi
                    </a>
                @endif
                @if($neonate->delivery)
                    <a href="{{ route('admin.inc.show', $neonate->delivery) }}" class="btn btn-sm btn-light-info w-100 mt-2">
                        <i class="ki-outline ki-pulse fs-3"></i> Lihat Persalinan
                    </a>
                @endif
                @if(auth()->user()->hasPermission('child.view'))
                    <a href="{{ route('admin.child.show', $neonate) }}" class="btn btn-sm btn-info w-100 mt-2">
                        <i class="ki-outline ki-syringe fs-3"></i> Imunisasi & KMS
                    </a>
                @endif
                <a href="{{ route('admin.kn.index') }}" class="btn btn-sm btn-light w-100 mt-2">← Daftar Bayi</a>
            </div>
        </div>

        {{-- Tindakan saat lahir --}}
        <div class="card mb-5">
            <div class="card-header py-3"><h4 class="card-title">💉 Tindakan Saat Lahir</h4></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-1">
                    @if($neonate->imd_dilakukan)<span class="badge badge-light-success">✓ IMD</span>@endif
                    @if($neonate->vit_k1_diberi)<span class="badge badge-light-success">✓ Vit K1 {{ optional($neonate->vit_k1_at)->format('H:i') }}</span>@endif
                    @if($neonate->salep_mata)<span class="badge badge-light-success">✓ Salep Mata</span>@endif
                    @if($neonate->hb0_diberi)<span class="badge badge-light-success">✓ HB-0 {{ optional($neonate->hb0_at)->format('H:i') }}</span>@endif
                </div>
                @if($neonate->hb0_batch)
                    <div class="text-muted fs-8 mt-2">HB-0 Batch: <b>{{ $neonate->hb0_batch }}</b></div>
                @endif
                @if(! $neonate->imd_dilakukan && ! $neonate->vit_k1_diberi && ! $neonate->salep_mata && ! $neonate->hb0_diberi)
                    <div class="text-muted">Belum ada tindakan tercatat. Edit untuk lengkapi.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- 3 KN Timeline Cards --}}
        @foreach($knPeriods as $knNum => $info)
            @php $visit = $visits->get($knNum); @endphp
            <div class="card mb-4 border border-2 border-{{ $info['color'] }}">
                <div class="card-header bg-light-{{ $info['color'] }} py-3">
                    <h4 class="card-title text-{{ $info['color'] }} mb-0">
                        @if($visit)<i class="ki-outline ki-check-circle fs-3 text-success me-1"></i>@endif
                        {{ $info['label'] }} — <span class="fs-7 text-muted">{{ $info['periode'] }}</span>
                    </h4>
                    <div class="card-toolbar">
                        @if($visit)
                            <span class="badge badge-light-success">✓ {{ $visit->visit_date->isoFormat('D MMM YY') }}</span>
                        @endif
                        @if(auth()->user()->hasPermission('kn.create'))
                            <button class="btn btn-sm btn-{{ $visit ? 'light-warning' : 'primary' }} ms-2" data-bs-toggle="modal" data-bs-target="#modal_kn_{{ $knNum }}">
                                <i class="ki-outline ki-{{ $visit ? 'pencil' : 'plus' }} fs-3"></i> {{ $visit ? 'Edit' : 'Catat KN'.$knNum }}
                            </button>
                        @endif
                    </div>
                </div>
                @if($visit)
                <div class="card-body">
                    <div class="row g-2 fs-8">
                        <div class="col-md-3"><div class="text-muted">BB</div><b>{{ $visit->berat_badan_gram ?? '-' }} gr</b></div>
                        <div class="col-md-3"><div class="text-muted">PB</div><b>{{ $visit->panjang_badan_cm ?? '-' }} cm</b></div>
                        <div class="col-md-3"><div class="text-muted">Suhu</div><b>{{ $visit->suhu_celcius ?? '-' }}°C</b></div>
                        <div class="col-md-3"><div class="text-muted">Tali Pusat</div><b>{{ ucfirst($visit->tali_pusat ?? '-') }}</b></div>

                        <div class="col-md-4"><div class="text-muted">Menyusu</div><b>{{ ucfirst($visit->menyusu ?? '-') }}</b></div>
                        <div class="col-md-4"><div class="text-muted">Ikterus</div><b>Level {{ $visit->ikterus_level ?? 0 }} {{ $visit->ikterus_level >= 3 ? '⚠' : '' }}</b></div>
                        <div class="col-md-4"><div class="text-muted">Lingkar Kepala</div><b>{{ $visit->lingkar_kepala_cm ?? '-' }} cm</b></div>

                        @if($visit->tanda_bahaya && count($visit->tanda_bahaya))
                            <div class="col-md-12 mt-2">
                                <div class="text-muted text-danger fs-9 fw-bold">🚨 TANDA BAHAYA TERDETEKSI:</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($visit->tanda_bahaya as $tb)
                                        <span class="badge badge-light-danger fs-9">⚠ {{ $tandaBahayaItems[$tb] ?? $tb }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($visit->masalah_lain)
                            <div class="col-md-6"><div class="text-muted">Masalah Lain</div><b>{{ $visit->masalah_lain }}</b></div>
                        @endif
                        @if($visit->tindakan)
                            <div class="col-md-6"><div class="text-muted">Tindakan</div><b>{{ $visit->tindakan }}</b></div>
                        @endif

                        @if($visit->dirujuk)
                            <div class="col-md-12 mt-2">
                                <div class="alert alert-danger py-2 fs-8 mb-0">
                                    🚨 <b>DIRUJUK</b> — {{ $visit->rujukan_alasan }}
                                </div>
                            </div>
                        @endif

                        @if($visit->tanggal_kembali)
                            <div class="col-md-12 mt-2">
                                <span class="badge badge-light-info">🗓 Kembali: {{ $visit->tanggal_kembali->isoFormat('D MMM YY') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- Modal Form KN{{ $knNum }} --}}
            <div class="modal fade" id="modal_kn_{{ $knNum }}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <form action="{{ route('admin.kn.visit.store', $neonate) }}" method="POST" class="modal-content">
                        @csrf
                        <input type="hidden" name="kn_number" value="{{ $knNum }}">
                        <div class="modal-header bg-light-{{ $info['color'] }}">
                            <h3 class="text-{{ $info['color'] }}">{{ $info['label'] }} — {{ $info['periode'] }}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2 mb-3">
                                <div class="col-md-3"><label class="form-label fs-7 required">Tgl Kunjungan</label>
                                    <input type="date" name="visit_date" value="{{ optional($visit?->visit_date)->format('Y-m-d') ?? today()->format('Y-m-d') }}" class="form-control form-control-solid" required>
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">BB (gram)</label>
                                    <input type="number" name="berat_badan_gram" value="{{ $visit?->berat_badan_gram }}" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">PB (cm)</label>
                                    <input type="number" step="0.1" name="panjang_badan_cm" value="{{ $visit?->panjang_badan_cm }}" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-2"><label class="form-label fs-7">Suhu °C</label>
                                    <input type="number" step="0.1" name="suhu_celcius" value="{{ $visit?->suhu_celcius }}" placeholder="36.5-37.5" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">Lingkar Kepala (cm)</label>
                                    <input type="number" step="0.1" name="lingkar_kepala_cm" value="{{ $visit?->lingkar_kepala_cm }}" class="form-control form-control-solid">
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-4"><label class="form-label fs-7">Tali Pusat</label>
                                    <select name="tali_pusat" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach($taliPusatOptions as $k => $v)<option value="{{ $k }}" @selected($visit?->tali_pusat === $k)>{{ $v }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-4"><label class="form-label fs-7">Menyusu</label>
                                    <select name="menyusu" class="form-select form-select-solid">
                                        <option value="">-</option>
                                        @foreach($menyusuOptions as $k => $v)<option value="{{ $k }}" @selected($visit?->menyusu === $k)>{{ $v }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="col-md-4"><label class="form-label fs-7">Ikterus Level (Kramer)</label>
                                    <select name="ikterus_level" class="form-select form-select-solid">
                                        @for($i=0; $i<=4; $i++)<option value="{{ $i }}" @selected((int)$visit?->ikterus_level === $i)>{{ $i }} — {{ ['Tidak ada','Wajah','Sampai dada','Sampai perut','Seluruh tubuh'][$i] }}</option>@endfor
                                    </select>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-3"></div>
                            <h6 class="text-danger text-uppercase fs-8 mb-2">🚨 Tanda Bahaya (centang yang ada)</h6>
                            @php $tbGiven = $visit?->tanda_bahaya ?? []; @endphp
                            <div class="row g-2 mb-3">
                                @foreach($tandaBahayaItems as $key => $label)
                                    <div class="col-md-4">
                                        <div class="form-check form-check-custom p-2 border rounded">
                                            <input class="form-check-input" type="checkbox" name="tanda_bahaya[]" value="{{ $key }}" id="tb_{{ $knNum }}_{{ $key }}" @checked(in_array($key, $tbGiven))>
                                            <label class="form-check-label ms-2 fs-8" for="tb_{{ $knNum }}_{{ $key }}">⚠ {{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6"><label class="form-label fs-7">Masalah Lain</label>
                                    <textarea name="masalah_lain" rows="2" class="form-control form-control-solid">{{ $visit?->masalah_lain }}</textarea>
                                </div>
                                <div class="col-md-6"><label class="form-label fs-7">Tindakan</label>
                                    <textarea name="tindakan" rows="2" class="form-control form-control-solid">{{ $visit?->tindakan }}</textarea>
                                </div>
                                <div class="col-md-6"><label class="form-label fs-7">Terapi</label>
                                    <textarea name="terapi" rows="2" class="form-control form-control-solid">{{ $visit?->terapi }}</textarea>
                                </div>
                                <div class="col-md-3"><label class="form-label fs-7">Tanggal Kembali</label>
                                    <input type="date" name="tanggal_kembali" value="{{ optional($visit?->tanggal_kembali)->format('Y-m-d') }}" class="form-control form-control-solid">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="form-check form-switch form-check-custom pb-2">
                                        <input class="form-check-input" type="checkbox" name="dirujuk" value="1" id="dirujuk_{{ $knNum }}" @checked($visit?->dirujuk)>
                                        <label class="form-check-label fw-semibold ms-2 fs-7" for="dirujuk_{{ $knNum }}">Dirujuk</label>
                                    </div>
                                </div>
                                <div class="col-md-12"><label class="form-label fs-7">Alasan Rujukan (kalau dirujuk)</label>
                                    <input type="text" name="rujukan_alasan" value="{{ $visit?->rujukan_alasan }}" class="form-control form-control-solid">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">{{ $visit ? '💾 Update KN'.$knNum : '✓ Simpan KN'.$knNum }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
@push('scripts')<x-sweet-flash />@endpush
