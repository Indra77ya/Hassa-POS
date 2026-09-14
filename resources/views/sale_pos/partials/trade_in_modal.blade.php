<div class="modal fade" id="pos_trade_in_modal" tabindex="-1" role="dialog" aria-labelledby="pos_trade_in_modal_label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="pos_trade_in_modal_label"><i class="fas fa-exchange-alt"></i> Tukar Tambah (Trade-In) Perangkat Bekas</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_device_name', 'Nama Perangkat Bekas:*') !!}
                            {!! Form::text('trade_in[device_name]', null, ['class' => 'form-control', 'id' => 'trade_in_pos_device_name', 'placeholder' => 'Contoh: iPhone 11 Pro']); !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_brand', 'Merek:') !!}
                            {!! Form::text('trade_in[brand]', null, ['class' => 'form-control', 'id' => 'trade_in_pos_brand', 'placeholder' => 'Contoh: Apple']); !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_model', 'Model / Tipe:') !!}
                            {!! Form::text('trade_in[model]', null, ['class' => 'form-control', 'id' => 'trade_in_pos_model', 'placeholder' => 'Contoh: A2215']); !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_serial_no', 'No Seri / IMEI:') !!}
                            {!! Form::text('trade_in[serial_no]', null, ['class' => 'form-control', 'id' => 'trade_in_pos_serial_no', 'placeholder' => 'Contoh: 356789...']); !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_value', 'Nilai Potongan Tukar Tambah (Rp):*') !!}
                            {!! Form::text('trade_in[trade_in_value]', null, ['class' => 'form-control input_number', 'id' => 'trade_in_pos_value', 'placeholder' => '0.00']); !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_unit_price', 'Harga Jual Estimasi (Rp):') !!}
                            {!! Form::text('trade_in[unit_price]', null, ['class' => 'form-control input_number', 'id' => 'trade_in_pos_unit_price', 'placeholder' => '0.00']); !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_condition', 'Kondisi Perangkat:') !!}
                            {!! Form::text('trade_in[condition]', null, ['class' => 'form-control', 'id' => 'trade_in_pos_condition', 'placeholder' => 'Mulus, Layar Baret, dll']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('trade_in_pos_notes', 'Catatan Kelengkapan:') !!}
                            {!! Form::text('trade_in[notes]', null, ['class' => 'form-control', 'id' => 'trade_in_pos_notes', 'placeholder' => 'Unit + Charger']); !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" data-dismiss="modal">Simpan & Gunakan</button>
            </div>
        </div>
    </div>
</div>
