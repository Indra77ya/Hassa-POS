@extends('layouts.app')
@section('title', __('assetmanagement::lang.assets'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('assetmanagement::lang.assets')
        <small>@lang('assetmanagement::lang.assets')</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('assetmanagement::lang.assets')])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal"
                    data-href="{{action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'create'])}}"
                    data-container=".asset_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="asset_table">
                <thead>
                    <tr>
                        <th>SKU / Ref</th>
                        <th>@lang('user.name')</th>
                        <th>@lang('assetmanagement::lang.categories')</th>
                        <th>@lang('assetmanagement::lang.purchase_date')</th>
                        <th>@lang('assetmanagement::lang.historical_cost')</th>
                        <th>@lang('assetmanagement::lang.salvage_value')</th>
                        <th>@lang('assetmanagement::lang.accumulated_depreciation')</th>
                        <th>@lang('assetmanagement::lang.net_book_value')</th>
                        <th>@lang('sale.status')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade asset_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var asset_table = $('#asset_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{action([\Modules\AssetManagement\Http\Controllers\AssetController::class, "index"])}}',
            columns: [
                { data: 'sku', name: 'sku' },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category_name', searchable: false },
                { data: 'purchase_date', name: 'purchase_date' },
                { data: 'historical_cost', name: 'historical_cost' },
                { data: 'salvage_value', name: 'salvage_value' },
                { data: 'accumulated_depreciation', name: 'accumulated_depreciation', searchable: false },
                { data: 'net_book_value', name: 'net_book_value', searchable: false },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('submit', 'form#asset_add_form, form#asset_edit_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: form.attr('method'),
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success === true) {
                        $('div.asset_modal').modal('hide');
                        toastr.success(result.msg);
                        asset_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', 'a.delete_asset_button', function(e) {
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
                            if (result.success === true) {
                                toastr.success(result.msg);
                                asset_table.ajax.reload();
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
