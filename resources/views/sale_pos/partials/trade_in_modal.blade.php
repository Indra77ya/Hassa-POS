<div class="modal fade" id="pos_trade_in_modal" tabindex="-1" role="dialog" aria-labelledby="posTradeInModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="posTradeInModalLabel"><i class="fa fa-refresh"></i> Tukar Tambah Perangkat (Trade-In)</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pos_trade_in_device_name">Nama/Model Perangkat:*</label>
                            <input type="text" name="trade_in[device_name]" id="pos_trade_in_device_name" class="form-control" placeholder="misal: Samsung Galaxy S21 128GB">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pos_trade_in_serial_no">SN / IMEI Perangkat:</label>
                            <input type="text" name="trade_in[serial_no]" id="pos_trade_in_serial_no" class="form-control" placeholder="Nomor Seri / IMEI">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pos_trade_in_brand_id">Brand / Merek:</label>
                            {!! Form::select('trade_in[brand_id]', $brands ?? [], null, ['class' => 'form-control select2', 'id' => 'pos_trade_in_brand_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pos_trade_in_category_id">Kategori Stock:</label>
                            {!! Form::select('trade_in[category_id]', $categories ?? [], null, ['class' => 'form-control select2', 'id' => 'pos_trade_in_category_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pos_trade_in_valuation_amount">Nilai Tukar Tambah (Potongan Harga):*</label>
                            <input type="text" name="trade_in[valuation_amount]" id="pos_trade_in_valuation_amount" class="form-control input_number" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pos_trade_in_selling_price">Estimasi Harga Jual Kembali:</label>
                            <input type="text" name="trade_in[selling_price]" id="pos_trade_in_selling_price" class="form-control input_number" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="pos_trade_in_condition_notes">Kondisi / Kelayakan Perangkat Bekas:</label>
                            <textarea name="trade_in[condition_notes]" id="pos_trade_in_condition_notes" class="form-control" rows="2" placeholder="Catatan kondisi fisik/fungsi, misal: Layar baret, fungsi normal 100%"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" data-dismiss="modal">Simpan Data Tukar Tambah</button>
            </div>
        </div>
    </div>
</div>
