<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'store']), 'method' => 'post', 'id' => 'asset_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'assetmanagement::lang.add_asset' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('name', __( 'user.name' ) . ':*') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'user.name' ) ]); !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('sku', 'SKU / Ref No:') !!}
            {!! Form::text('sku', null, ['class' => 'form-control', 'placeholder' => 'SKU / Ref No' ]); !!}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('asset_category_id', __( 'assetmanagement::lang.categories' ) . ':') !!}
            {!! Form::select('asset_category_id', $categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']); !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('purchase_date', __( 'assetmanagement::lang.purchase_date' ) . ':*') !!}
            {!! Form::text('purchase_date', @format_date('now'), ['class' => 'form-control date-picker', 'required', 'readonly']); !!}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('historical_cost', __( 'assetmanagement::lang.historical_cost' ) . ':*') !!}
            {!! Form::text('historical_cost', 0, ['class' => 'form-control input_number', 'required']); !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('salvage_value', __( 'assetmanagement::lang.salvage_value' ) . ':') !!}
            {!! Form::text('salvage_value', 0, ['class' => 'form-control input_number']); !!}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('useful_life_months', __( 'assetmanagement::lang.useful_life_months' ) . ':*') !!}
            {!! Form::number('useful_life_months', 12, ['class' => 'form-control', 'required', 'min' => 1]); !!}
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('depreciation_method', __( 'assetmanagement::lang.depreciation_method' ) . ':*') !!}
            {!! Form::select('depreciation_method', ['straight_line' => __('assetmanagement::lang.straight_line')], 'straight_line', ['class' => 'form-control', 'required']); !!}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            {!! Form::label('notes', __( 'brand.note' ) . ':') !!}
            {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]); !!}
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="checkbox">
            <label>
              {!! Form::checkbox('is_active', 1, true, ['class' => 'input-icheck']); !!} @lang('assetmanagement::lang.active')
            </label>
          </div>
        </div>
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div>
</div>
