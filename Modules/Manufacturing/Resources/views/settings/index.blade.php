@extends('layouts.app')
@section('title', __('messages.settings'))

@section('content')
@include('manufacturing::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('messages.settings')</h1>
</section>

<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => action([\Modules\Manufacturing\Http\Controllers\SettingsController::class, 'store']), 'method' => 'post', 'id' => 'manufacturing_settings_form' ]) !!}
    <div class="row">
        <div class="col-xs-12">
           <!--  <pos-tab-container> -->
            {{-- <div class="col-xs-12 pos-tab-container"> --}}
                @component('components.widget', ['class' =>  'pos-tab-container'])
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu tw-rounded-lg">
                    <div class="list-group">
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base active">@lang('messages.settings')</a>
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">Pemetaan Akun</a>
                    </div>
                </div>
                <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                    <div class="pos-tab-content active">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('ref_no_prefix', __('manufacturing::lang.mfg_ref_no_prefix') . ':' ) !!}
                                    {!! Form::text('ref_no_prefix', !empty($manufacturing_settings['ref_no_prefix']) ? $manufacturing_settings['ref_no_prefix'] : null, ['placeholder' => __('manufacturing::lang.mfg_ref_no_prefix'), 'class' => 'form-control']); !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <br>
                                    <div class="checkbox">
                                        <label>
                                        {!! Form::checkbox('disable_editing_ingredient_qty', 1, !empty($manufacturing_settings['disable_editing_ingredient_qty']), ['class' => 'input-icheck', 'id' => 'disable_editing_ingredient_qty']); !!} @lang('manufacturing::lang.disable_editing_ingredient_qty')
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <br>
                                    <div class="checkbox">
                                        <label>
                                        {!! Form::checkbox('enable_updating_product_price', 1, !empty($manufacturing_settings['enable_updating_product_price']), ['class' => 'input-icheck', 'id' => 'enable_updating_product_price']); !!} @lang('manufacturing::lang.enable_editing_product_price_after_production')
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pos-tab-content">
                        <div class="row">
                            <div class="col-md-12 text-right tw-mb-4">
                                <button type="button" class="btn btn-info btn-sm" id="btn_auto_map_mfg">
                                    <i class="fas fa-magic"></i> Auto Mapping Akun Default
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_raw_material_account_id', 'Akun Persediaan Bahan Baku (Raw Material):') !!}
                                    {!! Form::select('mfg_raw_material_account_id', $accounting_accounts, !empty($manufacturing_settings['mfg_raw_material_account_id']) ? $manufacturing_settings['mfg_raw_material_account_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                    <p class="help-block">Akun persediaan yang dikreditkan saat bahan baku digunakan pada proses produksi.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_finished_goods_account_id', 'Akun Persediaan Barang Jadi (Finished Goods):') !!}
                                    {!! Form::select('mfg_finished_goods_account_id', $accounting_accounts, !empty($manufacturing_settings['mfg_finished_goods_account_id']) ? $manufacturing_settings['mfg_finished_goods_account_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                    <p class="help-block">Akun persediaan yang didebit saat barang hasil produksi difinalisasi.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_production_cost_account_id', 'Akun Biaya Produksi / Overhead:') !!}
                                    {!! Form::select('mfg_production_cost_account_id', $accounting_accounts, !empty($manufacturing_settings['mfg_production_cost_account_id']) ? $manufacturing_settings['mfg_production_cost_account_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                    <p class="help-block">Akun biaya overhead produksi yang dikreditkan apabila biaya tambahan produksi tidak dipotong dari Kas/Bank.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_payment_account_id', 'Akun Pembayaran Biaya Produksi (Kas / Bank):') !!}
                                    {!! Form::select('mfg_payment_account_id', $pos_accounts, !empty($manufacturing_settings['mfg_payment_account_id']) ? $manufacturing_settings['mfg_payment_account_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                    <p class="help-block">Akun Kas/Bank pada modul Payment Account yang digunakan untuk membayar biaya tambahan produksi.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endcomponent
                
            {{-- </div> --}}
            <!--  </pos-tab-container> -->
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right">@lang('messages.update')</button>
        </div>
    </div>

    <div class="col-xs-12">
        <p class="help-block"><i>{!! __('manufacturing::lang.version_info', ['version' => $version]) !!}</i></p>
    </div>
    {!! Form::close() !!}
</section>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready( function () {
        $(".file-input").fileinput(fileinput_setting);

        $(document).on('click', '#btn_auto_map_mfg', function () {
            swal({
                title: 'Konfirmasi Auto Mapping',
                text: 'Apakah Anda yakin ingin memetakan atau membuatkan akun default Manufaktur secara otomatis?',
                icon: 'info',
                buttons: true,
                dangerMode: false,
            }).then(willMap => {
                if (willMap) {
                    $.ajax({
                        method: 'POST',
                        url: "{{ action([\Modules\Manufacturing\Http\Controllers\SettingsController::class, 'autoMapAccounts']) }}",
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(result) {
                            if (result.success) {
                                toastr.success(result.msg);
                                window.location.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>

@endsection