@extends('layouts.app')
@section('title', __('laundry::lang.service_types'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.service_types')</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{ action([\Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class, 'create']) }}" data-container=".view_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>
            </div>
        @endslot

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="service_types_table">
                <thead>
                    <tr>
                        <th>@lang('messages.action')</th>
                        <th>@lang('laundry::lang.name')</th>
                        <th>@lang('laundry::lang.completion_hours')</th>
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
    window.service_types_table = $('#service_types_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class, 'index']) }}',
        columns: [
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'completion_hours', name: 'completion_hours' },
            { data: 'description', name: 'description' }
        ]
    });

    $(document).on('click', '.delete_service_type_button', function(e) {
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
                            service_types_table.ajax.reload();
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
