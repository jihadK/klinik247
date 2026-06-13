@php
    // Override helper: ambil dari form POST (override), fallback ke DB
    $o = $override ?? [];
    $get = fn (string $key, $fallback = null) => $o[$key] ?? $fallback;

    $site = $delivery->site;

    // Kop surat (editable)
    $kopName     = $get('kop_name',     $site?->name);
    $kopSubtitle = $get('kop_subtitle', $site?->letterhead_subtitle ?? 'Praktik Mandiri Bidan (PMB)');
    $kopAddress  = $get('kop_address',  $site?->address);
    $kopPhone    = $get('kop_phone',    $site?->phone);
    $kopEmail    = $get('kop_email',    $site?->email);

    // Tujuan rujukan
    $rujukanKe     = $get('rujukan_ke',     $delivery->rujukan_ke);
    $rujukanAlamat = $get('rujukan_alamat', null);
    $rujukanAlasan = $get('rujukan_alasan', $delivery->rujukan_alasan ?? 'Berdasarkan hasil penapisan ibu bersalin, ditemukan faktor risiko yang memerlukan penanganan dengan fasilitas dan kewenangan yang lebih lengkap.');

    // Faktor risiko (multi-line text)
    $faktorRisiko = $get('faktor_risiko', null);
    $tindakanPraRujuk = $get('tindakan_pra_rujuk', null);

    // Penandatangan
    $tempatTanggal  = $get('tempat_tanggal',  ($site?->letterhead_city ?? $site?->city ?? 'Lamongan') . ', ' . now()->isoFormat('D MMMM YYYY'));
    $signerName     = $get('signer_name',     optional($delivery->servedBy)->full_name ?? $site?->letterhead_director);
    $signerSipb     = $get('signer_sipb',     $site?->letterhead_sipb);
    $signerPosition = $get('signer_position', 'Bidan Penanggung Jawab');
    $noSurat        = $get('no_surat',        $delivery->no_persalinan . '/RUJ/' . now()->format('m/Y'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Rujukan — {{ $delivery->no_persalinan }}</title>
    <style>
        @page { size: A4; margin: 15mm 18mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; background: #f3f3f3; margin: 0; }
        .toolbar { position: sticky; top: 0; background: #fff; padding: 12px; text-align: center; border-bottom: 1px solid #ddd; z-index: 10; }
        .toolbar button, .toolbar a { padding: 8px 16px; border: 0; border-radius: 4px; cursor: pointer; text-decoration: none; margin: 0 4px; font-family: Arial, sans-serif; }
        .btn-print { background: #009ef7; color: #fff; }
        .btn-back { background: #e4e6ef; color: #181c32; }

        .surat {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            background: #fff;
            margin: 20px auto;
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .kop {
            border-bottom: 3px double #000;
            padding-bottom: 10mm;
            margin-bottom: 8mm;
            text-align: center;
        }
        .kop .nama { font-size: 16pt; font-weight: bold; letter-spacing: 1px; }
        .kop .sub { font-size: 11pt; font-style: italic; }
        .kop .alamat { font-size: 10pt; margin-top: 2mm; }

        .title-block { text-align: center; margin: 6mm 0 8mm; }
        .title-block h2 { font-size: 14pt; text-decoration: underline; margin-bottom: 2mm; letter-spacing: 1px; }
        .title-block .no-surat { font-size: 11pt; }

        .salutation { margin: 8mm 0 4mm; font-size: 12pt; }
        .salutation .yth { margin-bottom: 1mm; }

        .body { text-align: justify; margin-bottom: 6mm; }

        table.data { width: 100%; margin: 4mm 0; border-collapse: collapse; }
        table.data td { padding: 2mm 3mm; vertical-align: top; }
        table.data td:first-child { width: 40%; }
        table.data td:nth-child(2) { width: 5%; }

        .penapisan-box {
            border: 2px solid #c2185b;
            padding: 4mm;
            margin: 4mm 0;
            background: #fef2f2;
        }
        .penapisan-box h4 { margin-bottom: 3mm; color: #c2185b; }
        .penapisan-box ul { padding-left: 6mm; margin: 0; }
        .penapisan-box li { margin-bottom: 1mm; }

        .ttd-block { margin-top: 15mm; display: flex; justify-content: flex-end; }
        .ttd-box { width: 70mm; text-align: center; }
        .ttd-box .place-date { margin-bottom: 2mm; }
        .ttd-box .position { font-weight: bold; }
        .ttd-box .signature { margin: 18mm 0 1mm; }
        .ttd-box .name { font-weight: bold; text-decoration: underline; }

        .footer-note { margin-top: 8mm; font-size: 10pt; font-style: italic; color: #555; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .surat { box-shadow: none; margin: 0; min-height: 0; padding: 0; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <a href="{{ route('admin.inc.show', $delivery) }}" class="btn-back">← Kembali</a>
    <button onclick="window.print()" class="btn-print">🖨 Cetak Surat</button>
</div>

<div class="surat">
    {{-- ===== KOP SURAT ===== --}}
    @if($site && $site->kop_image_url)
        {{-- Kalau ada kop image upload → pakai itu sebagai header --}}
        <div class="kop" style="border: 0; padding: 0;">
            <img src="{{ asset('storage/'.$site->kop_image_url) }}" alt="Kop Surat {{ $site->name }}" style="width:100%; max-height:45mm; object-fit:contain;">
        </div>
    @else
        {{-- Fallback: kop text-only --}}
        <div class="kop">
            <div class="nama">{{ strtoupper($kopName ?? 'PRAKTIK MANDIRI BIDAN') }}</div>
            @if($kopSubtitle)<div class="sub">{{ $kopSubtitle }}</div>@endif
            <div class="alamat">{{ $kopAddress }}</div>
            <div class="alamat">Telp: {{ $kopPhone ?? '-' }} · Email: {{ $kopEmail ?? '-' }}</div>
        </div>
    @endif

    {{-- ===== JUDUL ===== --}}
    <div class="title-block">
        <h2>SURAT RUJUKAN PASIEN</h2>
        <div class="no-surat">Nomor: {{ $noSurat }}</div>
    </div>

    {{-- ===== SALUTATION ===== --}}
    <div class="salutation">
        <div class="yth">Kepada Yth,</div>
        <div><b>Direktur / Kepala {{ $rujukanKe ?: '....................................' }}</b></div>
        @if($rujukanAlamat)<div>{{ $rujukanAlamat }}</div>@endif
        <div>di tempat</div>
    </div>

    {{-- ===== ISI ===== --}}
    <div class="body">
        Dengan hormat,<br><br>
        Bersama dengan surat ini, dengan keterbatasan fasilitas dan kewenangan pelayanan di tempat kami, kami merujuk pasien dengan data berikut untuk mendapatkan pemeriksaan dan penatalaksanaan lebih lanjut:
    </div>

    <table class="data">
        <tr><td>Nama</td><td>:</td><td><b>{{ $delivery->patient->name }}</b></td></tr>
        <tr><td>No. RM / Persalinan</td><td>:</td><td>{{ $delivery->patient->no_rm }} / <b>{{ $delivery->no_persalinan }}</b></td></tr>
        <tr><td>NIK</td><td>:</td><td>{{ $delivery->patient->nik ?? '-' }}</td></tr>
        <tr><td>BPJS / Pembiayaan</td><td>:</td><td>{{ $delivery->patient->no_bpjs ?? '-' }} · {{ optional($delivery->patient->payerType)->name ?? 'Umum' }}</td></tr>
        <tr><td>TTL / Umur</td><td>:</td><td>{{ $delivery->patient->birth_place }}, {{ optional($delivery->patient->birth_date)->format('d/m/Y') }} ({{ $delivery->patient->age }})</td></tr>
        <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $delivery->patient->gender_label }}</td></tr>
        <tr><td>Alamat</td><td>:</td><td>{{ $delivery->patient->full_address }}</td></tr>
        <tr><td>No. HP</td><td>:</td><td>{{ $delivery->patient->phone ?? '-' }}</td></tr>
        <tr><td colspan="3"><b>Data Kehamilan & Persalinan:</b></td></tr>
        <tr><td>No. Kartu Ibu Hamil</td><td>:</td><td>{{ $delivery->pregnancy?->no_kartu_hamil ?? '-' }} · GPA <b>{{ $delivery->pregnancy?->gpa_label ?? '-' }}</b></td></tr>
        <tr><td>HPHT / HPL</td><td>:</td><td>{{ optional($delivery->pregnancy?->hpht)->format('d/m/Y') }} / {{ optional($delivery->pregnancy?->hpl)->format('d/m/Y') }}</td></tr>
        <tr><td>UK Saat Persalinan</td><td>:</td><td>{{ $delivery->pregnancy?->uk_sekarang ?? '-' }} minggu</td></tr>
        <tr><td>Masuk PMB</td><td>:</td><td>{{ optional($delivery->masuk_at)->isoFormat('D MMMM YYYY HH:mm') }}</td></tr>
    </table>

    {{-- ===== PEMERIKSAAN SAAT MASUK ===== --}}
    <p><b>Hasil Pemeriksaan Saat Masuk PMB:</b></p>
    <table class="data">
        <tr><td>Tekanan Darah</td><td>:</td><td>{{ $delivery->masuk_ttv_td ?? '-' }} mmHg</td></tr>
        <tr><td>Nadi / Suhu / RR</td><td>:</td><td>{{ $delivery->masuk_ttv_nadi ?? '-' }} ×/mnt · {{ $delivery->masuk_ttv_suhu ?? '-' }} °C · {{ $delivery->masuk_ttv_rr ?? '-' }} ×/mnt</td></tr>
        <tr><td>DJJ / His / VT</td><td>:</td><td>{{ $delivery->masuk_djj ?? '-' }} ×/mnt · {{ $delivery->masuk_his_per_10 ?? '-' }} ×/10' · Pembukaan {{ $delivery->masuk_vt_pembukaan ?? '-' }} cm</td></tr>
        <tr><td>Ketuban</td><td>:</td><td>{{ ucfirst($delivery->masuk_ketuban ?? '-') }}</td></tr>
        <tr><td>Keluhan</td><td>:</td><td>{{ $delivery->masuk_keluhan ?? '-' }}</td></tr>
    </table>

    {{-- ===== FAKTOR RISIKO PENAPISAN ===== --}}
    @if($faktorRisiko)
        <div class="penapisan-box">
            <h4>⚠ FAKTOR RISIKO TERDETEKSI ({{ $delivery->penapisan_skor }} / 18)</h4>
            <div style="white-space: pre-line;">{{ $faktorRisiko }}</div>
        </div>
    @endif

    {{-- ===== ALASAN RUJUKAN ===== --}}
    <p><b>Alasan Rujukan:</b><br>{{ $rujukanAlasan }}</p>

    {{-- ===== TINDAKAN YANG SUDAH DILAKUKAN ===== --}}
    @if($tindakanPraRujuk)
        <p><b>Tindakan Pra-Rujuk yang Sudah Dilakukan:</b></p>
        <div style="white-space: pre-line; padding-left: 6mm;">{{ $tindakanPraRujuk }}</div>
    @endif

    {{-- ===== PERMOHONAN ===== --}}
    <p style="margin-top: 4mm;">Demikian surat rujukan ini kami buat. Mohon bantuan untuk pemeriksaan dan penatalaksanaan selanjutnya. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="ttd-block">
        <div class="ttd-box">
            <div class="place-date">{{ $tempatTanggal }}</div>
            <div class="position">{{ $signerPosition }},</div>
            <div class="signature"></div>
            <div class="name">{{ strtoupper($signerName ?? '____________________') }}</div>
            @if($signerSipb)
                <div style="font-size:10pt;">NIP/SIPB: {{ $signerSipb }}</div>
            @endif
        </div>
    </div>

    <div class="footer-note">
        * Lembar 1: untuk RS Rujukan · Lembar 2: arsip PMB · Lembar 3: untuk keluarga pasien
    </div>
</div>

<script>
    // Auto-trigger print dialog opsional (uncomment kalau mau langsung print)
    // window.addEventListener('load', () => setTimeout(() => window.print(), 500));
</script>
</body>
</html>
