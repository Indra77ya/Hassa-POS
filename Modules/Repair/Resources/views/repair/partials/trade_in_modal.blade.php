<div class="modal fade" id="trade_in_modal" tabindex="-1" role="dialog" aria-labelledby="tradeInModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="tradeInModalLabel"><i class="fas fa-sync-alt text-success"></i> Form Input Tukar Tambah</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_model">Nama Tipe / Model Perangkat: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="trade_in_model" name="trade_in_model_modal" placeholder="Contoh: iPhone 11 128GB / Laptop Asus X441" value="@if(!empty($trade_in_details['model_name'])){{$trade_in_details['model_name']}}@endif">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_serial">Nomor Seri / IMEI:</label>
                            <input type="text" class="form-control" id="trade_in_serial" name="trade_in_serial_modal" placeholder="Contoh: 358912093810123" value="@if(!empty($trade_in_details['serial_no'])){{$trade_in_details['serial_no']}}@endif">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="trade_in_condition">Kondisi & Kelengkapan Perangkat:</label>
                            <textarea class="form-control" id="trade_in_condition" name="trade_in_condition_modal" rows="2" placeholder="Contoh: Mulus 95%, Battery Health 85%, Kelengkapan Unit + Charger Original">@if(!empty($trade_in_details['condition'])){{$trade_in_details['condition']}}@endif</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_val_input">Nilai Tukar Tambah (Potongan POS): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fas fa-money-bill-wave"></i></span>
                                <input type="text" class="form-control input_number" id="trade_in_val_input" name="trade_in_val_input_modal" placeholder="0" value="@if(!empty($trade_in_details['trade_in_value'])){{@num_format($trade_in_details['trade_in_value'])}}@endif">
                            </div>
                            <span class="help-block">Nilai potongan yang akan mengurangi total belanja kasir.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_resale_price_input">Estimasi Harga Jual Kembali (Resale Price):</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control input_number" id="trade_in_resale_price_input" name="trade_in_resale_price_input_modal" placeholder="0" value="@if(!empty($trade_in_details['resale_price'])){{@num_format($trade_in_details['resale_price'])}}@else @if(!empty($trade_in_details['trade_in_value'])){{@num_format($trade_in_details['trade_in_value'])}}@endif @endif">
                            </div>
                            <span class="help-block">Harga jual produk bekas ini jika dijual kembali ke konsumen lain.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="save_trade_in_btn"><i class="fas fa-check"></i> Simpan Tukar Tambah</button>
            </div>
        </div>
    </div>
</div>
