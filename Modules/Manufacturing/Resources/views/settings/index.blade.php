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
                        <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('accounting::lang.accounting') &amp; Account</a>
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
                        <div class="row tw-mb-4">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success btn-sm pull-right" id="auto_map_accounts_btn">
                                    <i class="fas fa-magic"></i> Auto Mapping Akun
                                </button>
                                <p class="help-block">Klik <strong>Auto Mapping Akun</strong> untuk secara otomatis mendeteksi atau membuat akun yang sesuai di Modul Accounting &amp; Payment Account.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_raw_material_account_id', 'Akun Persediaan Bahan Baku (Raw Material Inventory):') !!}
                                    {!! Form::select('mfg_raw_material_account_id', $accounting_accounts, !empty($manufacturing_settings['mfg_raw_material_account_id']) ? $manufacturing_settings['mfg_raw_material_account_id'] : null, ['class' => 'form-control select2', 'id' => 'mfg_raw_material_account_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_finished_goods_account_id', 'Akun Persediaan Barang Jadi (Finished Goods Inventory):') !!}
                                    {!! Form::select('mfg_finished_goods_account_id', $accounting_accounts, !empty($manufacturing_settings['mfg_finished_goods_account_id']) ? $manufacturing_settings['mfg_finished_goods_account_id'] : null, ['class' => 'form-control select2', 'id' => 'mfg_finished_goods_account_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_production_cost_account_id', 'Akun Biaya Produksi / Overhead:') !!}
                                    {!! Form::select('mfg_production_cost_account_id', $accounting_accounts, !empty($manufacturing_settings['mfg_production_cost_account_id']) ? $manufacturing_settings['mfg_production_cost_account_id'] : null, ['class' => 'form-control select2', 'id' => 'mfg_production_cost_account_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('mfg_payment_account_id', 'Akun Pembayaran Kas/Bank Default:') !!}
                                    {!! Form::select('mfg_payment_account_id', $payment_accounts, !empty($manufacturing_settings['mfg_payment_account_id']) ? $manufacturing_settings['mfg_payment_account_id'] : null, ['class' => 'form-control select2', 'id' => 'mfg_payment_account_id', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
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

        $(document).on('click', '#auto_map_accounts_btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: "{{ action([\Modules\Manufacturing\Http\Controllers\SettingsController::class, 'autoMapAccounts']) }}",
                type: 'POST',
                dataType: 'json',
                data: { _token: "{{ csrf_token() }}" },
                success: function(result) {
                    btn.attr('disabled', false).html('<i class="fas fa-magic"></i> Auto Mapping Akun');
                    if (result.success) {
                        toastr.success(result.msg);
                        if (result.data) {
                            if (result.data.mfg_raw_material_account_id) {
                                if ($('#mfg_raw_material_account_id option[value="' + result.data.mfg_raw_material_account_id + '"]').length === 0) {
                                    var newOption = new Option(result.data.mfg_raw_material_account_name, result.data.mfg_raw_material_account_id, true, true);
                                    $('#mfg_raw_material_account_id').append(newOption).trigger('change');
                                } else {
                                    $('#mfg_raw_material_account_id').val(result.data.mfg_raw_material_account_id).trigger('change');
                                }
                            }
                            if (result.data.mfg_finished_goods_account_id) {
                                if ($('#mfg_finished_goods_account_id option[value="' + result.data.mfg_finished_goods_account_id + '"]').length === 0) {
                                    var newOption = new Option(result.data.mfg_finished_goods_account_name, result.data.mfg_finished_goods_account_id, true, true);
                                    $('#mfg_finished_goods_account_id').append(newOption).trigger('change');
                                } else {
                                    $('#mfg_finished_goods_account_id').val(result.data.mfg_finished_goods_account_id).trigger('change');
                                }
                            }
                            if (result.data.mfg_production_cost_account_id) {
                                if ($('#mfg_production_cost_account_id option[value="' + result.data.mfg_production_cost_account_id + '"]').length === 0) {
                                    var newOption = new Option(result.data.mfg_production_cost_account_name, result.data.mfg_production_cost_account_id, true, true);
                                    $('#mfg_production_cost_account_id').append(newOption).trigger('change');
                                } else {
                                    $('#mfg_production_cost_account_id').val(result.data.mfg_production_cost_account_id).trigger('change');
                                }
                            }
                            if (result.data.mfg_payment_account_id) {
                                if ($('#mfg_payment_account_id option[value="' + result.data.mfg_payment_account_id + '"]').length === 0) {
                                    var newOption = new Option(result.data.mfg_payment_account_name, result.data.mfg_payment_account_id, true, true);
                                    $('#mfg_payment_account_id').append(newOption).trigger('change');
                                } else {
                                    $('#mfg_payment_account_id').val(result.data.mfg_payment_account_id).trigger('change');
                                }
                            }
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    btn.attr('disabled', false).html('<i class="fas fa-magic"></i> Auto Mapping Akun');
                    toastr.error("{{ __('messages.something_went_wrong') }}");
                }
            });
        });
    });
</script>

@endsection