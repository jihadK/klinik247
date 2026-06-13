<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Asuhan Persalinan — {{ $delivery->no_persalinan }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f3f3f3; }
        .toolbar { background: #fff; padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        .toolbar button, .toolbar a { padding: 8px 16px; border: 0; border-radius: 4px; cursor: pointer; text-decoration: none; margin: 0 4px; }
        .btn-print { background: #009ef7; color: #fff; }
        .btn-back { background: #e4e6ef; color: #181c32; }
        .doc { width: 210mm; padding: 10mm; background: #fff; margin: 20px auto; font-size: 9pt; box-shadow: 0 4px 16px rgba(0,0,0,.1); }
        .doc h2 { text-align: center; }
        .doc h3 { background: #c2185b; color: #fff; padding: 2mm 3mm; margin: 4mm 0 2mm; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        table.bordered th, table.bordered td { border: 1px solid #999; padding: 2mm; text-align: left; }
        table.bordered th { background: #f3e5f5; }
        .penapisan-yes { color: #c2185b; font-weight: bold; }
        @media print { body { background: #fff; } .toolbar { display: none; } .doc { box-shadow: none; margin: 0; } }
    </style>
</head>
<body>
<div class="toolbar">
    <a href="{{ route('admin.inc.show', $delivery) }}" class="btn-back">← Kembali</a>
    <button onclick="window.print()" class="btn-print">🖨 Cetak</button>
</div>
<div class="doc">
    <h2>ASUHAN PERSALINAN</h2>
    <p style="text-align:center;">{{ strtoupper($delivery->site->name ?? 'Klinik') }} · {{ $delivery->site->address ?? '' }}</p>
    <p style="text-align:center;"><b>No: {{ $delivery->no_persalinan }}</b></p>

    <h3>A. Identitas Pasien</h3>
    <table>
        <tr><td style="width:30%">Nama</td><td>{{ $delivery->patient->name }}</td></tr>
        <tr><td>No. RM / Kartu Hamil</td><td>{{ $delivery->patient->no_rm }} / {{ $delivery->pregnancy?->no_kartu_hamil }}</td></tr>
        <tr><td>GPA</td><td>{{ $delivery->pregnancy?->gpa_label }}</td></tr>
        <tr><td>HPHT / HPL</td><td>{{ optional($delivery->pregnancy?->hpht)->format('d/m/Y') }} / {{ optional($delivery->pregnancy?->hpl)->format('d/m/Y') }}</td></tr>
        <tr><td>Masuk PMB</td><td>{{ optional($delivery->masuk_at)->isoFormat('D MMMM YYYY HH:mm') }}</td></tr>
    </table>

    <h3>B. Penapisan Ibu Bersalin (Skor: {{ $delivery->penapisan_skor }}/18)</h3>
    <table class="bordered">
        <thead><tr><th>No</th><th>Faktor Risiko</th><th width="20%">Status</th></tr></thead>
        <tbody>
            @foreach(\App\Models\Delivery::penapisanItems() as $field => $label)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $label }}</td>
                    <td class="{{ $delivery->{$field} ? 'penapisan-yes' : '' }}">{{ $delivery->{$field} ? '✓ YA' : 'Tidak' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><b>Keputusan:</b> {{ \App\Models\Delivery::keputusanPenapisanOptions()[$delivery->penapisan_keputusan] ?? '-' }}</p>

    <h3>C. Pemeriksaan Saat Masuk</h3>
    <table>
        <tr><td style="width:30%">TD / Nadi / Suhu / RR</td><td>{{ $delivery->masuk_ttv_td }} / {{ $delivery->masuk_ttv_nadi }} / {{ $delivery->masuk_ttv_suhu }} / {{ $delivery->masuk_ttv_rr }}</td></tr>
        <tr><td>DJJ / His /10' / VT (cm)</td><td>{{ $delivery->masuk_djj }} / {{ $delivery->masuk_his_per_10 }} / {{ $delivery->masuk_vt_pembukaan }}</td></tr>
        <tr><td>Ketuban</td><td>{{ ucfirst($delivery->masuk_ketuban ?? '-') }}</td></tr>
        <tr><td>Keluhan</td><td>{{ $delivery->masuk_keluhan }}</td></tr>
    </table>

    <h3>D. SOAP Timeline ({{ $delivery->soaps->count() }} observasi)</h3>
    <table class="bordered">
        <thead><tr><th>Tgl/Jam</th><th>Kala</th><th>S</th><th>O</th><th>A</th><th>P</th></tr></thead>
        <tbody>
        @foreach($delivery->soaps as $s)
            <tr>
                <td>{{ $s->observed_at->format('d/m H:i') }}</td>
                <td>{{ $s->kala_label }}</td>
                <td>{{ $s->subjective }}</td>
                <td>
                    @if($s->ttv_td)TD: {{ $s->ttv_td }}<br>@endif
                    @if($s->djj)DJJ: {{ $s->djj }}<br>@endif
                    @if($s->his_per_10)His: {{ $s->his_per_10 }}x/10'<br>@endif
                    @if($s->vt_pembukaan)VT: {{ $s->vt_pembukaan }} cm<br>@endif
                    @if($s->ketuban)Ketuban: {{ $s->ketuban }}@endif
                </td>
                <td>{{ $s->assessment }}</td>
                <td>{{ $s->plan }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h3>E. 4 Kala Persalinan</h3>
    <table>
        <tr><td style="width:30%">Kala I</td><td>{{ optional($delivery->kala1_mulai_at)->format('H:i') }} → {{ optional($delivery->kala1_selesai_at)->format('H:i') }} ({{ $delivery->kala1_duration }} jam)</td></tr>
        <tr><td>Kala II — Bayi Lahir</td><td>{{ optional($delivery->bayi_lahir_at)->format('d/m H:i') }} · {{ $delivery->bayi_jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} · BB {{ $delivery->bayi_bb_gram }} g · PB {{ $delivery->bayi_pb_cm }} cm · APGAR {{ $delivery->bayi_apgar_1 }}/{{ $delivery->bayi_apgar_5 }}</td></tr>
        <tr><td>Kala III — Plasenta</td><td>{{ optional($delivery->plasenta_lahir_at)->format('H:i') }} · {{ $delivery->plasenta_lahir_spontan ? 'Spontan' : 'Manual' }}</td></tr>
        <tr><td>Kala IV</td><td>Laserasi: {{ \App\Models\Delivery::laserasiOptions()[$delivery->perineum_laserasi] ?? '-' }} · Heckting: {{ $delivery->heckting_dilakukan ? 'Ya' : 'Tidak' }} · Perdarahan: {{ $delivery->perdarahan_ml }} ml</td></tr>
    </table>

    <h3>F. Terapi Pasca Persalinan</h3>
    <table>
        <tr><td style="width:30%">Ibu</td><td>
            @if($delivery->terapi_amoxicillin)✓ Amoxicillin @endif
            @if($delivery->terapi_asam_mef)✓ As. Mef @endif
            @if($delivery->terapi_fe)✓ Fe @endif
            @if($delivery->terapi_metergin)✓ Metergin @endif
            {{ $delivery->terapi_ibu_dosis_notes }}
        </td></tr>
        <tr><td>Bayi</td><td>
            @if($delivery->bayi_injeksi_neo_k)✓ Vit K1 @endif
            @if($delivery->bayi_salep_mata)✓ Salep Mata @endif
            @if($delivery->bayi_imunisasi_hb0)✓ HB-0 @endif
        </td></tr>
        <tr><td>Kondisi Ibu / Bayi</td><td>{{ \App\Models\Delivery::ibuKondisiOptions()[$delivery->ibu_kondisi] ?? '-' }} / {{ \App\Models\Delivery::bayiKondisiOptions()[$delivery->bayi_kondisi] ?? '-' }}</td></tr>
    </table>

    <div style="margin-top: 15mm; text-align: right;">
        <p>Bidan Penanggung Jawab,</p>
        <br><br><br>
        <p><b>( {{ optional($delivery->servedBy)->full_name ?? '...........................' }} )</b></p>
    </div>
</div>
</body>
</html>
