<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Alur Manufaktur: Pembelian -> Manufaktur -> Penjualan</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Header styling */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 10pt;
            color: #475569;
            margin-top: 4px;
        }
        .meta-badge {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8.5pt;
            font-weight: bold;
            display: inline-block;
        }

        /* Section Headings */
        h2.section-title {
            font-size: 11pt;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 4px solid #2563eb;
            margin-top: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        h3.subsection-title {
            font-size: 10pt;
            color: #1e40af;
            margin-top: 10px;
            margin-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 2px;
        }

        /* Flowchart Diagram Styles */
        .flow-container {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .flow-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            background-color: #ffffff;
        }
        .flow-box-header {
            font-weight: bold;
            font-size: 10.5pt;
            padding: 4px 8px;
            border-radius: 4px;
            color: #ffffff;
            margin-bottom: 6px;
        }
        .bg-purchase { background-color: #0d9488; }
        .bg-mfg { background-color: #2563eb; }
        .bg-sales { background-color: #16a34a; }

        .flow-desc {
            font-size: 8.5pt;
            color: #334155;
            line-height: 1.3;
        }
        .arrow-td {
            text-align: center;
            vertical-align: middle;
            font-size: 16pt;
            font-weight: bold;
            color: #64748b;
            padding: 0 5px;
        }

        /* Cards and Info Boxes */
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .step-block {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .route-path {
            font-family: monospace;
            background-color: #e2e8f0;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8.5pt;
        }

        /* Step List */
        ol.step-list {
            margin: 0;
            padding-left: 20px;
        }
        ol.step-list li {
            margin-bottom: 6px;
            line-height: 1.4;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #334155;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Status & Accounting Badges */
        .badge-debit {
            background-color: #dcfce7;
            color: #15803d;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8pt;
        }
        .badge-credit {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8pt;
        }

        .page-break {
            page-break-before: always;
        }

        .footer-note {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="header-title">PANDUAN ALUR MANUFAKTUR</div>
                <div class="header-subtitle">Integrasi Alur Operasional: Pembelian &rarr; Manufaktur &rarr; Penjualan Kasir POS</div>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: top;">
                <span class="meta-badge">DOKUMEN PANDUAN SISTEM</span>
                <div style="font-size: 8pt; color: #64748b; margin-top: 5px;">
                    Tanggal: {{ date('d F Y') }}<br>
                    Modul: Manufaktur & Akuntansi
                </div>
            </td>
        </tr>
    </table>

    <!-- Executive Summary / Intro -->
    <div class="info-card">
        <strong>Ringkasan Alur Kerja:</strong><br>
        Dokumen ini menjelaskan alur kerja lengkap operasional manufaktur pada sistem. Proses dimulai dari <strong>Pembelian Bahan Baku</strong> dari pemasok, dilanjutkan ke <strong>Proses Manufaktur</strong> (Pembuatan Resep, Estimasi Produksi, & Pengolahan Bahan menjadi Barang Jadi), hingga <strong>Penjualan Produk Akhir</strong> melalui Kasir POS. Seluruh transaksi secara otomatis memutakhirkan stok barang dan jurnal akuntansi double-entry.
    </div>

    <!-- Visual Diagram Flowchart -->
    <h2 class="section-title">1. DIAGRAM ALUR PROSES TERINTEGRASI</h2>
    <table class="flow-container">
        <tr>
            <!-- Step 1: Pembelian -->
            <td style="width: 30%; vertical-align: top;">
                <div class="flow-box">
                    <div class="flow-box-header bg-purchase">1. PEMBELIAN</div>
                    <div class="flow-desc">
                        • Beli Bahan Baku dari Pemasok<br>
                        • Masuk Stok Bahan Baku<br>
                        • Catat Utang / Pengeluaran Kas
                    </div>
                </div>
            </td>
            <td class="arrow-td" style="width: 5%;">&rarr;</td>
            <!-- Step 2: Manufaktur -->
            <td style="width: 30%; vertical-align: top;">
                <div class="flow-box">
                    <div class="flow-box-header bg-mfg">2. MANUFAKTUR</div>
                    <div class="flow-desc">
                        • Buat Resep Produk (BOM)<br>
                        • Map Akun Otomatis<br>
                        • Input Produksi (Kurangi Bahan Baku & Tambah Barang Jadi)<br>
                        • Sinkronisasi Jurnal
                    </div>
                </div>
            </td>
            <td class="arrow-td" style="width: 5%;">&rarr;</td>
            <!-- Step 3: Penjualan -->
            <td style="width: 30%; vertical-align: top;">
                <div class="flow-box">
                    <div class="flow-box-header bg-sales">3. PENJUALAN (POS)</div>
                    <div class="flow-desc">
                        • Transaksi Penjualan Barang Jadi<br>
                        • Potong Stok Barang Jadi<br>
                        • Masuk Kas / Bank<br>
                        • Pengakuan Pendapatan & HPP
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Detail Step-by-Step -->
    <h2 class="section-title">2. DETAIL TAHAP DEMI TAHAP</h2>

    <!-- TAHAP 1 -->
    <h3 class="subsection-title">TAHAP 1: Pembelian Bahan Baku (Raw Material Purchase)</h3>
    <p style="margin-top: 4px; margin-bottom: 6px;">
        Sebelum proses manufaktur dimulai, stok bahan baku (Raw Materials) harus didaftarkan dan dibeli dari pemasok.
    </p>
    <div class="info-card">
        <strong>Navigasi Menu:</strong> <span class="route-path">Pembelian &gt; Tambah Pembelian</span> (URL: <code>/purchases/create</code>)
    </div>
    <ol class="step-list">
        <li><strong>Pilih Pemasok & Lokasi Bisnis:</strong> Pilih nama pemasok dan lokasi gudang/bisnis tempat bahan baku akan diterima.</li>
        <li><strong>Input Item Bahan Baku:</strong> Masukkan item produk berjenis bahan baku, tentukan jumlah (quantity), dan harga beli per unit.</li>
        <li><strong>Biaya Tambahan / Pajak (Opsional):</strong> Masukkan biaya pengiriman atau pajak jika ada.</li>
        <li><strong>Simpan & Pelunasan:</strong> Pilih status pembayaran (Lunas, Sebagian, atau Jatuh Tempo).</li>
    </ol>
    <p><strong>Dampak Sistem Otomatis:</strong></p>
    <ul style="margin-top: 2px; margin-bottom: 10px; padding-left: 20px;">
        <li>Stok produk bahan baku bertambah di lokasi bisnis yang dipilih.</li>
        <li>Jurnal Akuntansi Otomatis: <span class="badge-debit">Debet</span> Persediaan Bahan Baku | <span class="badge-credit">Kredit</span> Kas/Bank atau Utang Usaha.</li>
    </ul>

    <!-- TAHAP 2 -->
    <h3 class="subsection-title">TAHAP 2: Pengaturan Akun & Formulansi Resep (Recipe / Bill of Materials)</h3>
    <p style="margin-top: 4px; margin-bottom: 6px;">
        Resep menentukan komposisi bahan baku yang dibutuhkan untuk menghasilkan 1 unit produk jadi, beserta estimasi biaya produksi.
    </p>
    <div class="info-card">
        <strong>Navigasi Menu:</strong> <span class="route-path">Manufaktur &gt; Pengaturan</span> &amp; <span class="route-path">Manufaktur &gt; Resep</span>
    </div>
    <ol class="step-list">
        <li><strong>Auto Map Akun Manufaktur (Satu Kali Setup):</strong>
            <br>Buka menu <em>Manufaktur &gt; Pengaturan</em>. Klik tombol <strong>"Auto Map Akun"</strong> untuk memetakan akun default secara otomatis:
            <ul>
                <li>Persediaan Bahan Baku</li>
                <li>Persediaan Barang Jadi</li>
                <li>Biaya Produksi / Overhead</li>
                <li>Akun Pembayaran (Kas/Bank)</li>
            </ul>
        </li>
        <li><strong>Membuat Resep Baru:</strong>
            <br>Buka menu <em>Manufaktur &gt; Resep &gt; Tambah Resep</em>. Pilih produk jadi yang akan diproduksi.
        </li>
        <li><strong>Memilih Bahan Baku & Kuantitas:</strong>
            <br>Tambahkan item-item bahan baku beserta jumlah yang digunakan per porsi/unit barang jadi.
        </li>
        <li><strong>Tambahan Biaya Produksi / Overhead (Opsional):</strong>
            <br>Masukkan biaya produksi tambahan (seperti tenaga kerja, listrik, atau kemasan) dengan tipe: <em>Fixed (Tetap)</em>, <em>Percentage (%)</em>, atau <em>Per Unit</em>.
        </li>
    </ol>

    <!-- TAHAP 3 -->
    <h3 class="subsection-title">TAHAP 3: Eksekusi Produksi (Production Execution)</h3>
    <p style="margin-top: 4px; margin-bottom: 6px;">
        Proses ini mengubah stok bahan baku menjadi stok barang jadi berdasarkan resep yang telah dibuat.
    </p>
    <div class="info-card">
        <strong>Navigasi Menu:</strong> <span class="route-path">Manufaktur &gt; Produksi &gt; Tambah Produksi</span> (URL: <code>/manufacturing/production/create</code>)
    </div>
    <ol class="step-list">
        <li><strong>Pilih Resep & Lokasi:</strong> Pilih resep produk jadi dan lokasi produksi.</li>
        <li><strong>Estimasi Jumlah Produksi Otomatis:</strong>
            <br>Sistem secara otomatis menghitung dan menampilkan <strong>"Estimasi Maksimum Produksi"</strong> berdasarkan ketersediaan stok bahan baku paling minimum di lokasi tersebut.
        </li>
        <li><strong>Input Jumlah Produksi:</strong> Masukkan jumlah barang jadi yang ingin diproduksi. Sistem akan menghitung total kebutuhan bahan baku secara riil.</li>
        <li><strong>Status Produksi (Final):</strong>
            <br>Pilih status <strong>Final</strong> untuk menyelesaikan produksi.
        </li>
    </ol>

    <p><strong>Dampak Sistem Otomatis saat Produksi Final:</strong></p>
    <table class="data-table">
        <thead>
            <tr>
                <th>Komponen</th>
                <th>Perubahan Stok</th>
                <th>Dampak Jurnal Akuntansi Double-Entry</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Bahan Baku (Ingredients)</strong></td>
                <td>Berkurang otomatis sesuai resep &amp; kuantitas produksi</td>
                <td><span class="badge-credit">Kredit</span> Akun Persediaan Bahan Baku</td>
            </tr>
            <tr>
                <td><strong>Barang Jadi (Finished Product)</strong></td>
                <td>Bertambah otomatis di gudang/lokasi terpilih</td>
                <td><span class="badge-debit">Debet</span> Akun Persediaan Barang Jadi</td>
            </tr>
            <tr>
                <td><strong>Biaya Overhead / Operational</strong></td>
                <td>Dicatat sesuai input biaya produksi tambahan</td>
                <td><span class="badge-credit">Kredit</span> Akun Kas/Bank atau Akun Biaya Production Overhead</td>
            </tr>
        </tbody>
    </table>

    <!-- TAHAP 4 -->
    <h3 class="subsection-title">TAHAP 4: Penjualan Produk Jadi via Kasir POS / Sales</h3>
    <p style="margin-top: 4px; margin-bottom: 6px;">
        Produk barang jadi yang telah diproduksi siap dijual kepada pelanggan melalui modul POS atau Penjualan.
    </p>
    <div class="info-card">
        <strong>Navigasi Menu:</strong> <span class="route-path">Penjualan &gt; Kasir POS</span> (URL: <code>/pos/create</code>)
    </div>
    <ol class="step-list">
        <li><strong>Pilih Produk Barang Jadi:</strong> Kasir memilih item barang jadi pada layar POS.</li>
        <li><strong>Pembayaran Pelanggan:</strong> Pilih metode pembayaran (Kas, Bank/Transfer, Midtrans Payment Gateway, dll.).</li>
        <li><strong>Selesaikan Transaksi & Cetak Struk:</strong> Klik Bayar. Sistem akan mencetak kuitansi/faktur penjualan.</li>
    </ol>
    <p><strong>Dampak Sistem Otomatis:</strong></p>
    <ul style="margin-top: 2px; margin-bottom: 10px; padding-left: 20px;">
        <li>Stok Barang Jadi berkurang secara real-time.</li>
        <li>Penerimaan kas/bank tercatat pada laporan saldo keuangan.</li>
        <li>Jurnal Akuntansi Penjualan: <span class="badge-debit">Debet</span> Kas/Bank | <span class="badge-credit">Kredit</span> Pendapatan Penjualan &amp; Pengakuan Harga Pokok Penjualan (HPP).</li>
    </ul>

    <!-- MATRIX RINGKASAN INTEGRASI MODUL -->
    <h2 class="section-title">3. MATRIKS INTEGRASI MODUL OPERASIONAL</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Tahapan Alur</th>
                <th style="width: 26%;">Modul Terlibat</th>
                <th style="width: 26%;">Efek Modul Stok</th>
                <th style="width: 26%;">Efek Modul Akuntansi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>1. Pembelian Bahan Baku</strong></td>
                <td>Pembelian (Purchase) &amp; Kontak Supplier</td>
                <td>Stok Bahan Baku (+)<br><em>(Persediaan Bertambah)</em></td>
                <td>Debet: Persediaan Bahan Baku<br>Kredit: Kas / Utang Usaha</td>
            </tr>
            <tr>
                <td><strong>2. Formulasi Resep & Mapping</strong></td>
                <td>Manufaktur (Recipe &amp; Settings)</td>
                <td>Tidak mengubah stok<br><em>(Definisi Formula)</em></td>
                <td>Pemetaan Akun Baku ke Bagan Akun (CoA)</td>
            </tr>
            <tr>
                <td><strong>3. Pelaksanaan Produksi</strong></td>
                <td>Manufaktur (Production)</td>
                <td>Stok Bahan Baku (-)<br>Stok Barang Jadi (+)</td>
                <td>Debet: Persediaan Barang Jadi<br>Kredit: Persediaan Bahan Baku<br>Kredit: Biaya Overhead / Kas</td>
            </tr>
            <tr>
                <td><strong>4. Penjualan Barang Jadi</strong></td>
                <td>Penjualan / POS &amp; Pembayaran</td>
                <td>Stok Barang Jadi (-)<br><em>(Terjual ke Konsumen)</em></td>
                <td>Debet: Kas / Bank<br>Kredit: Pendapatan Penjualan<br>Debet: HPP | Kredit: Barang Jadi</td>
            </tr>
        </tbody>
    </table>

    <!-- REKOMENDASI BEST PRACTICES -->
    <h2 class="section-title">4. REKOMENDASI &amp; PETUNJUK PENGGUNAAN</h2>
    <div class="info-card">
        <ol style="margin: 0; padding-left: 18px;">
            <li><strong>Lakukan "Auto Map Akun" sebelum produksi pertama kali:</strong> Memastikan seluruh transaksi jurnal akuntansi manufaktur dan POS tercatat rapi tanpa perlu konfigurasi manual yang rumit.</li>
            <li><strong>Periksa Estimasi Maksimum Produksi:</strong> Gunakan indikator estimasi pada layar pembuatan produksi untuk menghindari minus stok bahan baku.</li>
            <li><strong>Gunakan Status Produksi "Final":</strong> Hanya produksi berstatus Final yang memotong stok bahan baku, menambah stok barang jadi, serta memicu jurnal akuntansi.</li>
            <li><strong>Evaluasi Laporan Laba Rugi & Neraca:</strong> Nilai aset persediaan bahan baku dan barang jadi akan selalu sinkron antara modul Stok dan modul Akuntansi.</li>
        </ol>
    </div>

    <div class="footer-note">
        Dokumen Panduan Alur Manufaktur ini dibuat secara otomatis oleh sistem. Hak Cipta &copy; {{ date('Y') }}. Seluruh hak cipta dilindungi undang-undang.
    </div>

</body>
</html>
