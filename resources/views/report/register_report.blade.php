@extends('layouts.app')
@section('title', __('report.register_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('report.register_report')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']), 'method' => 'get', 'id' => 'register_report_filter_form' ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('register_user_id',  __('report.user') . ':') !!}
                        {!! Form::select('register_user_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('report.all_users')]); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('register_status',  __('sale.status') . ':') !!}
                        {!! Form::select('register_status', ['open' => __('cash_register.open'), 'close' => __('cash_register.close')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('report.all')]); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('register_report_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('register_report_date_range', null , ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'register_report_date_range', 'readonly']); !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="register_report_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="white-space: nowrap !important;">@lang('report.open_time')</th>
                                <th style="white-space: nowrap !important;">@lang('report.close_time')</th>
                                <th style="white-space: nowrap !important;">@lang('sale.location')</th>
                                <th style="white-space: nowrap !important;">@lang('report.user')</th>
                                <th style="white-space: nowrap !important;">@lang('cash_register.total_card_slips')</th>
                                <th style="white-space: nowrap !important;">@lang('cash_register.total_cheques')</th>
                                <th style="white-space: nowrap !important;">@lang('cash_register.total_cash')</th>
                                <th style="white-space: nowrap !important;">@lang('lang_v1.total_bank_transfer')</th>
                                <th style="white-space: nowrap !important;">@lang('lang_v1.total_advance_payment')</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_1']}}</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_2']}}</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_3']}}</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_4']}}</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_5']}}</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_6']}}</th>
                                <th style="white-space: nowrap !important;">{{$payment_types['custom_pay_7']}}</th>
                                <th style="white-space: nowrap !important;">@lang('cash_register.other_payments')</th>
                                <th style="white-space: nowrap !important;">@lang('sale.total')</th>
                                <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
                            </tr>
                        </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                            <td class="footer_total_card_payment"></td>
                            <td class="footer_total_cheque_payment"></td>
                            <td class="footer_total_cash_payment"></td>
                            <td class="footer_total_bank_transfer_payment"></td>
                            <td class="footer_total_advance_payment"></td>'
                            <td class="footer_total_custom_pay_1"></td>
                            <td class="footer_total_custom_pay_2"></td>
                            <td class="footer_total_custom_pay_3"></td>
                            <td class="footer_total_custom_pay_4"></td>
                            <td class="footer_total_custom_pay_5"></td>
                            <td class="footer_total_custom_pay_6"></td>
                            <td class="footer_total_custom_pay_7"></td>
                            <td class="footer_total_other_payments"></td>
                            <td class="footer_total"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
    @endphp
    <script type="text/javascript">
        window.register_payment_labels = {
            custom_pay_1: "{{ !empty($custom_labels['payments']['custom_pay_1']) ? 1 : 0 }}",
            custom_pay_2: "{{ !empty($custom_labels['payments']['custom_pay_2']) ? 1 : 0 }}",
            custom_pay_3: "{{ !empty($custom_labels['payments']['custom_pay_3']) ? 1 : 0 }}",
            custom_pay_4: "{{ !empty($custom_labels['payments']['custom_pay_4']) ? 1 : 0 }}",
            custom_pay_5: "{{ !empty($custom_labels['payments']['custom_pay_5']) ? 1 : 0 }}",
            custom_pay_6: "{{ !empty($custom_labels['payments']['custom_pay_6']) ? 1 : 0 }}",
            custom_pay_7: "{{ !empty($custom_labels['payments']['custom_pay_7']) ? 1 : 0 }}"
        };
    </script>
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection