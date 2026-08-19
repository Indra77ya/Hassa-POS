@extends('layouts.app')
@section('title', __('assetmanagement::lang.asset_categories'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('assetmanagement::lang.asset_categories') }}</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('assetmanagement::lang.asset_categories')])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" data-toggle="modal" data-target="#add_category_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="asset_categories_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('user.name')</th>
                        <th>@lang('assetmanagement::lang.asset_code')</th>
                        <th>@lang('lang_v1.description')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <!-- Add Category Modal -->
    <div class="modal fade" id="add_category_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'store']), 'method' => 'post', 'id' => 'add_category_form']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('messages.add') @lang('assetmanagement::lang.asset_category')</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', __('user.name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('user.name')]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('code', __('assetmanagement::lang.asset_code') . ':') !!}
                        {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => __('assetmanagement::lang.asset_code')]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('description', __('lang_v1.description') . ':') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.description')]) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="edit_category_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => '', 'method' => 'put', 'id' => 'edit_category_form']) !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">@lang('messages.edit') @lang('assetmanagement::lang.asset_category')</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('edit_name', __('user.name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'id' => 'edit_name']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('edit_code', __('assetmanagement::lang.asset_code') . ':') !!}
                        {!! Form::text('code', null, ['class' => 'form-control', 'id' => 'edit_code']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('edit_description', __('lang_v1.description') . ':') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'edit_description']) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var categories_table = $('#asset_categories_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'index']) }}",
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'description', name: 'description' }
        ]
    });

    $(document).on('submit', 'form#add_category_form', function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success == true) {
                    $('#add_category_modal').modal('hide');
                    toastr.success(result.msg);
                    categories_table.ajax.reload();
                    $('form#add_category_form')[0].reset();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.edit_category_button', function(e) {
        e.preventDefault();
        var href = $(this).data('href');
        var name = $(this).data('name');
        var code = $(this).data('code');
        var description = $(this).data('description');

        $('#edit_category_form').attr('action', href);
        $('#edit_name').val(name);
        $('#edit_code').val(code);
        $('#edit_description').val(description);

        $('#edit_category_modal').modal('show');
    });

    $(document).on('submit', 'form#edit_category_form', function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        $.ajax({
            method: 'PUT',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success == true) {
                    $('#edit_category_modal').modal('hide');
                    toastr.success(result.msg);
                    categories_table.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.delete_category_button', function(e) {
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
                            categories_table.ajax.reload();
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
