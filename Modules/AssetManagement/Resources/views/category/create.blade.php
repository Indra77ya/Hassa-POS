<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'store']), 'method' => 'post', 'id' => 'asset_category_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'assetmanagement::lang.add_category' )</h4>
    </div>

    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __( 'user.name' ) . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'user.name' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('description', __( 'lang_v1.description' ) . ':') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __( 'lang_v1.description' ) ]); !!}
      </div>

      <div class="form-group">
        {!! Form::label('depreciation_expense_account_id', __( 'assetmanagement::lang.depreciation_expense_account' ) . ':') !!}
        {!! Form::select('depreciation_expense_account_id', $expense_accounts, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']); !!}
      </div>

      <div class="form-group">
        {!! Form::label('accumulated_depreciation_account_id', __( 'assetmanagement::lang.accumulated_depreciation_account' ) . ':') !!}
        {!! Form::select('accumulated_depreciation_account_id', $asset_accounts, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']); !!}
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div>
</div>
