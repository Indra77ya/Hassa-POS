@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.communicator'))

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('superadmin::lang.communicator')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div
                    class="tw-transition-all tw-mb-4 lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
                    <div class="tw-p-4 sm:tw-p-5">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-text-sky-500 tw-shrink-0"
                                version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve">

                                <defs>
                                </defs>
                                <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;"
                                    transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                                    <circle cx="46.441" cy="44.031" r="12.841"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(248,204,29); fill-rule: nonzero; opacity: 1;"
                                        transform="  matrix(1 0 0 1 0 0) " />
                                    <path
                                        d="M 45.187 90 c -7.154 0 -14.285 -1.718 -20.82 -5.132 C 13.718 79.302 5.873 69.923 2.279 58.457 c -3.595 -11.466 -2.51 -23.645 3.056 -34.294 c 8.923 -17.074 27.704 -26.57 46.73 -23.635 c 0.561 0.087 0.945 0.611 0.859 1.172 c -0.086 0.561 -0.612 0.946 -1.172 0.859 C 33.587 -0.244 15.671 8.82 7.155 25.115 C 1.844 35.277 0.808 46.9 4.239 57.842 s 10.917 19.893 21.08 25.204 c 11.495 6.008 24.931 6.513 36.858 1.386 c 0.517 -0.221 1.125 0.017 1.349 0.539 c 0.224 0.521 -0.017 1.126 -0.539 1.349 C 57.27 88.777 51.22 90 45.187 90 z"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(248,204,29); fill-rule: nonzero; opacity: 1;"
                                        transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                                    <path
                                        d="M 77.216 79.151 h -2.46 v -2.459 c 0 -0.568 -0.459 -1.027 -1.027 -1.027 s -1.027 0.459 -1.027 1.027 v 2.459 h -2.459 c -0.568 0 -1.027 0.459 -1.027 1.027 s 0.459 1.027 1.027 1.027 h 2.459 v 2.46 c 0 0.568 0.459 1.027 1.027 1.027 s 1.027 -0.459 1.027 -1.027 v -2.46 h 2.46 c 0.568 0 1.027 -0.459 1.027 -1.027 S 77.783 79.151 77.216 79.151 z"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(248,204,29); fill-rule: nonzero; opacity: 1;"
                                        transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                                    <path
                                        d="M 89.564 14.004 L 80.411 3.076 c -0.175 -0.209 -0.425 -0.34 -0.697 -0.364 c -0.269 -0.022 -0.542 0.061 -0.75 0.236 L 48.01 28.876 c -0.112 0.094 -0.196 0.206 -0.257 0.329 c -0.001 0.003 -0.004 0.004 -0.005 0.007 l -7.701 15.748 c -0.178 0.365 -0.125 0.8 0.135 1.112 c 0.198 0.236 0.489 0.367 0.788 0.367 c 0.094 0 0.189 -0.013 0.282 -0.039 l 16.855 -4.821 c 0.009 -0.003 0.016 -0.01 0.025 -0.013 c 0.125 -0.039 0.245 -0.097 0.352 -0.187 l 30.953 -25.928 C 89.871 15.087 89.928 14.439 89.564 14.004 z M 48.898 31.536 l 7.122 8.503 L 42.906 43.79 L 48.898 31.536 z M 57.951 39.144 l -7.834 -9.352 L 79.496 5.183 l 7.833 9.352 L 57.951 39.144 z"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                        transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                                    <path
                                        d="M 61.595 50.569 c -0.016 0 -0.03 0 -0.046 0 H 44.025 c -0.567 0 -1.027 -0.459 -1.027 -1.027 s 0.46 -1.027 1.027 -1.027 h 17.528 c 0.012 0 0.025 0 0.038 0 c 4.174 0 8.092 -2.588 10.49 -6.932 c 2.763 -5.008 7.353 -7.993 12.283 -7.993 c 0.016 0 0.03 0 0.046 0 l 4.122 0.016 c 0.567 0.002 1.025 0.464 1.023 1.031 c -0.002 0.566 -0.461 1.023 -1.027 1.023 c -0.001 0 -0.003 0 -0.004 0 l -4.122 -0.016 c -4.191 -0.037 -8.12 2.576 -10.523 6.931 C 71.114 47.583 66.524 50.569 61.595 50.569 z"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(0,0,0); fill-rule: nonzero; opacity: 1;"
                                        transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                                    <path
                                        d="M 44.046 73.048 C 33.282 73.049 23.17 66.83 18.485 56.464 c -3.502 -7.749 -3.33 -16.561 0.471 -24.177 c 0.253 -0.507 0.869 -0.715 1.378 -0.46 c 0.508 0.253 0.714 0.87 0.46 1.378 c -3.524 7.061 -3.683 15.23 -0.437 22.412 c 5.566 12.314 19.391 18.318 32.167 13.962 c 0.798 -0.273 1.59 -0.586 2.355 -0.932 c 0.519 -0.235 1.126 -0.004 1.359 0.513 c 0.234 0.517 0.004 1.126 -0.513 1.359 c -0.825 0.373 -1.678 0.71 -2.538 1.004 C 50.16 72.555 47.077 73.048 44.046 73.048 z"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(248,204,29); fill-rule: nonzero; opacity: 1;"
                                        transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                                    <path
                                        d="M 26.275 28.371 c -0.551 0 -1.102 -0.109 -1.626 -0.328 c -1.041 -0.434 -1.85 -1.248 -2.28 -2.292 c -0.429 -1.043 -0.426 -2.192 0.009 -3.233 l 0 0 c 0.896 -2.149 3.373 -3.169 5.524 -2.271 c 2.149 0.896 3.168 3.375 2.271 5.524 c -0.434 1.041 -1.248 1.85 -2.292 2.28 C 27.363 28.265 26.819 28.371 26.275 28.371 z M 24.274 23.31 c -0.46 1.104 0.063 2.377 1.167 2.837 c 0.535 0.224 1.124 0.225 1.66 0.004 c 0.536 -0.22 0.954 -0.636 1.177 -1.171 c 0.46 -1.104 -0.063 -2.376 -1.167 -2.837 C 26.008 21.685 24.735 22.207 24.274 23.31 L 24.274 23.31 z"
                                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(248,204,29); fill-rule: nonzero; opacity: 1;"
                                        transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                                </g>
                            </svg>
                            <h3 class="box-title">@lang('superadmin::lang.compose_message')</h3>
                        </div>
                        <div class="tw-mt-5">
                            <div
                                class="tw-grid tw-w-full tw-h-100 tw-border tw-border-gray-200 tw-border-dashed tw-rounded-xl tw-bg-gray-50 ">
                                <div class="">
                                    {!! Form::open([
                                        'url' => action([\Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'send']),
                                        'method' => 'post',
                                        'id' => 'communication_form',
                                    ]) !!}
                                    <div class="col-md-12 form-group">
                                        {!! Form::label('recipients', __('superadmin::lang.recipients') . ':*') !!} <button type="button"
                                            class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary select-all">@lang('lang_v1.select_all')</button> <button
                                            type="button"
                                            class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary deselect-all">@lang('lang_v1.deselect_all')</button>
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
                                    <div class="col-md-12 form-group ">
                                        <div class="col-md-8 message-container">
                                            {!! Form::label('message', __('superadmin::lang.message') . ':*') !!}
                                        </div>
                                        {!! Form::textarea('message', null, ['class' => 'form-control', 'required', 'rows' => 6]) !!}
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <button type="submit" class="tw-dw-btn tw-dw-btn-error tw-text-white pull-right"
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
                    class="tw-transition-all tw-mb-4 lg:tw-col-span-2 xl:tw-col-span-2 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
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
