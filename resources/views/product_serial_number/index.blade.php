@extends('layouts.app')
@section('title', __('lang_v1.serial_numbers'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.serial_numbers')
        <small>Serial Number / IMEI Tracking List</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Serial Number / IMEI Tracking'])
        <div class="row tw-mb-4">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('filter_status', __('sale.status') . ':') !!}
                    {!! Form::select('filter_status', ['' => 'All Status', 'in_stock' => __('lang_v1.in_stock'), 'sold' => __('lang_v1.sold'), 'used_in_repair' => 'In Repair', 'returned' => 'Returned'], null, ['class' => 'form-control select2', 'id' => 'filter_status', 'style' => 'width:100%']); !!}
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="product_serial_numbers_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Serial Number / IMEI</th>
                        <th>@lang('product.product_name')</th>
                        <th>@lang('lang_v1.sn_purchase_price')</th>
                        <th>@lang('lang_v1.sn_selling_price')</th>
                        <th>@lang('sale.status')</th>
                        <th>Purchase Ref No</th>
                        <th>Sell Invoice No</th>
                        <th>@lang('messages.date')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
    var sn_table = $('#product_serial_numbers_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/product-serial-numbers',
            data: function(d) {
                d.status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'serial_number', name: 'product_serial_numbers.serial_number' },
            { data: 'product', name: 'products.name' },
            { data: 'purchase_price', name: 'product_serial_numbers.purchase_price' },
            { data: 'selling_price', name: 'product_serial_numbers.selling_price' },
            { data: 'status', name: 'product_serial_numbers.status' },
            { data: 'purchase_ref', name: 'purch_tx.ref_no' },
            { data: 'sell_invoice', name: 'sell_tx.invoice_no' },
            { data: 'created_at', name: 'product_serial_numbers.created_at' }
        ],
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#product_serial_numbers_table'));
        }
    });

    $('#filter_status').change(function(){
        sn_table.ajax.reload();
    });
});
</script>
@endsection
