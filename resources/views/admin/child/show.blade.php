@extends('admin.layouts.app')
@section('title', $child->nama_bayi)
@section('page_title', 'Bayi/Anak — '.$child->nama_bayi)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-5">
            <div class="card-body text-center">
                <span class="badge badge-light-{{ $child->status_color }} fs-6 mb-3">{{ $child->status_label }}</span>
                <h3 class="mb-1">{{ $child->nama_bayi }}</h3>
                <div class="text-muted fs-7">Anak dari <b>{{ $child->patient?->name }}</b></div>
                <div class="fs-2x fw-bolder text-info mt-3 font-monospace">{{ $child->no_kartu_bayi }}</div>

                <div class="my-4 p-3 rounded text-white" style="background:linear-gradient(135deg,{{ $child->jenis_kelamin === 'L' ? '#3b82f6,#1e40af' : '#ec4899,#be185d' }});">
                    <div class="text-white-50 fs-8 text-uppercase">{{ $child->jenis_kelamin === 'L' ? '👦 Laki-laki' : '👧 Perempuan' }}</div>
                    <div class="fs-3 fw-bolder text-white">Umur: {{ $child->umur_label ?? '-' }}</div>
                    <div class="fs-7">Lahir: {{ optional($child->tanggal_lahir)->isoFormat('D MMM YYYY') }}</div>
                </div>

                <div class="text-muted fs-7">⚖ BB Lahir: <b class="text-dark">{{ $child->bb_lahir_gram ?? '-' }} gr</b></div>
                <div class="text-muted fs-7 mt-1">📏 PB Lahir: <b class="text-dark">{{ $child->pb_lahir_cm ?? '-' }} cm</b></div>

                @if(auth()->user()->hasPermission('child.update'))
                    <a href="{{ route('admin.kn.edit', $child) }}" class="btn btn-sm btn-light-warning w-100 mt-3">
                        <i class="ki-outline ki-pencil fs-3"></i> Edit Data Bayi
                    </a>
                @endif
                <a href="{{ route('admin.kn.show', $child) }}" class="btn btn-sm btn-light-info w-100 mt-2">
                    <i class="ki-outline ki-tag fs-3"></i> Lihat KN (Neonatus)
                </a>
                <a href="{{ route('admin.child.index') }}" class="btn btn-sm btn-light w-100 mt-2">← Daftar Anak</a>
            </div>
        </div>

        {{-- Tindakan saat lahir --}}
        <div class="card mb-5">
            <div class="card-header py-3"><h4 class="card-title">💉 Tindakan Saat Lahir</h4></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-1">
                    @if($child->imd_dilakukan)<span class="badge badge-light-success">✓ IMD</span>@endif
                    @if($child->vit_k1_diberi)<span class="badge badge-light-success">✓ Vit K1</span>@endif
                    @if($child->salep_mata)<span class="badge badge-light-success">✓ Salep Mata</span>@endif
                    @if($child->hb0_diberi)<span class="badge badge-light-success">✓ HB-0</span>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        {{-- ===== MATRIX IMUNISASI ===== --}}
        <div class="card mb-5">
            <div class="card-header py-3">
                <h3 class="card-title">💉 Status Imunisasi</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gs-0 gy-2">
                        <thead><tr class="fw-bold text-muted bg-light-info">
                            <th class="ps-4">Jenis Imunisasi</th>
                            <th class="text-center">Dose I</th>
                            <th class="text-center">Dose II</th>
                            <th class="text-center">Dose III</th>
                            <th class="text-center">Dose IV</th>
                            <th class="text-center">Dose V</th>
                        </tr></thead>
                        <tbody>
                            @foreach($matrix as $typeId => $row)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $row['type']->name }}</div>
                                        <div class="fs-9 text-muted">{{ $row['type']->code }}</div>
                                    </td>
                                    @for($d = 1; $d <= 5; $d++)
                                        <td class="text-center">
                                            @if(! isset($row['doses'][$d]) || $d > $row['type']->max_dose)
                                                <span class="text-muted">-</span>
                                            @elseif($row['doses'][$d])
                                                @php $rec = $row['doses'][$d]; @endphp
                                                <button class="btn btn-sm btn-success btn-show-imm"
                                                        data-bs-toggle="modal" data-bs-target="#modal_imm_detail"
                                                        data-info="{{ json_encode([
                                                            'type' => $row['type']->name,
                                                            'dose' => $d,
                                                            'date' => $rec->given_date?->isoFormat('D MMM YYYY'),
                                                            'batch' => $rec->no_batch,
                                                            'tempat' => $rec->tempat,
                                                            'side' => $rec->side_effects,
                                                            'next' => optional($rec->next_due_date)->isoFormat('D MMM YYYY'),
                                                        ]) }}"
                                                        title="{{ optional($rec->given_date)->format('d/m/Y') }}">
                                                    ✓
                                                </button>
                                            @else
                                                @if(auth()->user()->hasPermission('immunization.create'))
                                                    <button type="button" class="btn btn-sm btn-light-info btn-add-imm"
                                                            data-bs-toggle="modal" data-bs-target="#modal_imm_add"
                                                            data-type-id="{{ $row['type']->id }}"
                                                            data-type-name="{{ $row['type']->name }}"
                                                            data-dose="{{ $d }}">
                                                        + Beri
                                                    </button>
                                                @else
                                                    <span class="badge badge-light">Belum</span>
                                                @endif
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===== KUNJUNGAN KMS (Tumbuh Kembang) ===== --}}
        <div class="card mb-5">
            <div class="card-header py-3">
                <h3 class="card-title">📋 Kunjungan Anak (KMS — Tumbuh Kembang)</h3>
                @if(auth()->user()->hasPermission('child.create'))
                    <div class="card-toolbar">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_visit_add">
                            <i class="ki-outline ki-plus fs-3"></i> Tambah Kunjungan
                        </button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gs-0 gy-2 fs-8">
                        <thead><tr class="fw-bold text-muted bg-light-success">
                            <th>Tgl/Jenis</th>
                            <th>Umur</th>
                            <th>BB/PB</th>
                            <th>Lingkar Kepala</th>
                            <th>Suhu</th>
                            <th>Status Gizi</th>
                            <th>Perkembangan</th>
                            <th>Keluhan/Tindakan</th>
                            <th>Tgl Kembali</th>
                        </tr></thead>
                        <tbody>
                            @forelse($child->childVisits as $v)
                                <tr>
                                    <td>
                                        {{ $v->visit_date?->isoFormat('D MMM YY') }}
                                        @if($v->visit_type)<div class="text-muted fs-9">{{ $visitTypeOptions[$v->visit_type] ?? $v->visit_type }}</div>@endif
                                    </td>
                                    <td>{{ $v->umur_label ?? '-' }}</td>
                                    <td>
                                        @if($v->berat_badan_gram){{ $v->berat_badan_gram }} g<br>@endif
                                        @if($v->panjang_badan_cm){{ $v->panjang_badan_cm }} cm@endif
                                    </td>
                                    <td>{{ $v->lingkar_kepala_cm ?? '-' }} cm</td>
                                    <td>{{ $v->suhu_celcius ?? '-' }}°C</td>
                                    <td>
                                        @if($v->status_gizi)
                                            <span class="badge badge-light-{{ str_contains($v->status_gizi, 'buruk') || str_contains($v->status_gizi, 'obesitas') ? 'danger' : (str_contains($v->status_gizi, 'baik') ? 'success' : 'warning') }} fs-9">
                                                {{ $statusGiziOptions[$v->status_gizi] ?? $v->status_gizi }}
                                            </span>
                                        @endif
                                        @if($v->stunting)<div class="badge badge-light-danger fs-9 mt-1">⚠ Stunting</div>@endif
                                        @if($v->wasting)<div class="badge badge-light-danger fs-9 mt-1">⚠ Wasting</div>@endif
                                    </td>
                                    <td>
                                        @if($v->perkembangan_status)
                                            <span class="badge badge-light-{{ $v->perkembangan_status === 'sesuai' ? 'success' : ($v->perkembangan_status === 'meragukan' ? 'warning' : 'danger') }} fs-9">
                                                {{ $perkembanganOptions[$v->perkembangan_status] ?? $v->perkembangan_status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($v->keluhan)<div><b>K:</b> {{ \Illuminate\Support\Str::limit($v->keluhan, 30) }}</div>@endif
                                        @if($v->tindakan)<div class="text-muted"><b>T:</b> {{ \Illuminate\Support\Str::limit($v->tindakan, 30) }}</div>@endif
                                    </td>
                                    <td>{{ optional($v->tanggal_kembali)->isoFormat('D MMM YY') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-5">Belum ada kunjungan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal Beri Imunisasi ===== --}}
<div class="modal fade" id="modal_imm_add" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.child.immunization.store', $child) }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="immunization_type_id" id="imm_type_id">
            <input type="hidden" name="dose_number" id="imm_dose_num">
            <div class="modal-header bg-light-info">
                <h3>💉 Beri Imunisasi <span id="imm_label_type"></span> Dose <span id="imm_label_dose"></span></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fs-7 required">Tgl Pemberian</label>
                        <input type="date" name="given_date" value="{{ today()->format('Y-m-d') }}" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">No. Batch</label>
                        <input type="text" name="no_batch" class="form-control form-control-solid" placeholder="Mis. ABC123">
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Tempat</label>
                        <input type="text" name="tempat" value="{{ $child->site->name ?? '' }}" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Tgl Dose Berikutnya</label>
                        <input type="date" name="next_due_date" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7">Catatan</label>
                        <input type="text" name="catatan" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7">Efek Samping (KIPI)</label>
                        <textarea name="side_effects" rows="2" class="form-control form-control-solid" placeholder="Demam ringan, bengkak, kemerahan, dll"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">✓ Catat Pemberian</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Modal Detail Imunisasi ===== --}}
<div class="modal fade" id="modal_imm_detail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light-success">
                <h3>✅ Detail Imunisasi</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="imm_detail_body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal Tambah Kunjungan KMS ===== --}}
<div class="modal fade" id="modal_visit_add" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('admin.child.visit.store', $child) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-light-success">
                <h3>📋 Tambah Kunjungan Anak (KMS)</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3"><label class="form-label fs-7 required">Tgl Kunjungan</label>
                        <input type="date" name="visit_date" value="{{ today()->format('Y-m-d') }}" class="form-control form-control-solid" required>
                    </div>
                    <div class="col-md-4"><label class="form-label fs-7">Jenis Kunjungan</label>
                        <select name="visit_type" class="form-select form-select-solid">
                            <option value="">-</option>
                            @foreach($visitTypeOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-5"><label class="form-label fs-7">Tgl Kembali</label>
                        <input type="date" name="tanggal_kembali" class="form-control form-control-solid">
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>
                <h6 class="text-muted text-uppercase fs-8 mb-2">📏 Pengukuran (KMS)</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-3"><label class="form-label fs-7">BB (gram)</label>
                        <input type="number" name="berat_badan_gram" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">PB/TB (cm)</label>
                        <input type="number" step="0.1" name="panjang_badan_cm" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Lingkar Kepala (cm)</label>
                        <input type="number" step="0.1" name="lingkar_kepala_cm" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Lingkar Lengan (cm)</label>
                        <input type="number" step="0.1" name="lingkar_lengan_cm" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Suhu °C</label>
                        <input type="number" step="0.1" name="suhu_celcius" class="form-control form-control-solid">
                    </div>
                    <div class="col-md-3"><label class="form-label fs-7">Status Gizi</label>
                        <select name="status_gizi" class="form-select form-select-solid">
                            <option value="">-</option>
                            @foreach($statusGiziOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-check-custom pb-2">
                            <input class="form-check-input" type="checkbox" name="stunting" value="1" id="stunting">
                            <label class="form-check-label ms-2 fs-7" for="stunting">Stunting (TB/U)</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-check-custom pb-2">
                            <input class="form-check-input" type="checkbox" name="wasting" value="1" id="wasting">
                            <label class="form-check-label ms-2 fs-7" for="wasting">Wasting (BB/TB)</label>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4"><label class="form-label fs-7">Status Perkembangan</label>
                        <select name="perkembangan_status" class="form-select form-select-solid">
                            <option value="">-</option>
                            @foreach($perkembanganOptions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-8"><label class="form-label fs-7">Catatan Perkembangan</label>
                        <input type="text" name="perkembangan_catatan" class="form-control form-control-solid" placeholder="Mis. duduk sendiri, merangkak, kata pertama">
                    </div>
                </div>

                <div class="separator separator-dashed my-3"></div>
                <div class="row g-2">
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch form-check-custom pb-2">
                            <input class="form-check-input" type="checkbox" name="asi_eksklusif" value="1" id="asix">
                            <label class="form-check-label ms-2 fs-7" for="asix">ASI Eksklusif (< 6 bln)</label>
                        </div>
                    </div>
                    <div class="col-md-8"><label class="form-label fs-7">PMT (Pemberian Makanan Tambahan)</label>
                        <input type="text" name="pmt" class="form-control form-control-solid" placeholder="Mis. bubur tim, biskuit MP-ASI">
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Keluhan</label>
                        <textarea name="keluhan" rows="2" class="form-control form-control-solid"></textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Diagnosis</label>
                        <textarea name="diagnosis" rows="2" class="form-control form-control-solid"></textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Tindakan</label>
                        <textarea name="tindakan" rows="2" class="form-control form-control-solid"></textarea>
                    </div>
                    <div class="col-md-6"><label class="form-label fs-7">Terapi</label>
                        <textarea name="terapi" rows="2" class="form-control form-control-solid"></textarea>
                    </div>
                    <div class="col-md-12"><label class="form-label fs-7">Catatan</label>
                        <input type="text" name="notes" class="form-control form-control-solid">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">✓ Simpan Kunjungan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<x-sweet-flash />
<script>
$(function() {
    $('.btn-add-imm').on('click', function() {
        $('#imm_type_id').val($(this).data('type-id'));
        $('#imm_dose_num').val($(this).data('dose'));
        $('#imm_label_type').text($(this).data('type-name'));
        $('#imm_label_dose').text($(this).data('dose'));
    });

    $('.btn-show-imm').on('click', function() {
        const d = $(this).data('info');
        const html = `
            <div class="row g-3">
                <div class="col-md-6"><div class="text-muted fs-7">Jenis</div><div class="fw-bold">${d.type} — Dose ${d.dose}</div></div>
                <div class="col-md-6"><div class="text-muted fs-7">Tgl Pemberian</div><div class="fw-bold">${d.date || '-'}</div></div>
                <div class="col-md-6"><div class="text-muted fs-7">No. Batch</div><div class="fw-bold">${d.batch || '-'}</div></div>
                <div class="col-md-6"><div class="text-muted fs-7">Tempat</div><div class="fw-bold">${d.tempat || '-'}</div></div>
                ${d.side ? '<div class="col-md-12"><div class="text-muted fs-7">⚠ Efek Samping (KIPI)</div><div class="p-2 bg-light-warning rounded">' + d.side + '</div></div>' : ''}
                ${d.next ? '<div class="col-md-12"><div class="text-muted fs-7">🗓 Dose Berikutnya</div><div class="fw-bold text-info">' + d.next + '</div></div>' : ''}
            </div>
        `;
        $('#imm_detail_body').html(html);
    });
});
</script>
@endpush
