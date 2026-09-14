<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Tukar Tambah - {{ $job_sheet->job_sheet_no ?? 'TT-' . $trade_in->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11pt;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .header-table td { vertical-align: top; }
        .title { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .subtitle { font-size: 11pt; font-weight: bold; margin-bottom: 15px; color: #555; }
        .detail-table th, .detail-table td {
            border: 1px solid #777;
            padding: 6px 8px;
        }
        .detail-table th { background-color: #f2f2f2; }
        .box-summary {
            border: 2px dashed #444;
            padding: 10px;
            background-color: #f9f9f9;
            margin-bottom: 15px;
        }
        .signature-table { margin-top: 30px; }
        .signature-table td { height: 70px; vertical-align: bottom; text-align: center; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="60%">
                <h2 style="margin:0; font-size:14pt;">{{ $trade_in->business->name ?? '' }}</h2>
                @if(!empty($trade_in->location))
                    <div>{{ $trade_in->location->name }}</div>
                    <div>{!! $trade_in->location->location_address !!}</div>
                    @if($trade_in->location->mobile) <div>Telp/WA: {{ $trade_in->location->mobile }}</div> @endif
                @endif
            </td>
            <td width="40%" class="text-right">
                <div class="bold" style="font-size: 12pt;">NO: TT-{{ str_pad($trade_in->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div>Tanggal: {{ @format_date($trade_in->created_at) }}</div>
                @if(!empty($job_sheet))
                    <div>No. Job Sheet: {{ $job_sheet->job_sheet_no }}</div>
                @endif
            </td>
        </tr>
    </table>

    <hr style="border: 0.5px solid #ccc; margin-bottom: 15px;">

    <div class="text-center title">BERITA ACARA & KUITANSI TUKAR TAMBAH</div>
    <div class="text-center subtitle">Penerimaan Perangkat Bekas (Trade-In)</div>

    <p>Telah diterima perangkat bekas dari pelanggan dengan rincian sebagai berikut:</p>

    <table>
        <tr>
            <td width="20%" class="bold">Nama Pelanggan</td>
            <td width="30%">: {{ $trade_in->customer->name ?? '-' }}</td>
            <td width="20%" class="bold">No. Telepon/HP</td>
            <td width="30%">: {{ $trade_in->customer->mobile ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bold">Alamat</td>
            <td colspan="3">: {{ $trade_in->customer->contact_address ?? '-' }}</td>
        </tr>
    </table>

    <div class="bold" style="margin-bottom: 5px;">Rincian Perangkat Bekas Yang Ditukarkan:</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Nama Perangkat</th>
                <th>Merek / Model</th>
                <th>No. Seri / IMEI</th>
                <th>Kondisi Perangkat</th>
                <th>Nilai Tukar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $trade_in->device_name }}</td>
                <td>{{ $trade_in->brand }} {{ $trade_in->model ? '/ ' . $trade_in->model : '' }}</td>
                <td>{{ $trade_in->serial_no ?? '-' }}</td>
                <td>{{ $trade_in->condition ?? 'Normal' }}</td>
                <td class="text-right bold">{{ @num_format($trade_in->trade_in_value) }}</td>
            </tr>
        </tbody>
    </table>

    @if($trade_in->notes)
        <p><strong>Catatan/Kelengkapan:</strong> {{ $trade_in->notes }}</p>
    @endif

    <div class="box-summary text-center">
        <div style="font-size: 10pt;">TOTAL POTONGAN TUKAR TAMBAH (TRADE-IN DISCOUNT):</div>
        <div style="font-size: 18pt; font-weight: bold; color: #1b5e20;">Rp {{ @num_format($trade_in->trade_in_value) }}</div>
    </div>

    <div class="bold" style="margin-top: 10px;">Syarat & Ketentuan Tukar Tambah:</div>
    <ol style="font-size: 9pt; padding-left: 18px; color: #555;">
        <li>Pelanggan menyatakan bahwa perangkat bekas yang ditukarkan adalah milik sah pelanggan dan tidak tersangkut tindak pidana/hukum.</li>
        <li>Nilai tukar tambah yang telah disepakati akan langsung digunakan sebagai potongan pembayaran atas biaya perbaikan / pembelian unit baru.</li>
        <li>Perangkat bekas yang telah ditukarkan dan ditandatangani berita acara ini tidak dapat ditarik atau dibatalkan kembali.</li>
    </ol>

    <table class="signature-table">
        <tr>
            <td width="50%">
                <div>Pelanggan / Penyerah Perangkat,</div>
                <br><br><br>
                <div class="bold">( {{ $trade_in->customer->name ?? 'Pelanggan' }} )</div>
            </td>
            <td width="50%">
                <div>Penerima / Petugas Toko,</div>
                <br><br><br>
                <div class="bold">( {{ auth()->user()->user_full_name ?? 'Petugas Toko' }} )</div>
            </td>
        </tr>
    </table>
</body>
</html>
