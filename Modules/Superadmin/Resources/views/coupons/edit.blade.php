@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Coupon')

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Main content -->
    <section class="content">

        <div class="box box-solid">
            <div class="box-header">
                <h3 class="box-title">@lang('superadmin::lang.add_new_coupon') </h3>
            </div>
            <div class="box-body">
                {!! Form::model($coupon, [
                    'url' => action(
                        [\Modules\Superadmin\Http\Controllers\CouponController::class, 'update'],
                        ['coupon' => $coupon->id],
                    ),
                    'method' => 'put',
                    'id' => 'edit_coupon',
                ]) !!}
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('coupon_code', __('superadmin::lang.coupon_code') . ':') !!}
                        {!! Form::text('coupon_code', $coupon->coupon_code, [
                            'class' => 'form-control',
                            'placeholder' => __('superadmin::lang.coupon_code'),
                            'required',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('discount_type', __('superadmin::lang.discount_type') . ':') !!}
                        {!! Form::select('discount_type', $discount_types, $coupon->discount_type, [
                            'class' => 'form-control',
                            'placeholder' => __('messages.please_select'),
                            'required',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('discount', __('superadmin::lang.discount') . ':') !!}
                        {!! Form::number('discount', $coupon->discount, [
                            'class' => 'form-control',
                            'placeholder' => __('superadmin::lang.discount'),
                            'required',
                            'step' => '0.01'
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('expiry_date', __('superadmin::lang.expiry_date') . ':') !!}
                        {!! Form::text('expiry_date', !empty($coupon->expiry_date) ? @format_date($coupon->expiry_date) : null, [
                            'class' => 'form-control',
                            'placeholder' => __('superadmin::lang.expiry_date'),
                            'readonly',
                            'id' => 'expiry_date',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('applied_on_packages', __('superadmin::lang.applied_on_packages') . ':') !!}
                        {!! Form::select('applied_on_packages[]', $packages, json_decode($coupon->applied_on_packages), [
                            'class' => 'form-control select2',
                            'multiple',
                        ]) !!}
                    <small>@lang('superadmin::lang.applied_on_packages_help_text')</small>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('applied_on_business', __('superadmin::lang.applied_on_business')) !!}
                        {!! Form::select('applied_on_business[]', $businesses, json_decode($coupon->applied_on_business), [
                            'class' => 'form-control select2',
                            'multiple',
                        ]) !!}
                        <small>@lang('superadmin::lang.applied_on_business_help_text')</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="checkbox">
                      <label>
                        {!! Form::checkbox('is_active',1 ,$coupon->is_active ,
                        [ 'class' => 'input-icheck']); !!} {{ __('superadmin::lang.is_active') }}
                      </label>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    {!! Form::submit(__('messages.update'), ['class' => 'tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-lg']) !!}
                </div>

                {!! Form::close() !!}
            </div>
        </div>

    </section>
    <!-- /.content -->
@endsection


@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#expiry_date').datepicker({
            autoclose: true
            });

            $("form#edit_coupon").validate();
        });
    </script>
@endsection
