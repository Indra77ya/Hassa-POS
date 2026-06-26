@extends('repair::layouts.repair_status')
@section('title', __('repair::lang.repair_status'))
@section('content')
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <div
                class="tw-p-5 md:tw-p-6 tw-mb-4 tw-rounded-2xl tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-ring-1 tw-ring-gray-200">
                <div class="tw-flex tw-flex-col tw-gap-4 tw-dw-rounded-box tw-dw-p-6 tw-dw-max-w-md">
                    <div class="tw-flex tw-items-center tw-flex-col">
                        <h1 class="tw-text-lg md:tw-text-xl tw-font-semibold tw-text-[#1e1e1e]">
                            {{ __('repair::lang.repair_status') }}
                        </h1>
                        <h2 class="tw-text-sm tw-font-medium tw-text-gray-500">
                            @lang('lang_v1.enter_details_below_to_check_repair_status')
                        </h2>
                    </div>
                    @php
                        $search_options = [
                            'job_sheet_no' => __('repair::lang.job_sheet_no'),
                            'invoice_no' => __('sale.invoice_no'),
                        ];

                        $placeholder = __('repair::lang.job_sheet_or_invoice_no');

                        if (config('repair.enable_repair_check_using_mobile_num')) {
                            $search_options['mobile_num'] = __('lang_v1.mobile_number');
                            $placeholder .= ' / ' . __('lang_v1.mobile_number');
                        }
                    @endphp
                    <form form method="POST"
                        action="{{ action([\Modules\Repair\Http\Controllers\CustomerRepairStatusController::class, 'postRepairStatus']) }}"
                        id="check_repair_status">
                        {{ csrf_field() }}
                        <label class="tw-dw-form-control">
                            <div class="tw-dw-label">
                                <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-black">@lang('lang_v1.search_by')</span>
                            </div>
                            {!! Form::select('search_type', $search_options, null, [
                                'class' =>
                                    'tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 tw-bg-transparent tw-rounded-lg tw-px-3 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium',
                            ]) !!}
                        </label>
                        <label class="tw-dw-form-control">
                            {!! Form::text('search_number', null, [
                                'class' =>
                                    'tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 tw-bg-transparent tw-rounded-lg tw-px-3 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium',
                                'required',
                                'placeholder' => $placeholder,
                            ]) !!}
                        </label>
                        <label class="tw-dw-form-control">
                            <input type="text" name="serial_no"
                                class="tw-border tw-border-[#D1D5DA] tw-outline-none tw-h-12 tw-bg-transparent tw-rounded-lg tw-px-3 tw-font-medium tw-text-black placeholder:tw-text-gray-500 placeholder:tw-font-medium"
                                id="repair_serial_no" placeholder="@lang('repair::lang.serial_no')">
                        </label>
                        <button type="submit"
                            class="ladda-button tw-bg-gradient-to-r tw-from-indigo-500 tw-to-blue-500 tw-h-12 tw-rounded-xl tw-text-sm md:tw-text-base tw-text-white tw-font-semibold tw-w-full tw-max-w-full mt-2 hover:tw-from-indigo-600 hover:tw-to-blue-600 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-ring-offset-2 active:tw-from-indigo-700 active:tw-to-blue-700">
                            @lang('lang_v1.search')
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
        </div>
        <div class="col-md-12 col-xs-12">
            <div
                class="tw-rounded-2xl">
                <div class="tw-flex tw-flex-col tw-gap-4 tw-dw-rounded-box tw-dw-p-6 tw-dw-max-w-md repair_status_details">
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('submit', 'form#check_repair_status', function(e) {
                e.preventDefault();
                var data = $('form#check_repair_status').serialize();
                var url = $('form#check_repair_status').attr('action');
                var ladda = Ladda.create(document.querySelector('.ladda-button'));
                ladda.start();
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        ladda.stop();
                        if (result.success) {
                            $(".repair_status_details").html(result.repair_html);
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });
        });
    </script>
@endsection
