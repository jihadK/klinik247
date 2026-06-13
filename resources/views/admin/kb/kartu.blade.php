<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu KB — {{ $acceptor->no_kartu_kb }} — {{ $acceptor->patient->name }}</title>
    <style>
        @page { size: A5; margin: 8mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f3f3f3; color: #333; }
        .toolbar { position: sticky; top: 0; background: #fff; padding: 12px 20px; border-bottom: 1px solid #ddd; display: flex; gap: 8px; justify-content: center; z-index: 10; }
        .toolbar button, .toolbar a { padding: 8px 16px; border: 0; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-print { background: #009ef7; color: #fff; }
        .btn-back  { background: #e4e6ef; color: #181c32; }

        .kartu-wrap { padding: 20px; display: flex; justify-content: center; }
        .kartu { width: 148mm; min-height: 210mm; padding: 8mm; background: #fde0ef; border: 2px solid #d63384; border-radius: 4px; box-shadow: 0 4px 16px rgba(0,0,0,.15); font-size: 9pt; }

        .header { text-align: center; padding-bottom: 4mm; border-bottom: 2px solid #d63384; margin-bottom: 4mm; }
        .header .clinic-name { font-size: 14pt; font-weight: 800; color: #c2185b; letter-spacing: 1px; }
        .header .clinic-sub { font-size: 9pt; color: #666; }

        .title { text-align: center; font-size: 16pt; font-weight: 800; color: #c2185b; padding: 2mm; border: 2px solid #c2185b; margin: 3mm auto; display: inline-block; }
        .title-wrap { text-align: center; }
        .no-kartu { font-family: 'Courier New', monospace; font-size: 11pt; font-weight: bold; }

        .section { margin-bottom: 4mm; }
        .section-title { background: #c2185b; color: #fff; padding: 1mm 3mm; font-weight: bold; font-size: 9pt; }
        .row-pair { display: flex; gap: 5mm; margin-top: 1mm; }
        .row-pair > div { flex: 1; }
        .field { margin-bottom: 1mm; }
        .label { display: inline-block; min-width: 40mm; font-size: 8pt; color: #555; }
        .value { font-weight: 600; border-bottom: 1px dotted #999; padding: 0 2mm; }

        table.visits { width: 100%; border-collapse: collapse; margin-top: 2mm; font-size: 7.5pt; }
        table.visits th, table.visits td { border: 1px solid #999; padding: 1mm 1.5mm; text-align: left; }
        table.visits th { background: #fff; font-weight: bold; }
        .consent { text-align: center; margin-top: 6mm; padding-top: 3mm; border-top: 1px solid #999; }
        .signature { display: flex; justify-content: space-between; margin-top: 12mm; font-size: 8pt; text-align: center; }
        .signature > div { width: 45%; }
        .signature .line { border-top: 1px solid #555; margin-top: 18mm; padding-top: 1mm; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .kartu-wrap { padding: 0; }
            .kartu { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <a href="{{ route('admin.kb.show', $acceptor) }}" class="btn-back">← Kembali</a>
    <button onclick="window.print()" class="btn-print">🖨 Cetak</button>
</div>

<div class="kartu-wrap">
    <div class="kartu">
        <div class="header">
            <div class="clinic-name">{{ strtoupper($acceptor->site->name ?? 'Klinik') }}</div>
            <div class="clinic-sub">{{ $acceptor->site->address ?? '' }} · {{ $acceptor->site->phone ?? '' }}</div>
        </div>

        <div class="title-wrap">
            <div class="title">KARTU K.B.</div>
            <div class="no-kartu">No: {{ $acceptor->no_kartu_kb }}</div>
        </div>

        {{-- Identitas Akseptor + Suami --}}
        <div class="section">
            <div class="row-pair">
                <div>
                    <div class="section-title">Akseptor</div>
                    <div class="field"><span class="label">Nama</span><span class="value">{{ $acceptor->patient->name }}</span></div>
                    <div class="field"><span class="label">Umur</span><span class="value">{{ $acceptor->patient->age }}</span></div>
                    <div class="field"><span class="label">Pendidikan</span><span class="value">{{ optional($acceptor->patient->education)->name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Kawin ke</span><span class="value">{{ $acceptor->akseptor_kawin_ke ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pekerjaan</span><span class="value">{{ $acceptor->patient->occupation ?? '-' }}</span></div>
                    <div class="field"><span class="label">Alamat</span><span class="value">{{ \Illuminate\Support\Str::limit($acceptor->patient->full_address, 50) }}</span></div>
                </div>
                <div>
                    <div class="section-title">Suami</div>
                    <div class="field"><span class="label">Nama</span><span class="value">{{ $acceptor->suami_name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Umur</span><span class="value">{{ $acceptor->suami_age ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pendidikan</span><span class="value">{{ optional($acceptor->suamiEducation)->name ?? '-' }}</span></div>
                    <div class="field"><span class="label">Kawin ke</span><span class="value">{{ $acceptor->suami_kawin_ke ?? '-' }}</span></div>
                    <div class="field"><span class="label">Pekerjaan</span><span class="value">{{ $acceptor->suami_occupation ?? '-' }}</span></div>
                    <div class="field"><span class="label">Telp</span><span class="value">{{ $acceptor->patient->phone ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        {{-- Status & Pemeriksaan singkat --}}
        <div class="section">
            <div class="section-title">Status Peserta KB Baru</div>
            <div class="field"><span class="label">Jumlah Anak Hidup</span><span class="value">{{ $acceptor->jumlah_anak_hidup ?? '-' }}</span></div>
            <div class="field"><span class="label">Ingin Anak Lagi</span><span class="value">{{ ucwords(str_replace('_',' ', $acceptor->keinginan_punya_anak_lagi ?? '-')) }}</span></div>
            <div class="field"><span class="label">Sikap Pasangan</span><span class="value">{{ ucwords(str_replace('_',' ', $acceptor->sikap_pasangan_terhadap_kb ?? '-')) }}</span></div>
        </div>

        <div class="section">
            <div class="section-title">Pemeriksaan Awal</div>
            <div class="row-pair">
                <div>
                    <div class="field"><span class="label">Tekanan Darah</span><span class="value">{{ $acceptor->tekanan_darah ?? '-' }}</span></div>
                    <div class="field"><span class="label">Berat Badan</span><span class="value">{{ $acceptor->berat_badan ?? '-' }} kg</span></div>
                </div>
                <div>
                    <div class="field"><span class="label">Haid Terakhir</span><span class="value">{{ optional($acceptor->haid_terakhir)->isoFormat('D MMM YY') ?? '-' }}</span></div>
                    <div class="field"><span class="label">Persalinan Terakhir</span><span class="value">{{ optional($acceptor->tanggal_persalinan_terakhir)->isoFormat('D MMM YY') ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Alat Kontrasepsi</div>
            <div class="field"><span class="label">Jenis</span><span class="value"><b>{{ strtoupper($acceptor->kontrasepsi?->name ?? '-') }}</b></span></div>
            <div class="row-pair">
                <div>
                    <div class="field"><span class="label">Tgl Dilayani</span><span class="value">{{ optional($acceptor->tanggal_dilayani)->isoFormat('D MMM YYYY') }}</span></div>
                </div>
                <div>
                    <div class="field"><span class="label">Tgl Kontrol</span><span class="value">{{ optional($acceptor->tanggal_pesan_kontrol)->isoFormat('D MMM YYYY') ?? '-' }}</span></div>
                </div>
                <div>
                    <div class="field"><span class="label">Tgl Dilepas</span><span class="value">{{ optional($acceptor->tanggal_dilepas)->isoFormat('D MMM YYYY') ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        {{-- Tabel Kunjungan Ulang --}}
        <div class="section">
            <div class="section-title">Kunjungan Ulang</div>
            <table class="visits">
                <thead>
                    <tr>
                        <th style="width:18mm">Tgl</th>
                        <th style="width:18mm">Haid Tgl</th>
                        <th style="width:14mm">B.B.</th>
                        <th style="width:18mm">TD</th>
                        <th>Keluhan / Efek Samping / Komplikasi</th>
                        <th>Tindakan</th>
                        <th style="width:18mm">Tgl Kembali</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($acceptor->visits as $v)
                        <tr>
                            <td>{{ $v->visit_date?->format('d/m/y') }}</td>
                            <td>{{ optional($v->haid_tanggal)->format('d/m/y') ?? '' }}</td>
                            <td>{{ $v->berat_badan ?? '' }}</td>
                            <td>{{ $v->tekanan_darah ?? '' }}</td>
                            <td>
                                {{ $v->keluhan }}
                                @if($v->efek_samping) | ES: {{ $v->efek_samping }} @endif
                                @if($v->komplikasi) | Komp: {{ $v->komplikasi }} @endif
                            </td>
                            <td>{{ $v->tindakan ?? '' }}</td>
                            <td>{{ optional($v->tanggal_kembali)->format('d/m/y') ?? '' }}</td>
                        </tr>
                    @empty
                        @for($i=0; $i<8; $i++)
                            <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                        @endfor
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="consent">
            <b>PERSETUJUAN PELAYANAN KONTRASEPSI (INFORMED CONSENT)</b>
            <p style="font-size:8pt; margin: 2mm 0;">
                Kami yang bertanda tangan di bawah ini telah memahami penjelasan mengenai alat kontrasepsi <b>{{ strtoupper($acceptor->kontrasepsi?->name) }}</b>,
                dan dengan sukarela memilih untuk dilayani.
            </p>
            <div class="signature">
                <div><b>Yang Memberi Penjelasan</b><br>(Bidan)<div class="line">( {{ $acceptor->consent_witness ?? '____________' }} )</div></div>
                <div><b>Suami / Istri</b><br>(Calon Peserta KB)<div class="line">( {{ $acceptor->patient->name }} )</div></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
