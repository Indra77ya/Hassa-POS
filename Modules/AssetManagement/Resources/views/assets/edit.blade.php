@extends('layouts.app')
@section('title', __('assetmanagement::lang.edit_asset'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-text-black">{{ __('assetmanagement::lang.edit_asset') }}</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        {!! Form::open(['url' => action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'update'], [$asset->id]), 'method' => 'put', 'id' => 'edit_asset_form']) !!}
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('fixed_asset_account_id', 'Akun Aset Tetap (Debit):') !!}
                    {!! Form::select('fixed_asset_account_id', $fixed_asset_accounts, $asset->fixed_asset_account_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('payment_account_id', 'Sumber Dana / Akun Pembayaran (Kredit):') !!}
                    {!! Form::select('payment_account_id', $payment_accounts, $asset->payment_account_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('name', __('assetmanagement::lang.asset_name') . ':*') !!}
                    {!! Form::text('name', $asset->name, ['class' => 'form-control', 'required', 'placeholder' => __('assetmanagement::lang.asset_name')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('asset_code', __('assetmanagement::lang.asset_code') . ':') !!}
                    {!! Form::text('asset_code', $asset->asset_code, ['class' => 'form-control', 'placeholder' => __('assetmanagement::lang.asset_code')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('asset_category_id', __('assetmanagement::lang.asset_category') . ':') !!}
                    {!! Form::select('asset_category_id', $categories, $asset->asset_category_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('assetmanagement::lang.location') . ':') !!}
                    {!! Form::select('location_id', $locations, $asset->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('purchase_date', __('assetmanagement::lang.purchase_date') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        {!! Form::text('purchase_date', @format_date($asset->purchase_date), ['class' => 'form-control date-picker', 'required', 'readonly']) !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('purchase_price', __('assetmanagement::lang.purchase_price') . ':*') !!}
                    {!! Form::text('purchase_price', @num_format($asset->purchase_price), ['class' => 'form-control input_number', 'required', 'placeholder' => __('assetmanagement::lang.purchase_price')]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('salvage_value', __('assetmanagement::lang.salvage_value') . ':') !!}
                    {!! Form::text('salvage_value', @num_format($asset->salvage_value), ['class' => 'form-control input_number', 'placeholder' => __('assetmanagement::lang.salvage_value')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('useful_life', __('assetmanagement::lang.useful_life') . ':*') !!}
                    {!! Form::number('useful_life', $asset->useful_life, ['class' => 'form-control', 'required', 'min' => 1, 'placeholder' => __('assetmanagement::lang.useful_life_in_months')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('depreciation_method', __('assetmanagement::lang.depreciation_method') . ':*') !!}
                    {!! Form::select('depreciation_method', ['straight_line' => __('assetmanagement::lang.straight_line')], $asset->depreciation_method, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('status', __('assetmanagement::lang.status') . ':*') !!}
                    {!! Form::select('status', ['active' => 'Active', 'sold' => 'Sold', 'disposed' => 'Disposed'], $asset->status, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    {!! Form::label('description', __('lang_v1.description') . ':') !!}
                    {!! Form::textarea('description', $asset->description, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.description')]) !!}
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
