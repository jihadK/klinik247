<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Ibu Hamil — {{ $pregnancy->no_kartu_hamil }} — {{ $pregnancy->patient->name }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f3f3; color: #333; }
        .toolbar { position: sticky; top: 0; background: #fff; padding: 12px 20px; border-bottom: 1px solid #ddd; display: flex; gap: 8px; justify-content: center; z-index: 10; }
        .toolbar button, .toolbar a { padding: 8px 16px; border: 0; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-print { background: #009ef7; color: #fff; }
        .btn-back  { background: #e4e6ef; color: #181c32; }

        .kartu-wrap { padding: 20px; display: flex; justify-content: center; }
        .kartu { width: 210mm; padding: 10mm; background: #e8f5e9; border: 2px solid #2e7d32; box-shadow: 0 4px 16px rgba(0,0,0,.15); font-size: 9pt; }

        .header { text-align: center; padding-bottom: 4mm; border-bottom: 2px solid #2e7d32; margin-bottom: 4mm; }
        .header .clinic-name { font-size: 14pt; font-weight: 800; color: #1b5e20; letter-spacing: 1px; }
        .header .clinic-sub { font-size: 9pt; color: #666; }

        .title-wrap { text-align: center; margin-bottom: 4mm; }
        .title { display: inline-block; padding: 2mm 8mm; background: #2e7d32; color: #fff; font-size: 14pt; font-weight: 800; border-radius: 4px; }
        .no-kartu { font-family: 'Courier New', monospace; font-size: 11pt; font-weight: bold; margin-top: 2mm; }

        .section { margin-bottom: 4mm; }
        .section-title { background: #2e7d32; color: #fff; padding: 1.5mm 3mm; font-weight: bold; font-size: 9pt; }
        .row-pair { display: flex; gap: 5mm; margin-top: 1mm; }
        .row-pair > div { flex: 1; }
        .field { margin-bottom: 1mm; }
        .label { display: inline-block; min-width: 35mm; font-size: 8pt; color: #555; }
        .value { font-weight: 600; border-bottom: 1px dotted #999; padding: 0 2mm; }

        table.hist, table.anc { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-top: 2mm; }
        table.hist th, table.hist td, table.anc th, table.anc td { border: 1px solid #999; padding: 1mm 1.5mm; text-align: left; }
        table.hist th, table.anc th { background: #c8e6c9; font-weight: bold; font-size: 7pt; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .kartu-wrap { padding: 0; }
            .kartu { box-shadow: none; border-width: 0; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <a href="{{ route('admin.anc.show', $pregnancy) }}" class="btn-back">← Kembali</a>
    <button onclick="window.print()" class="btn-print">🖨 Cetak</button>
</div>

<div class="kartu-wrap">
    <div class="kartu">
        <div class="header">
            <div class="clinic-name">{{ strtoupper($pregnancy->site->name ?? 'Klinik') }}</div>
            <div class="clinic-sub">{{ $pregnancy->site->address ?? '' }} · {{ $pregnancy->site->phone ?? '' }}</div>
        </div>

        <div class="title-wrap">
            <div class="title">KARTU IBU HAMIL</div>
            <div class="no-kartu">No: {{ $pregnancy->no_kartu_hamil }}</div>
        </div>

        {{-- Section A: Identitas --}}
        <div class="section">
            <div class="row-pair">
                <div>
                    <div class="section-title">A. Identitas Ibu</div>
                    <div class="field"><span class="label">Nama Ibu</span><span class="value">{{ $pregnancy->patient->name }}</span></div>
                    <div class="field"><span class="label">TTL</span><span class="value">{{ $pregnancy->patient->birth_place }}, {{ optional($pregnancy->patient->birth_date)->format('d/m/Y') }}</span></div>
                    <div class="field"><span class="label">Umur</span><span class="value">{{ $pregnancy->patient->age }}</span></div>
                    <div class="field"><span class="label">Pendidikan</span><span class="value">{{ optional($pregnancy->patient->education)->name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pekerjaan</span><span class="value">{{ $pregnancy->patient->occupation ?? '-' }}</span></div>
                    <div class="field"><span class="label">Agama</span><span class="value">{{ optional($pregnancy->patient->religion)->name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pembiayaan</span><span class="value">{{ optional($pregnancy->patient->payerType)->name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Alamat</span><span class="value">{{ \Illuminate\Support\Str::limit($pregnancy->patient->full_address, 60) }}</span></div>
                    <div class="field"><span class="label">No HP</span><span class="value">{{ $pregnancy->patient->phone ?? '-' }}</span></div>
                </div>
                <div>
                    <div class="section-title">Identitas Suami</div>
                    <div class="field"><span class="label">Nama</span><span class="value">{{ $pregnancy->suami_nama ?? '-' }}</span></div>
                    <div class="field"><span class="label">Umur</span><span class="value">{{ $pregnancy->suami_umur ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pendidikan</span><span class="value">{{ optional($pregnancy->suamiEducation)->name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pekerjaan</span><span class="value">{{ $pregnancy->suami_pekerjaan ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        {{-- Section B: Riwayat Obstetri --}}
        <div class="section">
            <div class="section-title">B. Riwayat Obstetri — {{ $pregnancy->gpa_label }} (Hamil ke-{{ $pregnancy->hamil_ke ?? $pregnancy->gravida }})</div>
            @if($pregnancy->histories->count())
                <table class="hist">
                    <thead><tr>
                        <th>Ke</th><th>Tahun</th><th>JK</th><th>Cara Lahir</th><th>BB</th><th>PB</th>
                        <th>Tempat</th><th>Penolong</th><th>Kondisi</th><th>Komplikasi</th>
                    </tr></thead>
                    <tbody>
                        @foreach($pregnancy->histories as $h)
                            <tr>
                                <td>{{ $h->hamil_ke }}</td>
                                <td>{{ $h->tahun ?? '' }}</td>
                                <td>{{ $h->jenis_kelamin ?? '' }}</td>
                                <td>{{ \App\Models\PregnancyHistory::caraLahirOptions()[$h->cara_lahir] ?? '' }}</td>
                                <td>{{ $h->bb_lahir_gram ? $h->bb_lahir_gram.'g' : '' }}</td>
                                <td>{{ $h->pb_lahir_cm ? $h->pb_lahir_cm.'cm' : '' }}</td>
                                <td>{{ \App\Models\PregnancyHistory::tempatBersalinOptions()[$h->tempat_bersalin] ?? '' }}</td>
                                <td>{{ \App\Models\PregnancyHistory::penolongOptions()[$h->penolong] ?? '' }}</td>
                                <td>{{ \App\Models\PregnancyHistory::kondisiAnakOptions()[$h->kondisi_anak] ?? '' }}</td>
                                <td>{{ $h->komplikasi ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="field" style="padding:2mm 3mm; font-style: italic; color: #888;">Belum ada riwayat anak sebelumnya.</div>
            @endif
        </div>

        {{-- Section C: Pemeriksaan K1 --}}
        <div class="section">
            <div class="section-title">C. Pemeriksaan Saat K1</div>
            <div class="row-pair">
                <div>
                    <div class="field"><span class="label">Tanggal K1</span><span class="value">{{ optional($pregnancy->tanggal_k1)->format('d/m/Y') }}</span></div>
                    <div class="field"><span class="label">HPHT</span><span class="value">{{ optional($pregnancy->hpht)->format('d/m/Y') ?? '-' }}</span></div>
                    <div class="field"><span class="label">HPL</span><span class="value">{{ optional($pregnancy->hpl)->format('d/m/Y') ?? '-' }}</span></div>
                    <div class="field"><span class="label">TB</span><span class="value">{{ $pregnancy->tinggi_badan_cm ?? '-' }} cm</span></div>
                    <div class="field"><span class="label">BB Awal</span><span class="value">{{ $pregnancy->berat_badan_awal ?? '-' }} kg</span></div>
                </div>
                <div>
                    <div class="field"><span class="label">LILA</span><span class="value">{{ $pregnancy->lila_cm ?? '-' }} cm</span></div>
                    <div class="field"><span class="label">IMT</span><span class="value">{{ $pregnancy->imt ?? '-' }}</span></div>
                    <div class="field"><span class="label">Tekanan Darah</span><span class="value">{{ $pregnancy->vital_sign_td ?? '-' }} mmHg</span></div>
                    <div class="field"><span class="label">Recom Kenaikan BB</span><span class="value">{{ $pregnancy->recom_kenaikan_bb ?? '-' }}</span></div>
                </div>
            </div>
            <div class="field"><span class="label">Riwayat Alergi</span><span class="value">{{ $pregnancy->riwayat_alergi ?? '-' }}</span></div>
            <div class="field"><span class="label">Riwayat Penyakit</span><span class="value">{{ $pregnancy->riwayat_penyakit ?? '-' }}</span></div>
            <div class="field"><span class="label">Keluhan Awal</span><span class="value">{{ $pregnancy->keluhan_awal ?? '-' }}</span></div>
        </div>

        {{-- Section D: Kunjungan ANC --}}
        <div class="section">
            <div class="section-title">D. Perawatan Selama Hamil</div>
            <table class="anc">
                <thead><tr>
                    <th>Tgl/Tempat</th><th>Keluhan</th><th>TFU/UK</th><th>Letak/DJJ</th>
                    <th>BB/Tensi</th><th>TT</th><th>Terapi</th><th>Hasil Lab</th>
                    <th>Penatalaksanaan</th><th>Tgl Kembali</th>
                </tr></thead>
                <tbody>
                    @forelse($pregnancy->ancVisits as $v)
                        <tr>
                            <td>{{ $v->visit_date?->format('d/m/y') }}<br><small>{{ $v->tempat_periksa }}</small></td>
                            <td>{{ $v->keluhan }}</td>
                            <td>{{ $v->tfu_cm ? $v->tfu_cm.'cm' : '' }}<br>{{ $v->uk_minggu ? $v->uk_minggu.'mg' : '' }}</td>
                            <td>{{ $v->letak_janin }}<br>{{ $v->djj_per_menit ? $v->djj_per_menit.'/mnt' : '' }}</td>
                            <td>{{ $v->berat_badan_kg ? $v->berat_badan_kg.'kg' : '' }}<br>{{ $v->tekanan_darah }}</td>
                            <td>{{ $v->status_tt }}<br>{{ $v->pemberian_tt ? '✓' : '' }}</td>
                            <td>{{ $v->terapi }}</td>
                            <td>{{ $v->hasil_lab }}</td>
                            <td>{{ $v->penatalaksanaan }}</td>
                            <td>{{ optional($v->tanggal_kembali)->format('d/m/y') }}</td>
                        </tr>
                    @empty
                        @for($i=0; $i<10; $i++)
                            <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                        @endfor
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
