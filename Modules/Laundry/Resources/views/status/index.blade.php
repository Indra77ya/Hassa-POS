@extends('layouts.app')
@section('title', __('laundry::lang.statuses'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.statuses')</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{ action([\Modules\Laundry\Http\Controllers\LaundryStatusController::class, 'create']) }}" data-container=".view_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="statuses_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('laundry::lang.name')</th>
                        <th>@lang('laundry::lang.color')</th>
                        <th>@lang('laundry::lang.sort_order')</th>
                        <th>@lang('laundry::lang.is_completed_status')</th>
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
    var statuses_table = $('#statuses_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\Modules\Laundry\Http\Controllers\LaundryStatusController::class, 'index']) }}',
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'color', name: 'color' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'is_completed_status', name: 'is_completed_status' }
        ]
    });

    $(document).on('click', '.delete_status_button', function(e) {
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
                            statuses_table.ajax.reload();
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
