<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\ProductSerialNumberController::class, 'update'], [$serial->id]), 'method' => 'put', 'id' => 'edit_serial_number_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Edit Serial Number: {{ $serial->serial_number }}</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('serial_number', 'Serial Number / IMEI:*') !!}
        {!! Form::text('serial_number', $serial->serial_number, ['class' => 'form-control', 'required']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('purchase_price', 'Harga Beli (HPP):') !!}
        {!! Form::text('purchase_price', $serial->purchase_price !== null ? @num_format($serial->purchase_price) : null, ['class' => 'form-control input_number', 'placeholder' => 'Default']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('selling_price', 'Harga Jual Custom:') !!}
        {!! Form::text('selling_price', $serial->selling_price !== null ? @num_format($serial->selling_price) : null, ['class' => 'form-control input_number', 'placeholder' => 'Default']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('status', 'Status:*') !!}
        {!! Form::select('status', [
          'in_stock' => 'Tersedia (In Stock)',
          'sold' => 'Terjual (Sold)',
          'used_in_repair' => 'Digunakan di Repair',
          'returned' => 'Retur'
        ], $serial->status, ['class' => 'form-control', 'required']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('notes', 'Catatan / Deskripsi Perangkat:') !!}
        {!! Form::textarea('notes', $serial->notes, ['class' => 'form-control', 'rows' => 3]); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
    </div>

    {!! Form::close() !!}

  </div>
</div>
