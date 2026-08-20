@php
    $is_accounting = !empty($data['accounting_data']['is_accounting']);
    $acc = $data['accounting_data'] ?? [];
@endphp

<!-- KPI SUMMARY CARDS -->
<div class="row" style="margin-bottom: 20px;">
    <!-- Total Pendapatan / Sales -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box bg-blue" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <span class="info-box-icon" style="border-radius: 8px 0 0 8px;"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-weight: 600; text-transform: uppercase;">Total Pendapatan</span>
                <span class="info-box-number display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: bold;">
                    {{ $is_accounting ? $acc['total_income'] : $data['total_sell'] }}
                </span>
                <small style="opacity: 0.9;">Total Penjualan Operasional</small>
            </div>
        </div>
    </div>

    <!-- HPP / COGS -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box bg-yellow" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <span class="info-box-icon" style="border-radius: 8px 0 0 8px;"><i class="fa fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-weight: 600; text-transform: uppercase;">Harga Pokok (HPP)</span>
                <span class="info-box-number display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: bold;">
                    {{ $is_accounting ? $acc['total_cogs'] : (($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock']) }}
                </span>
                <small style="opacity: 0.9;">Modal Pokok Barang Sold</small>
            </div>
        </div>
    </div>

    <!-- Gross Profit / Laba Kotor -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box {{ $data['gross_profit'] >= 0 ? 'bg-aqua' : 'bg-red' }}" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <span class="info-box-icon" style="border-radius: 8px 0 0 8px;"><i class="fa fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-weight: 600; text-transform: uppercase;">Laba Kotor</span>
                <span class="info-box-number display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: bold;">
                    {{ $data['gross_profit'] }}
                </span>
                <small style="opacity: 0.9;">Pendapatan dikurangi HPP</small>
            </div>
        </div>
    </div>

    <!-- Net Profit / Laba Bersih -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box {{ $data['net_profit'] >= 0 ? 'bg-green' : 'bg-red' }}" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.08);">
            <span class="info-box-icon" style="border-radius: 8px 0 0 8px;"><i class="fa fa-wallet"></i></span>
            <div class="info-box-content">
                <span class="info-box-text" style="font-weight: 600; text-transform: uppercase;">Laba / Rugi Bersih</span>
                <span class="info-box-number display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: bold;">
                    {{ $data['net_profit'] }}
                </span>
                <small style="opacity: 0.9;">
                    @if($data['net_profit'] >= 0)
                        <i class="fa fa-check-circle"></i> Profit Akhir Bersih
                    @else
                        <i class="fa fa-exclamation-triangle"></i> Defisit / Rugi
                    @endif
                </small>
            </div>
        </div>
    </div>
</div>

<!-- VERTICAL P&L FINANCIAL STATEMENT (STANDAR MODUL AKUNTANSI) -->
<div class="col-xs-12" style="padding: 0;">
    <div class="box box-solid" style="border-radius: 8px; border-top: 3px solid #3c8dbc; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <div class="box-header with-border" style="background-color: #fcfcfc; padding: 15px 20px;">
            <h3 class="box-title" style="font-weight: 700; color: #333; font-size: 18px;">
                <i class="fa fa-file-invoice-dollar text-primary"></i> Laporan Laba Rugi Perusahaan (Profit & Loss Statement)
            </h3>
            <span class="pull-right label {{ $is_accounting ? 'label-success' : 'label-warning' }}" style="font-size: 12px; padding: 6px 10px; border-radius: 4px;">
                <i class="fa {{ $is_accounting ? 'fa-check-circle' : 'fa-info-circle' }}"></i>
                {{ $is_accounting ? 'Terintegrasi Modul Akuntansi (Buku Besar General Ledger)' : 'Mode Standar Transaksi POS' }}
            </span>
        </div>
        <div class="box-body" style="padding: 20px 25px;">
            <table class="table table-hover table-striped" style="margin-bottom: 0;">
                <tbody>
                    @if($is_accounting)
                        <!-- 1. PENDAPATAN USAHA (OPERATING REVENUE) -->
                        <tr style="background-color: #eef5fc;">
                            <td colspan="2" style="font-size: 15px; font-weight: 700; color: #1e3a8a;">
                                <i class="fa fa-caret-right"></i> 1. PENDAPATAN USAHA (OPERATING REVENUE)
                            </td>
                        </tr>
                        @forelse($acc['incomes'] as $inc)
                            <tr>
                                <td style="padding-left: 35px; color: #444;">
                                    @if(!empty($inc->gl_code)) <code>{{ $inc->gl_code }}</code> - @endif {{ $inc->name }}
                                </td>
                                <td class="text-right" style="font-weight: 600;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $inc->balance }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td style="padding-left: 35px; color: #777; font-style: italic;">Pendapatan Penjualan</td>
                                <td class="text-right" style="font-weight: 600;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $acc['total_income'] }}</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr style="border-top: 1px solid #cbd5e1; font-weight: 700; background-color: #f8fafc;">
                            <td style="padding-left: 20px;">TOTAL PENDAPATAN USAHA</td>
                            <td class="text-right text-primary" style="font-size: 15px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['total_income'] }}</span>
                            </td>
                        </tr>

                        <!-- 2. HARGA POKOK PENJUALAN (COST OF GOODS SOLD / COGS) -->
                        <tr style="background-color: #fefce8;">
                            <td colspan="2" style="font-size: 15px; font-weight: 700; color: #854d0e; padding-top: 15px;">
                                <i class="fa fa-caret-right"></i> 2. HARGA POKOK PENJUALAN (HPP / COGS)
                            </td>
                        </tr>
                        @forelse($acc['cogs'] as $cogs_item)
                            <tr>
                                <td style="padding-left: 35px; color: #444;">
                                    @if(!empty($cogs_item->gl_code)) <code>{{ $cogs_item->gl_code }}</code> - @endif {{ $cogs_item->name }}
                                </td>
                                <td class="text-right" style="font-weight: 600;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $cogs_item->balance }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td style="padding-left: 35px; color: #777; font-style: italic;">Harga Pokok Penjualan</td>
                                <td class="text-right" style="font-weight: 600;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $acc['total_cogs'] }}</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr style="border-top: 1px solid #cbd5e1; font-weight: 700; background-color: #fef8d8;">
                            <td style="padding-left: 20px;">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                            <td class="text-right text-warning" style="font-size: 15px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['total_cogs'] }}</span>
                            </td>
                        </tr>

                        <!-- 3. LABA / RUGI KOTOR (GROSS PROFIT) -->
                        <tr style="background-color: #ecfdf5; border-top: 2px solid #059669; border-bottom: 2px solid #059669;">
                            <td style="font-size: 16px; font-weight: 800; color: #065f46; padding: 12px 20px;">
                                <i class="fa fa-calculator"></i> LABA KOTOR (GROSS PROFIT = PENDAPATAN - HPP)
                            </td>
                            <td class="text-right" style="font-size: 16px; font-weight: 800; color: {{ $acc['gross_profit'] >= 0 ? '#047857' : '#dc2626' }}; padding: 12px 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['gross_profit'] }}</span>
                            </td>
                        </tr>

                        <!-- 4. BEBAN OPERASIONAL (OPERATING EXPENSES) -->
                        <tr style="background-color: #fef2f2;">
                            <td colspan="2" style="font-size: 15px; font-weight: 700; color: #991b1b; padding-top: 15px;">
                                <i class="fa fa-caret-right"></i> 3. BEBAN OPERASIONAL (OPERATING EXPENSES)
                            </td>
                        </tr>
                        @forelse($acc['operating_expenses'] as $op_exp)
                            <tr>
                                <td style="padding-left: 35px; color: #444;">
                                    @if(!empty($op_exp->gl_code)) <code>{{ $op_exp->gl_code }}</code> - @endif {{ $op_exp->name }}
                                </td>
                                <td class="text-right" style="font-weight: 600;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $op_exp->balance }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td style="padding-left: 35px; color: #777; font-style: italic;">Tidak ada beban operasional terdaftar</td>
                                <td class="text-right" style="font-weight: 600;">
                                    <span class="display_currency" data-currency_symbol="true">0</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr style="border-top: 1px solid #cbd5e1; font-weight: 700; background-color: #fee2e2;">
                            <td style="padding-left: 20px;">TOTAL BEBAN OPERASIONAL</td>
                            <td class="text-right text-danger" style="font-size: 15px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['total_operating_expense'] }}</span>
                            </td>
                        </tr>

                        <!-- 5. PENDAPATAN & BEBAN NON-OPERASIONAL -->
                        @if($acc['total_other_income'] > 0 || $acc['total_other_expense'] > 0)
                            <tr style="background-color: #f3e8ff;">
                                <td colspan="2" style="font-size: 15px; font-weight: 700; color: #6b21a8; padding-top: 15px;">
                                    <i class="fa fa-caret-right"></i> 4. PENDAPATAN & BEBAN LAIN-LAIN (NON-OPERASIONAL)
                                </td>
                            </tr>
                            @foreach($acc['other_incomes'] as $o_inc)
                                <tr>
                                    <td style="padding-left: 35px; color: #444;">
                                        (+) @if(!empty($o_inc->gl_code)) <code>{{ $o_inc->gl_code }}</code> - @endif {{ $o_inc->name }}
                                    </td>
                                    <td class="text-right text-success" style="font-weight: 600;">
                                        <span class="display_currency" data-currency_symbol="true">{{ $o_inc->balance }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach($acc['other_expenses'] as $o_exp)
                                <tr>
                                    <td style="padding-left: 35px; color: #444;">
                                        (-) @if(!empty($o_exp->gl_code)) <code>{{ $o_exp->gl_code }}</code> - @endif {{ $o_exp->name }}
                                    </td>
                                    <td class="text-right text-danger" style="font-weight: 600;">
                                        <span class="display_currency" data-currency_symbol="true">{{ $o_exp->balance }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    @else
                        <!-- FALLBACK LEGACY POS TRANSACTIONS P&L -->
                        <tr style="background-color: #eef5fc;">
                            <td colspan="2" style="font-size: 15px; font-weight: 700; color: #1e3a8a;">
                                <i class="fa fa-caret-right"></i> 1. PENDAPATAN PENJUALAN (REVENUE)
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Total Penjualan (Exc. Tax & Discount)</td>
                            <td class="text-right" style="font-weight: 600;">
                                <span class="display_currency" data-currency_symbol="true">{{ $data['total_sell'] }}</span>
                            </td>
                        </tr>
                        @if(!empty($data['total_sell_shipping_charge']))
                            <tr>
                                <td style="padding-left: 35px;">Ongkos Kirim Penjualan</td>
                                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_shipping_charge'] }}</span></td>
                            </tr>
                        @endif
                        @if(!empty($data['total_sell_additional_expense']))
                            <tr>
                                <td style="padding-left: 35px;">Biaya Tambahan Penjualan</td>
                                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_additional_expense'] }}</span></td>
                            </tr>
                        @endif

                        <tr style="background-color: #fefce8;">
                            <td colspan="2" style="font-size: 15px; font-weight: 700; color: #854d0e; padding-top: 15px;">
                                <i class="fa fa-caret-right"></i> 2. HARGA POKOK PENJUALAN (HPP / COGS)
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Stok Awal (Opening Stock)</td>
                            <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['opening_stock'] }}</span></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">(+) Total Pembelian (Exc. Tax & Discount)</td>
                            <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase'] }}</span></td>
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">(-) Stok Akhir (Closing Stock)</td>
                            <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['closing_stock'] }}</span></td>
                        </tr>
                        @php
                            $cogs_calc = ($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock'];
                        @endphp
                        <tr style="border-top: 1px solid #cbd5e1; font-weight: 700; background-color: #fef8d8;">
                            <td style="padding-left: 20px;">TOTAL HARGA POKOK PENJUALAN (HPP)</td>
                            <td class="text-right text-warning" style="font-size: 15px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $cogs_calc }}</span>
                            </td>
                        </tr>

                        <!-- LABA KOTOR LEGACY -->
                        <tr style="background-color: #ecfdf5; border-top: 2px solid #059669; border-bottom: 2px solid #059669;">
                            <td style="font-size: 16px; font-weight: 800; color: #065f46; padding: 12px 20px;">
                                <i class="fa fa-calculator"></i> LABA KOTOR (GROSS PROFIT)
                            </td>
                            <td class="text-right" style="font-size: 16px; font-weight: 800; color: {{ $data['gross_profit'] >= 0 ? '#047857' : '#dc2626' }}; padding: 12px 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $data['gross_profit'] }}</span>
                            </td>
                        </tr>

                        <tr style="background-color: #fef2f2;">
                            <td colspan="2" style="font-size: 15px; font-weight: 700; color: #991b1b; padding-top: 15px;">
                                <i class="fa fa-caret-right"></i> 3. BEBAN OPERASIONAL & BIAYA LAINNYA
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-left: 35px;">Total Pengeluaran (Expense)</td>
                            <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_expense'] }}</span></td>
                        </tr>
                        @if(!empty($data['total_adjustment']))
                            <tr>
                                <td style="padding-left: 35px;">Penyesuaian Stok (Stock Adjustment)</td>
                                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_adjustment'] }}</span></td>
                            </tr>
                        @endif
                        @if(!empty($data['total_purchase_shipping_charge']))
                            <tr>
                                <td style="padding-left: 35px;">Ongkos Kirim Pembelian</td>
                                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase_shipping_charge'] }}</span></td>
                            </tr>
                        @endif
                        @if(!empty($data['total_sell_discount']))
                            <tr>
                                <td style="padding-left: 35px;">Diskon Penjualan Ditanggung</td>
                                <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_discount'] }}</span></td>
                            </tr>
                        @endif
                    @endif

                    <!-- LABA / RUGI BERSIH AKHIR (NET PROFIT) -->
                    <tr style="background-color: {{ $data['net_profit'] >= 0 ? '#dcfce7' : '#fee2e2' }}; border-top: 3px double {{ $data['net_profit'] >= 0 ? '#16a34a' : '#dc2626' }}; border-bottom: 3px double {{ $data['net_profit'] >= 0 ? '#16a34a' : '#dc2626' }};">
                        <td style="font-size: 18px; font-weight: 900; color: {{ $data['net_profit'] >= 0 ? '#15803d' : '#991b1b' }}; padding: 16px 20px;">
                            <i class="fa {{ $data['net_profit'] >= 0 ? 'fa-trophy' : 'fa-exclamation-triangle' }}"></i> LABA / RUGI BERSIH AKHIR (NET PROFIT)
                        </td>
                        <td class="text-right" style="font-size: 20px; font-weight: 900; color: {{ $data['net_profit'] >= 0 ? '#15803d' : '#991b1b' }}; padding: 16px 20px;">
                            <span class="display_currency" data-currency_symbol="true">{{ $data['net_profit'] }}</span>
                            @if(!empty($data['total_sell']) && $data['total_sell'] != 0)
                                <small style="font-size: 65%; font-weight: normal; margin-left: 5px;">
                                    ({{ number_format(($data['net_profit'] / ($is_accounting ? $acc['total_income'] : $data['total_sell'])) * 100, 2) }}%)
                                </small>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- INFORMATIONAL TAX SUMMARY -->
@if(!empty($data['total_sell_tax']) || !empty($data['total_purchase_tax']))
<div class="col-xs-12" style="padding: 0; margin-top: 15px;">
    <div class="box box-solid" style="border-radius: 8px; border-top: 2px solid #94a3b8;">
        <div class="box-header with-border" style="background-color: #f8fafc;">
            <h4 class="box-title" style="font-weight: 600; color: #475569; font-size: 15px;">
                <i class="fa fa-calculator text-muted"></i> Ringkasan Pajak (Pajak Keluaran & Masukan)
            </h4>
        </div>
        <div class="box-body">
            <table class="table table-condensed" style="margin-bottom:0; width: 100%;">
                <tr>
                    <th style="width: 50%;">Pajak Terkumpul dari Penjualan (PPN Keluaran):</th>
                    <td><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_tax'] }}</span></td>
                </tr>
                <tr>
                    <th>Pajak Dibayarkan pada Pembelian (PPN Masukan):</th>
                    <td><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase_tax'] }}</span></td>
                </tr>
                <tr style="border-top:1px solid #cbd5e1; font-weight: bold;">
                    @php $net_tax = $data['total_sell_tax'] - $data['total_purchase_tax']; @endphp
                    <th>Kewajiban Pajak Bersih (Net Tax Liability):</th>
                    <td>
                        <strong class="{{ $net_tax >= 0 ? 'text-danger' : 'text-success' }}">
                            <span class="display_currency" data-currency_symbol="true">{{ $net_tax }}</span>
                        </strong>
                    </td>
                </tr>
            </table>
            <small class="help-block" style="margin: 5px 0 0 0; color: #64748b;">*Catatan: Nilai PPN adalah titipan pajak dan tidak memengaruhi perhitungan Laba/Rugi operasional.</small>
        </div>
    </div>
</div>
@endif
