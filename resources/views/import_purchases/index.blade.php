@extends('layouts.app')
@section('title', __('lang_v1.import_purchases'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('lang_v1.import_purchases')</h1>
</section>

<!-- Main content -->
<section class="content">
    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            @component('components.widget')
                {!! Form::open(['url' => action([\App\Http\Controllers\ImportPurchasesController::class, 'preview']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                        <div class="col-sm-8">
                            <div class="form-group">
                                {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                {!! Form::file('purchases', ['accept' => '.xls, .xlsx, .csv', 'required' => 'required']); !!}
                              </div>
                        </div>
                        <div class="col-sm-4">
                        <br>
                            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-rounded-xl">@lang('lang_v1.upload_and_review')</button>
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <br>
                            <a href="{{ asset('files/import_purchases_template.csv') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (CSV)</a>
                            &nbsp;
                            <a href="{{ asset('files/import_purchases_template.xls') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (Excel)</a>
                        </div>
                    </div>
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['title' => __('lang_v1.template_example')])
                <p class="text-muted"><small>@lang('lang_v1.template_example_instruction')</small></p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="bg-gray">
                                <th>Reference No</th>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Supplier Phone</th>
                                <th>Purchase Date</th>
                                <th>Purchase Status</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Unit Cost Before Discount</th>
                                <th>Discount Percent</th>
                                <th>Item Tax</th>
                                <th>Selling Price</th>
                                <th>Serial Numbers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PUR-2026-001</td>
                                <td><span class="text-muted">(kosong)</span></td>
                                <td>Toko Komputer Perkasa</td>
                                <td>08123456789</td>
                                <td>2026-10-06 10:00:00</td>
                                <td><span class="label label-success">received</span></td>
                                <td>Laptop Lenovo ThinkPad</td>
                                <td>SKU-THINKPAD</td>
                                <td>2</td>
                                <td>12.000.000</td>
                                <td>0</td>
                                <td><span class="text-muted">(kosong)</span></td>
                                <td>14.000.000</td>
                                <td>SN1001, SN1002</td>
                            </tr>
                            <tr>
                                <td>PUR-2026-001</td>
                                <td><span class="text-muted">(kosong)</span></td>
                                <td>Toko Komputer Perkasa</td>
                                <td>08123456789</td>
                                <td>2026-10-06 10:00:00</td>
                                <td><span class="label label-success">received</span></td>
                                <td>Mouse Wireless Logitech</td>
                                <td>SKU-LOGIMOUSE</td>
                                <td>5</td>
                                <td>150.000</td>
                                <td>0</td>
                                <td><span class="text-muted">(kosong)</span></td>
                                <td>200.000</td>
                                <td><span class="text-muted">(kosong)</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['title' => __('lang_v1.instructions')])
                <table class="table table-condensed">
                    <tr>
                        <td>1.</td>
                        <td>@lang('lang_v1.upload_data_in_excel_format')</td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>@lang('lang_v1.choose_location_and_group_by')</td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>@lang('lang_v1.map_columns_with_purchase_fields')</td>
                    </tr>
                </table>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['title' => __('lang_v1.import_fields')])
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.import_field')</th>
                            <th>@lang('lang_v1.instruction')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($import_fields as $key => $value)
                            <tr>
                                <td>
                                    {{$value['label']}}
                                    <small class="text-muted">
                                        @if(!empty($value['is_optional']))
                                            (@lang('lang_v1.optional'))
                                        @else
                                            (@lang('lang_v1.required'))
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        @if(!empty($value['instruction']))
                                            {{$value['instruction']}}
                                        @endif
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['title' => __('lang_v1.imports_history')])
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('lang_v1.import_time')</th>
                            <th>@lang('lang_v1.total_records')</th>
                            <th>@lang('lang_v1.created_by')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($imported_purchases_array as $key => $value)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{@format_datetime($value['import_time'])}}</td>
                                <td>{{count($value['ref_nos'])}}</td>
                                <td>{{$value['created_by']}}</td>
                                <td>
                                    <a href="{{action([\App\Http\Controllers\ImportPurchasesController::class, 'revertPurchaseImport'], [$key])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" onclick="return confirm('{{__('messages.are_you_sure')}}');"><i class="fa fa-undo"></i> @lang('lang_v1.revert_batch_import')</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
@endsection
