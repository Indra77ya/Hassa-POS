@extends('layouts.app')
@section('title', __('laundry::lang.order_sheets'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.order_sheets')</h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('laundry_status_id', __('laundry::lang.status') . ':') !!}
                {!! Form::select('laundry_status_id', $statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('laundry_service_type_id', __('laundry::lang.service_type') . ':') !!}
                {!! Form::select('laundry_service_type_id', $service_types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-primary" href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'create']) }}">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </a>
                {!! Form::open(['url' => route('laundry.import_demo_data'), 'method' => 'post', 'style' => 'display:inline-block; margin-left: 5px;']) !!}
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin memasukkan data demo laundry?')">
                        <i class="fa fa-database"></i> @lang('laundry::lang.import_demo_data')
                    </button>
                {!! Form::close() !!}
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="order_sheets_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('laundry::lang.order_no')</th>
                        <th>@lang('contact.customer')</th>
                        <th>@lang('purchase.business_location')</th>
                        <th>@lang('laundry::lang.service_type')</th>
                        <th>@lang('laundry::lang.item_type')</th>
                        <th>@lang('laundry::lang.quantity')</th>
                        <th>@lang('laundry::lang.status')</th>
                        <th>@lang('laundry::lang.received_at')</th>
                        <th>@lang('laundry::lang.estimated_completion_at')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
@endsection

@section('javascript')
@include('laundry::layouts.partials.javascripts')
<script type="text/javascript">
$(document).ready(function() {
    window.order_sheets_table = $('#order_sheets_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index']) }}',
            data: function(d) {
                d.location_id = $('#location_id').val();
                d.laundry_status_id = $('#laundry_status_id').val();
                d.laundry_service_type_id = $('#laundry_service_type_id').val();
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'order_no', name: 'order_no' },
            { data: 'customer.name', name: 'customer.name' },
            { data: 'location.name', name: 'location.name' },
            { data: 'service_type.name', name: 'service_type.name', defaultContent: '-' },
            { data: 'item_type.name', name: 'item_type.name', defaultContent: '-' },
            { data: 'quantity', name: 'quantity' },
            { data: 'status', name: 'status' },
            { data: 'received_at', name: 'received_at' },
            { data: 'estimated_completion_at', name: 'estimated_completion_at' }
        ]
    });

    $(document).on('change', '#location_id, #laundry_status_id, #laundry_service_type_id', function() {
        order_sheets_table.ajax.reload();
    });

    $(document).on('click', 'a.delete_order_sheet_button', function(e) {
        e.preventDefault();
        var href = $(this).attr('data-href');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                            order_sheets_table.ajax.reload();
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
