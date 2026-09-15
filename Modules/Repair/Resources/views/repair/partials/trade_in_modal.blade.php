<div class="modal fade" tabindex="-1" role="dialog" id="trade_in_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fas fa-sync-alt"></i> @lang('repair::lang.trade_in') (@lang('repair::lang.trade_in_details'))</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_device_name">@lang('repair::lang.device') / @lang('repair::lang.model'):*</label>
                            <input type="text" class="form-control" id="trade_in_device_name" name="device_name" placeholder="Contoh: iPhone 11 64GB" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_serial_no">@lang('repair::lang.imei_sr_no'):</label>
                            <input type="text" class="form-control" id="trade_in_serial_no" name="serial_no" placeholder="Nomor Seri / IMEI">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_condition">@lang('repair::lang.condition_of_product'):</label>
                            <input type="text" class="form-control" id="trade_in_condition" name="condition" placeholder="Contoh: Mulus 90%, Baterai 85%">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="trade_in_value">@lang('repair::lang.trade_in_amount') (Rp):*</label>
                            <input type="text" class="form-control input_number trade_in_value_input" id="trade_in_value" name="modal_trade_in_amount" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="trade_in_notes">@lang('brand.note'):</label>
                            <textarea class="form-control" id="trade_in_notes" name="notes" rows="2" placeholder="Catatan tambahan perangkat bekas..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-flat save_trade_in_btn" id="save_trade_in_btn" data-dismiss="modal"><i class="fa fa-check"></i> @lang('repair::lang.save_and_use')</button>
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">@lang('messages.close')</button>
            </div>
        </div>
    </div>
</div>
