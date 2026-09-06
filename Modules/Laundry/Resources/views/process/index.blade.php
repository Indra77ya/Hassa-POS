@extends('layouts.app')
@section('title', __('laundry::lang.processes'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.processes')</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{ action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'create']) }}" data-container=".view_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="processes_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('laundry::lang.name')</th>
                        <th>@lang('laundry::lang.points')</th>
                        <th>@lang('laundry::lang.sort_order')</th>
                        <th>@lang('brand.note')</th>
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
    window.processes_table = $('#processes_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'index']) }}',
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'points', name: 'points' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'description', name: 'description' }
        ]
    });

    $(document).on('click', '.delete_process_button', function(e) {
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
                            processes_table.ajax.reload();
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
