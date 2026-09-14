<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara & Kuitansi Tukar Tambah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #666;
        }
        .table-info {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .table-info td {
            padding: 5px;
            vertical-align: top;
        }
        .table-details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .table-details th, .table-details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table-details th {
            background-color: #f5f5f5;
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            height: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $trade_in->business->name }}</h2>
        @if(!empty($trade_in->location))
            <p>{{ $trade_in->location->name }} - {!! $trade_in->location->location_address !!}</p>
        @endif
        <h3 style="margin-top: 15px; margin-bottom: 0;">BERITA ACARA & KUITANSI TUKAR TAMBAH</h3>
    </div>

    <table class="table-info">
        <tr>
            <td width="18%"><strong>Tanggal Transaksi</strong></td>
            <td width="2%">:</td>
            <td width="30%">{{ @format_datetime($trade_in->created_at) }}</td>
            <td width="18%"><strong>No. Ref Job Sheet</strong></td>
            <td width="2%">:</td>
            <td width="30%">{{ $trade_in->jobSheet->job_sheet_no ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nama Pelanggan</strong></td>
            <td>:</td>
            <td>{{ $trade_in->jobSheet->customer->name ?? ($trade_in->transaction->contact->name ?? '-') }}</td>
            <td><strong>No. HP Pelanggan</strong></td>
            <td>:</td>
            <td>{{ $trade_in->jobSheet->customer->mobile ?? ($trade_in->transaction->contact->mobile ?? '-') }}</td>
        </tr>
    </table>

    <h4>Detail Perangkat Bekas Ditukarkan:</h4>
    <table class="table-details">
        <thead>
            <tr>
                <th>Item / Nama Perangkat</th>
                <th>Brand / Merek</th>
                <th>SN / IMEI</th>
                <th>Kondisi / Kelayakan</th>
                <th>Nilai Tukar Tambah (Potongan)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $trade_in->device_name }}</td>
                <td>{{ $trade_in->brand->name ?? '-' }}</td>
                <td>{{ $trade_in->serial_no ?? '-' }}</td>
                <td>{{ $trade_in->condition_notes ?? '-' }}</td>
                <td><strong>Rp {{ @num_format($trade_in->valuation_amount) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <p style="font-size: 11px; color: #555; font-style: italic;">
        * Dengan menandatangani berita acara ini, pelanggan menyatakan bahwa perangkat bekas yang ditukarkan adalah milik sah pribadi dan bukan barang hasil kejahatan.
    </p>

    <table class="signatures">
        <tr>
            <td>
                Pelanggan,<br><br><br><br>
                ( _____________________ )
            </td>
            <td>
                Petugas Toko / Teknisi,<br><br><br><br>
                ( _____________________ )
            </td>
        </tr>
    </table>

    <script type="text/javascript">
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
