@extends('layouts.app')
@section('title', __('Inter-Company Links'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Inter-Company Links')
        <small>@lang('Manage automatic cross-business transaction links')</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('Link Contact to Partner Business')])
        <form id="add_intercompany_link_form" action="{{ action([\App\Http\Controllers\IntercompanyController::class, 'store']) }}" method="post">
            @csrf
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('Select Contact (Customer/Supplier)') . ':*') !!}
                        {!! Form::select('contact_id', $contacts, null, ['class' => 'form-group select2', 'style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('target_business_id', __('Link to Target Business') . ':*') !!}
                        {!! Form::select('target_business_id', $businesses, null, ['class' => 'form-group select2', 'style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
                <div class="col-md-2" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-block">@lang('messages.save')</button>
                </div>
            </div>
        </form>
    @endcomponent

    @component('components.widget', ['class' => 'box-solid', 'title' => __('Active Inter-Company Links')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="intercompany_links_table">
                <thead>
                    <tr>
                        <th>@lang('contact.contact')</th>
                        <th>@lang('Target Business')</th>
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
        var intercompany_table = $('#intercompany_links_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ action([\App\Http\Controllers\IntercompanyController::class, "index"]) }}',
            columns: [
                { data: 'contact_name', name: 'contact_name' },
                { data: 'target_business_name', name: 'target_business_name' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('submit', '#add_intercompany_link_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        toastr.success(result.msg);
                        intercompany_table.ajax.reload();
                        form.find('select').val('').trigger('change');
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });

        $(document).on('click', '.delete_intercompany_link_btn', function() {
            var url = $(this).data('href');
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        method: 'DELETE',
                        url: url,
                        dataType: 'json',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                intercompany_table.ajax.reload();
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
