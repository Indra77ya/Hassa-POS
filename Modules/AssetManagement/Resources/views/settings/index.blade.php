@extends('layouts.app')
@section('title', __('assetmanagement::lang.asset_settings'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('assetmanagement::lang.asset_settings') }}</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('assetmanagement::lang.asset_settings')])
        {!! Form::open(['url' => action([\Modules\AssetManagement\Http\Controllers\AssetSettingController::class, 'store']), 'method' => 'post']) !!}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('depreciation_expense_account_id', __('assetmanagement::lang.depreciation_expense_account') . ':*') !!}
                    {!! Form::select('depreciation_expense_account_id', $accounts, $setting->depreciation_expense_account_id, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('accumulated_depreciation_account_id', __('assetmanagement::lang.accumulated_depreciation_account') . ':*') !!}
                    {!! Form::select('accumulated_depreciation_account_id', $accounts, $setting->accumulated_depreciation_account_id, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            </div>
        </div>
        {!! Form::close() !!}
    @endcomponent
</section>
@endsection
