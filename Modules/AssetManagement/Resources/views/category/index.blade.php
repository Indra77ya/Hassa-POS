@extends('layouts.app')
@section('title', __('assetmanagement::lang.categories'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('assetmanagement::lang.categories')
        <small>@lang('assetmanagement::lang.categories')</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('assetmanagement::lang.categories')])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal"
                    data-href="{{action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'create'])}}"
                    data-container=".category_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="asset_category_table">
                <thead>
                    <tr>
                        <th>@lang('user.name')</th>
                        <th>@lang('lang_v1.description')</th>
                        <th>@lang('assetmanagement::lang.depreciation_expense_account')</th>
                        <th>@lang('assetmanagement::lang.accumulated_depreciation_account')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade category_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var asset_category_table = $('#asset_category_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, "index"])}}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'expense_account', name: 'expense_account', searchable: false },
                { data: 'accumulated_account', name: 'accumulated_account', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('submit', 'form#asset_category_add_form, form#asset_category_edit_form', function(e) {
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
                        $('div.category_modal').modal('hide');
                        toastr.success(result.msg);
                        asset_category_table.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', 'a.delete_category_button', function(e) {
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
                                asset_category_table.ajax.reload();
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
