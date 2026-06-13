<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Pasien — {{ $patient->no_rm }} — {{ $patient->name }}</title>
    <style>
        /* ===== Print: kartu ukuran A6 (105 × 148 mm), landscape kartu nama 85.6 × 54 mm) ===== */
        @page { size: 105mm 75mm; margin: 0; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        html, body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #ddd; }

        .toolbar {
            position: sticky; top: 0; background: #fff; padding: 12px 20px;
            border-bottom: 1px solid #ddd; display: flex; gap: 8px; justify-content: center; z-index: 10;
        }
        .toolbar button, .toolbar a {
            padding: 8px 16px; border: 0; border-radius: 4px; cursor: pointer; font-size: 14px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-print { background: #009ef7; color: #fff; }
        .btn-back  { background: #e4e6ef; color: #181c32; }

        .kartu-wrapper { padding: 30px; display: flex; justify-content: center; }

        .kartu {
            width: 105mm; height: 75mm; padding: 6mm 8mm;
            background: linear-gradient(135deg, #009ef7 0%, #0086d6 100%);
            color: #fff; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,.18);
            position: relative; overflow: hidden;
        }
        .kartu::before {
            content: ''; position: absolute; top: -20mm; right: -20mm;
            width: 50mm; height: 50mm; background: rgba(255,255,255,.08); border-radius: 50%;
        }
        .kartu::after {
            content: ''; position: absolute; bottom: -15mm; left: -15mm;
            width: 40mm; height: 40mm; background: rgba(255,255,255,.06); border-radius: 50%;
        }

        .head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4mm; position: relative; z-index: 2; }
        .clinic { font-size: 9pt; line-height: 1.2; }
        .clinic .name { font-weight: 700; font-size: 10pt; }
        .clinic .sub { opacity: .85; font-size: 7.5pt; }
        .label { font-size: 7pt; opacity: .8; text-transform: uppercase; letter-spacing: 1px; }

        .body { position: relative; z-index: 2; margin-top: 1mm; }
        .body .name { font-size: 12pt; font-weight: 700; margin-bottom: 1mm; }
        .body .info { font-size: 8pt; opacity: .92; line-height: 1.4; }

        .no-rm {
            font-size: 14pt; font-weight: 800; letter-spacing: 1px;
            margin-top: 1mm; font-family: 'Courier New', monospace;
        }

        .qr-wrap {
            position: absolute; right: 8mm; bottom: 12mm;
            background: #fff; padding: 1.5mm; border-radius: 2mm;
            box-shadow: 0 1px 4px rgba(0,0,0,.2); z-index: 3;
        }
        .qr-wrap canvas, .qr-wrap img { display: block; width: 22mm !important; height: 22mm !important; }
        .qr-label {
            position: absolute; right: 8mm; bottom: 6mm;
            font-size: 6pt; opacity: .85; text-align: center; width: 22mm;
            letter-spacing: 0.5px; z-index: 3;
        }

        .footer {
            position: absolute; bottom: 5mm; left: 8mm; right: 36mm;
            display: flex; justify-content: space-between; align-items: end;
            font-size: 7pt; opacity: .85; z-index: 2;
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .kartu-wrapper { padding: 0; }
            .kartu { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <a href="{{ route('admin.patients.show', $patient) }}" class="btn-back">← Kembali</a>
        <button onclick="window.print()" class="btn-print">🖨 Cetak Sekarang</button>
    </div>

    <div class="kartu-wrapper">
        <div class="kartu">
            <div class="head">
                <div class="clinic">
                    <div class="name">{{ $patient->site->name ?? 'Klinik247' }}</div>
                    <div class="sub">{{ $patient->site->address ?? '' }}</div>
                    <div class="sub">{{ $patient->site->phone ?? '' }}</div>
                </div>
                <div style="text-align:right;">
                    <div class="label">Kartu Pasien</div>
                </div>
            </div>

            <div class="body">
                <div class="label">Nama Pasien</div>
                <div class="name">{{ strtoupper($patient->name) }}</div>
                <div class="info">
                    {{ $patient->birth_place }}, {{ optional($patient->birth_date)->isoFormat('D MMM YYYY') }}
                    · {{ $patient->gender_label }}
                </div>
                <div class="no-rm">{{ $patient->no_rm }}</div>
            </div>

            <div class="footer">
                <div>
                    {{ optional($patient->payerType)->name ?? 'UMUM' }}
                    @if($patient->no_bpjs)<br>BPJS: {{ $patient->no_bpjs }}@endif
                </div>
                <div>{{ $patient->village?->name ?? '' }}</div>
            </div>

            {{-- QR Code: encode no_rm — bisa di-scan untuk lookup pasien saat kunjungan --}}
            <div class="qr-wrap" id="qrcode"></div>
            <div class="qr-label">SCAN ME</div>
        </div>
    </div>

    {{-- QR generator client-side (no install needed) --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qrcode'), {
            text: @json($patient->no_rm),
            width: 200,
            height: 200,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });

        // Auto-trigger print dialog setelah render (uncomment kalau mau langsung print)
        // window.addEventListener('load', () => setTimeout(() => window.print(), 500));
    </script>
</body>
</html>
