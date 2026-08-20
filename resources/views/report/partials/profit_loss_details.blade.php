@php
    $is_accounting = !empty($data['accounting_data']['is_accounting']);
    $acc = $data['accounting_data'] ?? [];
@endphp

<!-- KPI SUMMARY CARDS (STANDARD CLEAN & PROFESSIONAL DESIGN) -->
<div class="row" style="margin-bottom: 20px;">
    <!-- Total Pendapatan / Sales -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">
                Total Pendapatan
            </div>
            <div class="display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 4px 0;">
                {{ $is_accounting ? $acc['total_income'] : $data['total_sell'] }}
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Penjualan Operasional
            </div>
        </div>
    </div>

    <!-- HPP / COGS -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">
                Harga Pokok (HPP)
            </div>
            <div class="display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 4px 0;">
                {{ $is_accounting ? $acc['total_cogs'] : (($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock']) }}
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Modal Pokok Barang Terjual
            </div>
        </div>
    </div>

    <!-- Gross Profit / Laba Kotor -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">
                Laba Kotor
            </div>
            <div class="display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: 700; color: {{ $data['gross_profit'] >= 0 ? '#16a34a' : '#dc2626' }}; margin: 4px 0;">
                {{ $data['gross_profit'] }}
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Pendapatan dikurangi HPP
            </div>
        </div>
    </div>

    <!-- Net Profit / Laba Bersih -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">
                Laba / Rugi Bersih
            </div>
            <div class="display_currency" data-currency_symbol="true" style="font-size: 20px; font-weight: 700; color: {{ $data['net_profit'] >= 0 ? '#16a34a' : '#dc2626' }}; margin: 4px 0;">
                {{ $data['net_profit'] }}
            </div>
            <div style="font-size: 12px; color: #64748b;">
                @if($data['net_profit'] >= 0)
                    Laba Bersih Akhir
                @else
                    Defisit / Rugi Akhir
                @endif
            </div>
        </div>
    </div>
</div>

<!-- VERTICAL P&L FINANCIAL STATEMENT (STANDARD CLEAN LAYOUT) -->
<div class="col-xs-12" style="padding: 0;">
    <div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="panel-heading" style="background-color: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
            <h3 class="panel-title" style="font-weight: 700; color: #1e293b; font-size: 16px; display: inline-block;">
                Laporan Laba Rugi Perusahaan (Profit & Loss Statement)
            </h3>
            <span class="pull-right label {{ $is_accounting ? 'label-success' : 'label-default' }}" style="font-weight: 500; font-size: 11px; padding: 5px 8px;">
                {{ $is_accounting ? 'Modul Akuntansi' : 'Standar POS' }}
            </span>
        </div>
        <div class="panel-body" style="padding: 0;">
            <table class="table" style="margin-bottom: 0; width: 100%;">
                <tbody>
                    @if($is_accounting)
                        <!-- 1. PENDAPATAN USAHA (OPERATING REVENUE) -->
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                1. Pendapatan Usaha (Operating Revenue)
                            </td>
                        </tr>
                        @forelse($acc['incomes'] as $inc)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">
                                    @if(!empty($inc->gl_code)) <span style="color: #64748b; font-size: 12px; margin-right: 6px;">{{ $inc->gl_code }}</span> @endif {{ $inc->name }}
                                </td>
                                <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $inc->balance }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Pendapatan Penjualan</td>
                                <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $acc['total_income'] }}</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr style="border-top: 1px solid #cbd5e1; border-bottom: 1px solid #e2e8f0; font-weight: 700; background-color: #ffffff;">
                            <td style="padding-left: 20px; color: #1e293b;">Total Pendapatan Usaha</td>
                            <td class="text-right" style="font-size: 14px; color: #0f172a; padding-right: 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['total_income'] }}</span>
                            </td>
                        </tr>

                        <!-- 2. HARGA POKOK PENJUALAN (COST OF GOODS SOLD / COGS) -->
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                2. Harga Pokok Penjualan (HPP / COGS)
                            </td>
                        </tr>
                        @forelse($acc['cogs'] as $cogs_item)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">
                                    @if(!empty($cogs_item->gl_code)) <span style="color: #64748b; font-size: 12px; margin-right: 6px;">{{ $cogs_item->gl_code }}</span> @endif {{ $cogs_item->name }}
                                </td>
                                <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $cogs_item->balance }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Harga Pokok Penjualan</td>
                                <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $acc['total_cogs'] }}</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr style="border-top: 1px solid #cbd5e1; border-bottom: 1px solid #e2e8f0; font-weight: 700; background-color: #ffffff;">
                            <td style="padding-left: 20px; color: #1e293b;">Total Harga Pokok Penjualan (HPP)</td>
                            <td class="text-right" style="font-size: 14px; color: #0f172a; padding-right: 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['total_cogs'] }}</span>
                            </td>
                        </tr>

                        <!-- 3. LABA / RUGI KOTOR (GROSS PROFIT) -->
                        <tr style="background-color: #f8fafc; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                            <td style="font-size: 14px; font-weight: 700; color: #0f172a; padding: 12px 20px;">
                                LABA KOTOR (GROSS PROFIT)
                            </td>
                            <td class="text-right" style="font-size: 15px; font-weight: 700; color: {{ $acc['gross_profit'] >= 0 ? '#16a34a' : '#dc2626' }}; padding: 12px 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['gross_profit'] }}</span>
                            </td>
                        </tr>

                        <!-- 4. BEBAN OPERASIONAL (OPERATING EXPENSES) -->
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                3. Beban Operasional (Operating Expenses)
                            </td>
                        </tr>
                        @forelse($acc['operating_expenses'] as $op_exp)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">
                                    @if(!empty($op_exp->gl_code)) <span style="color: #64748b; font-size: 12px; margin-right: 6px;">{{ $op_exp->gl_code }}</span> @endif {{ $op_exp->name }}
                                </td>
                                <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                    <span class="display_currency" data-currency_symbol="true">{{ $op_exp->balance }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #64748b; font-style: italic;">Tidak ada beban operasional terdaftar</td>
                                <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                    <span class="display_currency" data-currency_symbol="true">0</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr style="border-top: 1px solid #cbd5e1; border-bottom: 1px solid #e2e8f0; font-weight: 700; background-color: #ffffff;">
                            <td style="padding-left: 20px; color: #1e293b;">Total Beban Operasional</td>
                            <td class="text-right" style="font-size: 14px; color: #0f172a; padding-right: 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $acc['total_operating_expense'] }}</span>
                            </td>
                        </tr>

                        <!-- 5. PENDAPATAN & BEBAN NON-OPERASIONAL -->
                        @if($acc['total_other_income'] > 0 || $acc['total_other_expense'] > 0)
                            <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    4. Pendapatan & Beban Lain-Lain (Non-Operasional)
                                </td>
                            </tr>
                            @foreach($acc['other_incomes'] as $o_inc)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding-left: 35px; color: #334155;">
                                        (+) @if(!empty($o_inc->gl_code)) <span style="color: #64748b; font-size: 12px; margin-right: 6px;">{{ $o_inc->gl_code }}</span> @endif {{ $o_inc->name }}
                                    </td>
                                    <td class="text-right" style="font-weight: 500; color: #16a34a; padding-right: 20px;">
                                        <span class="display_currency" data-currency_symbol="true">{{ $o_inc->balance }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach($acc['other_expenses'] as $o_exp)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding-left: 35px; color: #334155;">
                                        (-) @if(!empty($o_exp->gl_code)) <span style="color: #64748b; font-size: 12px; margin-right: 6px;">{{ $o_exp->gl_code }}</span> @endif {{ $o_exp->name }}
                                    </td>
                                    <td class="text-right" style="font-weight: 500; color: #dc2626; padding-right: 20px;">
                                        <span class="display_currency" data-currency_symbol="true">{{ $o_exp->balance }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    @else
                        <!-- FALLBACK LEGACY POS TRANSACTIONS P&L -->
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                1. Pendapatan Penjualan (Revenue)
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding-left: 35px; color: #334155;">Total Penjualan (Exc. Tax & Discount)</td>
                            <td class="text-right" style="font-weight: 500; color: #0f172a; padding-right: 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $data['total_sell'] }}</span>
                            </td>
                        </tr>
                        @if(!empty($data['total_sell_shipping_charge']))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Ongkos Kirim Penjualan</td>
                                <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_shipping_charge'] }}</span></td>
                            </tr>
                        @endif
                        @if(!empty($data['total_sell_additional_expense']))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Biaya Tambahan Penjualan</td>
                                <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_additional_expense'] }}</span></td>
                            </tr>
                        @endif

                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                2. Harga Pokok Penjualan (HPP / COGS)
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding-left: 35px; color: #334155;">Stok Awal (Opening Stock)</td>
                            <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['opening_stock'] }}</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding-left: 35px; color: #334155;">(+) Total Pembelian (Exc. Tax & Discount)</td>
                            <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase'] }}</span></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding-left: 35px; color: #334155;">(-) Stok Akhir (Closing Stock)</td>
                            <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['closing_stock'] }}</span></td>
                        </tr>
                        @php
                            $cogs_calc = ($data['opening_stock'] + $data['total_purchase']) - $data['closing_stock'];
                        @endphp
                        <tr style="border-top: 1px solid #cbd5e1; border-bottom: 1px solid #e2e8f0; font-weight: 700; background-color: #ffffff;">
                            <td style="padding-left: 20px; color: #1e293b;">Total Harga Pokok Penjualan (HPP)</td>
                            <td class="text-right" style="font-size: 14px; color: #0f172a; padding-right: 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $cogs_calc }}</span>
                            </td>
                        </tr>

                        <!-- LABA KOTOR LEGACY -->
                        <tr style="background-color: #f8fafc; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                            <td style="font-size: 14px; font-weight: 700; color: #0f172a; padding: 12px 20px;">
                                LABA KOTOR (GROSS PROFIT)
                            </td>
                            <td class="text-right" style="font-size: 15px; font-weight: 700; color: {{ $data['gross_profit'] >= 0 ? '#16a34a' : '#dc2626' }}; padding: 12px 20px;">
                                <span class="display_currency" data-currency_symbol="true">{{ $data['gross_profit'] }}</span>
                            </td>
                        </tr>

                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <td colspan="2" style="font-size: 13px; font-weight: 700; color: #334155; padding: 10px 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                                3. Beban Operasional & Biaya Lainnya
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding-left: 35px; color: #334155;">Total Pengeluaran (Expense)</td>
                            <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_expense'] }}</span></td>
                        </tr>
                        @if(!empty($data['total_adjustment']))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Penyesuaian Stok (Stock Adjustment)</td>
                                <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_adjustment'] }}</span></td>
                            </tr>
                        @endif
                        @if(!empty($data['total_purchase_shipping_charge']))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Ongkos Kirim Pembelian</td>
                                <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase_shipping_charge'] }}</span></td>
                            </tr>
                        @endif
                        @if(!empty($data['total_sell_discount']))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding-left: 35px; color: #334155;">Diskon Penjualan Ditanggung</td>
                                <td class="text-right" style="padding-right: 20px;"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_discount'] }}</span></td>
                            </tr>
                        @endif
                    @endif

                    <!-- LABA / RUGI BERSIH AKHIR (NET PROFIT) -->
                    <tr style="background-color: #f8fafc; border-top: 2px solid #334155; border-bottom: 3px double #334155;">
                        <td style="font-size: 15px; font-weight: 800; color: #0f172a; padding: 14px 20px; text-transform: uppercase;">
                            Laba / Rugi Bersih Akhir (Net Profit)
                        </td>
                        <td class="text-right" style="font-size: 18px; font-weight: 800; color: {{ $data['net_profit'] >= 0 ? '#16a34a' : '#dc2626' }}; padding: 14px 20px;">
                            <span class="display_currency" data-currency_symbol="true">{{ $data['net_profit'] }}</span>
                            @if(!empty($data['total_sell']) && $data['total_sell'] != 0)
                                <small style="font-size: 70%; font-weight: 600; color: #64748b; margin-left: 5px;">
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

<!-- INFORMATIONAL TAX SUMMARY (CLEAN STANDARD VIEW) -->
@if(!empty($data['total_sell_tax']) || !empty($data['total_purchase_tax']))
<div class="col-xs-12" style="padding: 0; margin-top: 15px;">
    <div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 6px;">
        <div class="panel-heading" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 15px;">
            <h4 class="panel-title" style="font-weight: 600; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                Ringkasan Pajak (Pajak Keluaran & Masukan)
            </h4>
        </div>
        <div class="panel-body" style="padding: 15px;">
            <table class="table table-condensed" style="margin-bottom:0; width: 100%;">
                <tr>
                    <th style="width: 50%; color: #475569; font-weight: 500;">Pajak Terkumpul dari Penjualan (PPN Keluaran):</th>
                    <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_sell_tax'] }}</span></td>
                </tr>
                <tr>
                    <th style="color: #475569; font-weight: 500;">Pajak Dibayarkan pada Pembelian (PPN Masukan):</th>
                    <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $data['total_purchase_tax'] }}</span></td>
                </tr>
                <tr style="border-top:1px solid #cbd5e1; font-weight: 700;">
                    @php $net_tax = $data['total_sell_tax'] - $data['total_purchase_tax']; @endphp
                    <th style="color: #0f172a;">Kewajiban Pajak Bersih (Net Tax Liability):</th>
                    <td class="text-right">
                        <span class="display_currency" data-currency_symbol="true" style="color: {{ $net_tax >= 0 ? '#dc2626' : '#16a34a' }};">{{ $net_tax }}</span>
                    </td>
                </tr>
            </table>
            <small class="help-block" style="margin: 6px 0 0 0; color: #64748b;">*Catatan: Nilai PPN adalah titipan pajak dan tidak memengaruhi perhitungan Laba/Rugi operasional.</small>
        </div>
    </div>
</div>
@endif
