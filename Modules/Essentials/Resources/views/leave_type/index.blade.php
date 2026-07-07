@extends('layouts.app')
@section('title', __('essentials::lang.leave_type'))

@section('content')
@include('essentials::layouts.nav_hrm')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('essentials::lang.leave_type')
    </h1>
</section>
<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-solid', 'title' => __( 'essentials::lang.all_leave_types' )])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                    data-toggle="modal" data-target="#add_leave_type_modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="leave_type_table">
                <thead>
                    <tr>
                        <th>@lang( 'essentials::lang.leave_type' )</th>
                        <th>@lang( 'essentials::lang.max_leave_count' )</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    @include('essentials::leave_type.create')

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready( function(){

            leave_type_table = $('#leave_type_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: "{{action([\Modules\Essentials\Http\Controllers\EssentialsLeaveTypeController::class, 'index'])}}",
                columnDefs: [
                    {
                        targets: 2,
                        orderable: false,
                        searchable: false,
                    },
                ],
            });

        });

        $(document).on('submit', 'form#add_leave_type_form, form#edit_leave_type_form', function (e) {
            e.preventDefault();
            var data = $(this).serialize();
            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div#add_leave_type_modal').modal('hide');
                        $('.view_modal').modal('hide');
                        toastr.success(result.msg);
                        leave_type_table.ajax.reload();
                        $('form#add_leave_type_form')[0].reset();
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        })
    </script>
@endsection
