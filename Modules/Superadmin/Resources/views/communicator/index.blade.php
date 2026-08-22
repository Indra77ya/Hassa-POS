@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.communicator'))

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-text-black">@lang('superadmin::lang.communicator')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div
                    class="tw-mb-4 lg:tw-col-span-2 xl:tw-col-span-2 tw-bg-white tw-shadow-sm tw-ring-1 hover:tw-shadow-md tw-ring-gray-200">
                    <div class="tw-p-4 sm:tw-p-5">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <h3 class="box-title">@lang('superadmin::lang.compose_message')</h3>
                        </div>
                        <div class="tw-mt-5">
                            <div
                                class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-bg-gray-50">
                                <div class="">
                                    {!! Form::open([
                                        'url' => action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'send']),
                                        'method' => 'post',
                                        'id' => 'communication_form',
                                    ]) !!}
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('recipients', __('superadmin::lang.recipients') . ':*') !!}
                                        <button type="button" class="btn btn-primary btn-xs select-all">@lang('lang_v1.select_all')</button>
                                        <button type="button" class="btn btn-default btn-xs deselect-all">@lang('lang_v1.deselect_all')</button>
                                        {!! Form::select('recipients[]', $businesses, null, [
                                            'class' => 'form-control select2',
                                            'required',
                                            'multiple',
                                            'id' => 'recipients',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('subject', __('superadmin::lang.subject') . ':*') !!}
                                        {!! Form::text('subject', null, ['class' => 'form-control', 'required']) !!}
                                    </div>
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('message', __('superadmin::lang.message') . ':*') !!}
                                        {!! Form::textarea('message', null, ['class' => 'form-control', 'required', 'rows' => 6]) !!}
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <button type="submit" class="btn btn-primary pull-right"
                                            id="send_message">@lang('superadmin::lang.send')</button>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div
                    class="tw-mb-4 lg:tw-col-span-2 xl:tw-col-span-2 tw-bg-white tw-shadow-sm tw-ring-1 hover:tw-shadow-md tw-ring-gray-200">
                    <div class="tw-p-4 sm:tw-p-5">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
							<svg xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-text-sky-500 tw-shrink-0"  version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve">

								<defs>
								</defs>
								<g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)" >
									<path d="M 48.831 86.169 c -13.336 0 -25.904 -6.506 -33.62 -17.403 c -2.333 -3.295 -4.163 -6.901 -5.437 -10.717 l 5.606 -1.872 c 1.09 3.265 2.657 6.352 4.654 9.174 c 6.61 9.336 17.376 14.908 28.797 14.908 c 19.443 0 35.26 -15.817 35.26 -35.26 c 0 -19.442 -15.817 -35.259 -35.26 -35.259 C 29.389 9.74 13.571 25.558 13.571 45 h -5.91 c 0 -22.701 18.468 -41.169 41.169 -41.169 C 71.532 3.831 90 22.299 90 45 C 90 67.701 71.532 86.169 48.831 86.169 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
									<polygon points="64.67,61.69 45.88,46.41 45.88,19.03 51.78,19.03 51.78,43.59 68.4,57.1 " style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;" transform="  matrix(1 0 0 1 0 0) "/>
									<polygon points="21.23,40.41 10.62,51.02 0,40.41 " style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;" transform="  matrix(1 0 0 1 0 0) "/>
								</g>
								</svg>
                            <h3 class="box-title">@lang('superadmin::lang.message_history')</h3>
                        </div>
                        <div class="tw-mt-5">
                            <table class="table" id="message-history">
                                <thead>
                                    <tr>
                                        <th>@lang('superadmin::lang.subject')</th>
                                        <th>@lang('superadmin::lang.message')</th>
                                        <th>@lang('lang_v1.date')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- /.content -->
@stop
@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('#send_message').click(function(e) {
                e.preventDefault();
                if ($('form#communication_form').valid()) {
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: false,
                    }).then((sure) => {
                        if (sure) {
                            $('form#communication_form').submit();
                        } else {
                            return false;
                        }
                    });
                }
            });

            $('#message-history').DataTable({
                dom: 'lfrtip',
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: '{{ action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'getHistory']) }}'
            });

            init_tinymce('message');
        });
    </script>
@endsection
