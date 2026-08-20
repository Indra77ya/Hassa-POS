@extends('layouts.app')
@section('title', __('assetmanagement::lang.assets'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('assetmanagement::lang.assets') }}</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_category_id', __('assetmanagement::lang.asset_category') . ':') !!}
                {!! Form::select('filter_category_id', $categories, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_location_id', __('assetmanagement::lang.location') . ':') !!}
                {!! Form::select('filter_location_id', $locations, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('filter_status', __('assetmanagement::lang.status') . ':') !!}
                {!! Form::select('filter_status', ['active' => 'Active', 'sold' => 'Sold', 'disposed' => 'Disposed'], null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __('assetmanagement::lang.assets')])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{ action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'create']) }}">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="assets_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('assetmanagement::lang.asset_code')</th>
                        <th>@lang('assetmanagement::lang.asset_name')</th>
                        <th>@lang('assetmanagement::lang.asset_category')</th>
                        <th>@lang('assetmanagement::lang.location')</th>
                        <th>@lang('assetmanagement::lang.purchase_date')</th>
                        <th>@lang('assetmanagement::lang.purchase_price')</th>
                        <th>@lang('assetmanagement::lang.monthly_depreciation')</th>
                        <th>@lang('assetmanagement::lang.accumulated_depreciation')</th>
                        <th>@lang('assetmanagement::lang.net_book_value')</th>
                        <th>@lang('assetmanagement::lang.status')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var assets_table = $('#assets_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'index']) }}",
            data: function(d) {
                d.category_id = $('#filter_category_id').val();
                d.location_id = $('#filter_location_id').val();
                d.status = $('#filter_status').val();
            }
        },
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'asset_code', name: 'assets.asset_code' },
            { data: 'name', name: 'assets.name' },
            { data: 'category_name', name: 'asset_categories.name' },
            { data: 'location_name', name: 'business_locations.name' },
            { data: 'purchase_date', name: 'assets.purchase_date' },
            { data: 'purchase_price', name: 'assets.purchase_price' },
            { data: 'monthly_depreciation', name: 'monthly_depreciation', orderable: false, searchable: false },
            { data: 'total_accumulated_depreciation', name: 'total_accumulated_depreciation', orderable: false, searchable: false },
            { data: 'net_book_value', name: 'net_book_value', orderable: false, searchable: false },
            { data: 'status', name: 'assets.status' }
        ]
    });

    $(document).on('change', '#filter_category_id, #filter_location_id, #filter_status', function() {
        assets_table.ajax.reload();
    });

    $(document).on('click', '.delete_asset_button', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
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
                        if (result.success == true) {
                            toastr.success(result.msg);
                            assets_table.ajax.reload();
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
