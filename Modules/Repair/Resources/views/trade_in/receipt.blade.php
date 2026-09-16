<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Tukar Tambah - #{{ $transaction->invoice_no ?? $trade_in->id }}</title>
    <link rel="stylesheet" href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            background: #f4f6f9;
            padding: 20px 0;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .receipt-header {
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a2a3a;
            margin-top: 10px;
        }
        .table-details th {
            background-color: #f8f9fa;
            font-weight: 700;
        }
        .declaration-box {
            background: #f9fbfd;
            border: 1px solid #e2e8f0;
            padding: 12px 15px;
            font-size: 12px;
            border-radius: 6px;
            margin: 20px 0;
            line-height: 1.5;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-box {
            text-align: center;
            height: 90px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container no-print text-center" style="margin-bottom: 20px;">
    <button onclick="window.print();" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> Cetak Kuitansi / Berita Acara</button>
    <button onclick="window.close();" class="btn btn-default btn-lg"><i class="fas fa-times"></i> Tutup</button>
</div>

<div class="receipt-container">
    {{-- Header --}}
    <div class="receipt-header">
        <div class="row">
            <div class="col-xs-7">
                <h3 style="margin:0; font-weight: 800; color: #0f172a;">{{ $business->name ?? 'Hassa POS' }}</h3>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #475569;">
                    @if(!empty($transaction->location))
                        {{ $transaction->location->landmark ?? '' }} {{ $transaction->location->city ?? '' }} {{ $transaction->location->state ?? '' }}<br>
                        Telp: {{ $transaction->location->mobile ?? '-' }}
                    @else
                        {{ $business->locations->first()->landmark ?? '' }}<br>
                        Telp: {{ $business->locations->first()->mobile ?? '-' }}
                    @endif
                </p>
            </div>
            <div class="col-xs-5 text-right">
                <div class="receipt-title">KUITANSI TUKAR TAMBAH</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 5px;">
                    <strong>No. Transaksi:</strong> {{ $transaction->invoice_no ?? '-' }}<br>
                    <strong>Tanggal:</strong> {{ @format_date($transaction->transaction_date ?? $trade_in->created_at) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Customer & Officer Info --}}
    <div class="row" style="margin-bottom: 20px; font-size: 13px;">
        <div class="col-xs-6">
            <strong style="color: #334155;">Pihak Pertama (Pelanggan):</strong><br>
            <span style="font-size: 15px; font-weight: bold;">{{ $transaction->contact->name ?? $job_sheet->customer->name ?? 'Pelanggan Walk-In' }}</span><br>
            HP: {{ $transaction->contact->mobile ?? $job_sheet->customer->mobile ?? '-' }}<br>
            Alamat: {{ $transaction->contact->landmark ?? $job_sheet->customer->landmark ?? '-' }}
        </div>
        <div class="col-xs-6 text-right">
            <strong style="color: #334155;">Pihak Kedua (Toko):</strong><br>
            <span style="font-size: 15px; font-weight: bold;">{{ $business->name }}</span><br>
            Petugas: {{ $trade_in->user->user_full_name ?? auth()->user()->user_full_name ?? 'Kasir' }}
        </div>
    </div>

    {{-- Trade-In Item Details Table --}}
    <h4 style="font-weight: 700; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">1. Detail Perangkat Tukar Tambah (Barang Bekas/Second)</h4>
    <table class="table table-bordered table-details" style="font-size: 13px;">
        <thead>
            <tr>
                <th>Tipe / Model Perangkat</th>
                <th>Nomor Seri / IMEI</th>
                <th>Kondisi & Kelengkapan</th>
                <th class="text-right">Nilai Tukar Tambah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $trade_in->model_name }}</strong></td>
                <td>{{ $trade_in->serial_no ?? '-' }}</td>
                <td>{{ $trade_in->condition ?? '-' }}</td>
                <td class="text-right"><strong>@format_currency($trade_in->trade_in_value)</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- New Purchased Items Table (If linked to POS Sale) --}}
    @if(!empty($transaction) && !empty($transaction->sell_lines))
    <h4 style="font-weight: 700; color: #1e293b; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; margin-top: 25px;">2. Detail Barang Baru yang Dibeli</h4>
    <table class="table table-bordered table-details" style="font-size: 13px;">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th>Nama Produk</th>
                <th class="text-center" width="10%">Qty</th>
                <th class="text-right" width="20%">Harga Satuan</th>
                <th class="text-right" width="25%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->sell_lines as $index => $line)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $line->product->name ?? '' }} {{ $line->variations->name != 'DUMMY' ? $line->variations->name : '' }}</td>
                <td class="text-center">{{ @format_quantity($line->quantity) }}</td>
                <td class="text-right">@format_currency($line->unit_price_inc_tax)</td>
                <td class="text-right">@format_currency($line->unit_price_inc_tax * $line->quantity)</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Payment Summary --}}
    <div class="row">
        <div class="col-xs-7">
            <div class="declaration-box">
                <strong>Pernyataan Kepemilikan:</strong><br>
                Pihak Pertama menyatakan secara sadar bahwa unit/perangkat tukar tambah di atas adalah milik sah dan tidak terlibat dalam tindak kejahatan atau sengketa. Hak kepemilikan perangkat resmi berpindah kepada Pihak Kedua sejak berlakunya transaksi ini.
            </div>
        </div>
        <div class="col-xs-5">
            <table class="table table-condensed" style="font-size: 13px;">
                <tr>
                    <td><strong>Total Pembelian:</strong></td>
                    <td class="text-right">@format_currency($transaction->final_total)</td>
                </tr>
                <tr class="text-danger">
                    <td><strong>Potongan Tukar Tambah:</strong></td>
                    <td class="text-right">(-) @format_currency($trade_in->trade_in_value)</td>
                </tr>
                <tr style="font-size: 15px; background: #f8fafc; font-weight: bold;">
                    <td>Net Total Bayar:</td>
                    <td class="text-right" style="color: #059669;">@format_currency($transaction->final_total - $trade_in->trade_in_value)</td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="row signature-section">
        <div class="col-xs-6 text-center">
            <p>Pihak Pertama (Pelanggan),</p>
            <div class="signature-box"></div>
            <p><strong>( {{ $transaction->contact->name ?? $job_sheet->customer->name ?? '...........................' }} )</strong></p>
        </div>
        <div class="col-xs-6 text-center">
            <p>Pihak Kedua (Toko),</p>
            <div class="signature-box"></div>
            <p><strong>( {{ $trade_in->user->user_full_name ?? auth()->user()->user_full_name ?? '...........................' }} )</strong></p>
        </div>
    </div>
</div>

</body>
</html>
