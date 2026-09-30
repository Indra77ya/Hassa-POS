@extends('layouts.app')
@section('title', 'Daftar Serial Number / IMEI')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Daftar Serial Number / IMEI
        <small class="tw-text-sm tw-font-normal tw-text-gray-700">Kelola dan tuju status unit barang berpivot serial number</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations ?? [], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('status_filter', 'Status:') !!}
                {!! Form::select('status_filter', [
                    'in_stock' => 'Tersedia (In Stock)',
                    'sold' => 'Terjual (Sold)',
                    'used_in_repair' => 'Digunakan di Repair',
                    'returned' => 'Retur'
                ], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Semua Serial Number / IMEI'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="product_serial_numbers_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>@lang('product.product_name')</th>
                        <th>SKU</th>
                        <th>@lang('purchase.business_location')</th>
                        <th>Harga Beli (HPP)</th>
                        <th>Harga Jual Custom</th>
                        <th>Status</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    var serial_table = $('#product_serial_numbers_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ action([\App\Http\Controllers\ProductSerialNumberController::class, 'index']) }}",
            data: function(d) {
                d.location_id = $('#location_id').val();
                d.status = $('#status_filter').val();
            }
        },
        columns: [
            { data: 'serial_number', name: 'product_serial_numbers.serial_number' },
            { data: 'product_name', name: 'products.name' },
            { data: 'sub_sku', name: 'variations.sub_sku' },
            { data: 'location_name', name: 'business_locations.name' },
            { data: 'purchase_price', name: 'product_serial_numbers.purchase_price' },
            { data: 'selling_price', name: 'product_serial_numbers.selling_price' },
            { data: 'status', name: 'product_serial_numbers.status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $(document).on('change', '#location_id, #status_filter', function() {
        serial_table.ajax.reload();
    });

    $(document).on('submit', 'form#edit_serial_number_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success === true) {
                    $('.view_modal').modal('hide');
                    toastr.success(result.msg);
                    serial_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
});
</script>
@endsection
