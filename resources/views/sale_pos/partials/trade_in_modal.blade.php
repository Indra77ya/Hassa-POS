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
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="save_pos_trade_in_btn" data-dismiss="modal">Simpan & Gunakan</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var previous_trade_in_val = 0;

        $('#pos_trade_in_modal').on('show.bs.modal', function() {
            previous_trade_in_val = __read_number($('#trade_in_pos_value')) || 0;
        });

        function apply_trade_in_deduction() {
            var new_trade_in_val = __read_number($('#trade_in_pos_value')) || 0;
            var device_name = $('#trade_in_pos_device_name').val() || '';

            if (device_name !== '' && new_trade_in_val > 0) {
                var first_payment_row = $('#payment_rows_div').find('.payment_row').first();
                if (first_payment_row.length) {
                    var total_payable = __read_number($('#final_total_input')) || 0;
                    var adjusted_cash_amount = total_payable - new_trade_in_val;
                    if (adjusted_cash_amount < 0) {
                        adjusted_cash_amount = 0;
                    }
                    __write_number(first_payment_row.find('.payment-amount'), adjusted_cash_amount);
                    if (typeof calculate_balance_due === 'function') {
                        calculate_balance_due();
                    }
                }
            }
        }

        $(document).on('click', '#save_pos_trade_in_btn', function() {
            var val_str = $('#trade_in_pos_value').val() || '0';
            var new_trade_in_val = __read_number($('#trade_in_pos_value')) || 0;
            var device_name = $('#trade_in_pos_device_name').val() || '';

            if (device_name !== '' && new_trade_in_val > 0) {
                apply_trade_in_deduction();
                $('#pos_trade_in_badge').removeClass('hide').text('Rp ' + val_str);
                $('#pos_trade_in_btn i').removeClass('tw-text-orange-500').addClass('tw-text-green-500');
                toastr.success('Potongan Tukar Tambah Rp ' + val_str + ' berhasil diterapkan!');
            } else {
                $('#pos_trade_in_badge').addClass('hide').text('');
                $('#pos_trade_in_btn i').removeClass('tw-text-green-500').addClass('tw-text-orange-500');
            }
        });

        $('#modal_payment').on('shown.bs.modal', function() {
            apply_trade_in_deduction();
        });
    });
</script>
