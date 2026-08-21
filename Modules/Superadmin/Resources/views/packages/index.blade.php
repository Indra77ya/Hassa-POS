@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.packages'))

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('superadmin::lang.packages') <small>@lang('superadmin::lang.all_packages')</small></h1>
        <!-- <ol class="breadcrumb">
            <a href="#"><i class="fa fa-dashboard"></i> Level</a><br/>
            <li class="active">Here<br/>
        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @include('superadmin::layouts.partials.currency')

        {{-- <div class="box box-solid">
        <div class="box-header">
            <h3 class="box-title">&nbsp;</h3>
        	<div class="box-tools">
                <a href="{{action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'create'])}}" 
                    class="btn btn-block btn-primary">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
            </div>
        </div>

        <div class="box-body">
        	@foreach ($packages as $package)
                <div class="col-md-4">
					<div class="box box-success hvr-grow-shadow">
						<div class="box-header with-border text-center">
							<h2 class="box-title">{{$package->name}}</h2>
								@if ($package->mark_package_as_popular == 1)
								<div class="pull-right">
									<span class="badge bg-green">
										@lang('superadmin::lang.popular')
									</span>
								</div>
								@endif
							<div class="row">
								@if ($package->is_private)
									<a href="#!" class="btn btn-box-tool">
										<i class="fas fa-lock fa-lg text-warning" data-toggle="tooltip"
										title="@lang('superadmin::lang.private_superadmin_only')"></i>
									</a>
								@endif

								@if ($package->is_one_time)
									<a href="#!" class="btn btn-box-tool">
										<i class="fas fa-dot-circle fa-lg text-info" data-toggle="tooltip"
										title="@lang('superadmin::lang.one_time_only_subscription')"></i>
									</a>
								@endif
								
								@if ($package->is_active == 1)
									<span class="badge bg-green">
										@lang('superadmin::lang.active')
									</span>
								@else
									<span class="badge bg-red">
									@lang('superadmin::lang.inactive')
									</span>
								@endif
								
								<a href="{{action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'edit'], [$package->id])}}" class="btn btn-box-tool" title="edit"><i class="fa fa-edit"></i></a>
								<a href="{{action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy'], [$package->id])}}" class="btn btn-box-tool link_confirmation" title="delete"><i class="fa fa-trash"></i></a>
              					
							</div>
						</div>
						<!-- /.box-header -->
						<div class="box-body text-center">

							@if ($package->location_count == 0)
								@lang('superadmin::lang.unlimited')
							@else
								{{$package->location_count}}
							@endif

							@lang('business.business_locations')
							<br/>

							@if ($package->user_count == 0)
								@lang('superadmin::lang.unlimited')
							@else
								{{$package->user_count}}
							@endif

							@lang('superadmin::lang.users')
							<br/>
						
							@if ($package->product_count == 0)
								@lang('superadmin::lang.unlimited')
							@else
								{{$package->product_count}}
							@endif

							@lang('superadmin::lang.products')
							<br/>

							@if ($package->invoice_count == 0)
								@lang('superadmin::lang.unlimited')
							@else
								{{$package->invoice_count}}
							@endif

							@lang('superadmin::lang.invoices')
							<br/>

							@if ($package->trial_days != 0)
									{{$package->trial_days}} @lang('superadmin::lang.trial_days')
								<br/>
							@endif

							@if (!empty($package->custom_permissions))
								@foreach ($package->custom_permissions as $permission => $value)
									@isset($permission_formatted[$permission])
										{{$permission_formatted[$permission]}}
										<br/>
									@endisset
								@endforeach
							@endif
							
							<h3 class="text-center">
								@if ($package->price != 0)
									<span class="display_currency" data-currency_symbol="true">
										{{$package->price}}
									</span>

									<small>
										/ {{$package->interval_count}} {{__('lang_v1.' . $package->interval)}}
									</small>
								@else
									@lang('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . __('lang_v1.' . $package->interval)])
								@endif
							</h3>

						</div>
						<!-- /.box-body -->

						<div class="box-footer text-center">
							{{$package->description}}
						</div>
					</div>
					<!-- /.box -->
                </div>
                @if ($loop->iteration % 3 == 0)
    				<div class="clearfix"></div>
    			@endif
            @endforeach

            <div class="col-md-12">
                {{ $packages->links() }}
            </div>
        </div>

    </div> --}}

        <div
            class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-justify-end tw-gap-2.5">
                    <a class="btn btn-primary btn-sm pull-right"
                        href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'create']) }}">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            @foreach ($packages as $package)
                                <div class="col-md-4 tw-mt-4">
                                    <div class="box box-solid box-primary hvr-grow-shadow" style="border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                        <div class="box-header with-border" style="padding: 15px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                            <div class="tw-flex tw-justify-between tw-items-center">
                                                <h3 class="box-title tw-text-lg tw-font-bold tw-text-gray-800" style="margin: 0;">{{ $package->name }}</h3>
                                                <div class="box-tools pull-right" style="margin: 0;">
                                                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'edit'], [$package->id]) }}"
                                                        class="btn btn-box-tool text-primary" title="edit" style="font-size: 14px; padding: 2px 5px;"><i class="fa fa-edit"></i></a>
                                                    <a href="{{ action([\Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy'], [$package->id]) }}"
                                                        class="btn btn-box-tool link_confirmation text-danger" title="delete" style="font-size: 14px; padding: 2px 5px;"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                            <div class="tw-flex tw-flex-wrap tw-gap-1.5 tw-mt-2.5">
                                                @if ($package->is_active == 1)
                                                    <span class="label bg-light-green">@lang('superadmin::lang.active')</span>
                                                @else
                                                    <span class="label bg-red">@lang('superadmin::lang.inactive')</span>
                                                @endif

                                                @if ($package->mark_package_as_popular == 1)
                                                    <span class="label bg-orange">@lang('superadmin::lang.popular')</span>
                                                @endif

                                                @if ($package->is_private)
                                                    <span class="label bg-purple" data-toggle="tooltip" title="@lang('superadmin::lang.private_superadmin_only')">
                                                        <i class="fa fa-lock"></i> Private
                                                    </span>
                                                @endif

                                                @if ($package->is_one_time)
                                                    <span class="label bg-aqua" data-toggle="tooltip" title="@lang('superadmin::lang.one_time_only_subscription')">
                                                        <i class="fa fa-dot-circle-o"></i> One Time
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <!-- /.box-header -->
                                        <div class="box-body" style="padding: 20px;">
                                            <ul class="list-unstyled tw-space-y-2 tw-text-sm tw-text-gray-700" style="margin-bottom: 20px;">
                                                <li class="tw-flex tw-items-center tw-gap-2">
                                                    <i class="fa fa-check text-success"></i>
                                                    <span><strong>@if ($package->location_count == 0) @lang('superadmin::lang.unlimited') @else {{ $package->location_count }} @endif</strong> @lang('business.business_locations')</span>
                                                </li>
                                                <li class="tw-flex tw-items-center tw-gap-2">
                                                    <i class="fa fa-check text-success"></i>
                                                    <span><strong>@if ($package->user_count == 0) @lang('superadmin::lang.unlimited') @else {{ $package->user_count }} @endif</strong> @lang('superadmin::lang.users')</span>
                                                </li>
                                                <li class="tw-flex tw-items-center tw-gap-2">
                                                    <i class="fa fa-check text-success"></i>
                                                    <span><strong>@if ($package->product_count == 0) @lang('superadmin::lang.unlimited') @else {{ $package->product_count }} @endif</strong> @lang('superadmin::lang.products')</span>
                                                </li>
                                                <li class="tw-flex tw-items-center tw-gap-2">
                                                    <i class="fa fa-check text-success"></i>
                                                    <span><strong>@if ($package->invoice_count == 0) @lang('superadmin::lang.unlimited') @else {{ $package->invoice_count }} @endif</strong> @lang('superadmin::lang.invoices')</span>
                                                </li>
                                                @if ($package->trial_days != 0)
                                                    <li class="tw-flex tw-items-center tw-gap-2">
                                                        <i class="fa fa-check text-success"></i>
                                                        <span><strong>{{ $package->trial_days }}</strong> @lang('superadmin::lang.trial_days')</span>
                                                    </li>
                                                @endif

                                                @if (!empty($package->custom_permissions))
                                                    @foreach ($package->custom_permissions as $permission => $value)
                                                        @isset($permission_formatted[$permission])
                                                            <li class="tw-flex tw-items-center tw-gap-2">
                                                                <i class="fa fa-check text-success"></i>
                                                                <span>{{ $permission_formatted[$permission] }}</span>
                                                            </li>
                                                        @endisset
                                                    @endforeach
                                                @endif
                                            </ul>

                                            <div class="well well-sm text-center" style="margin-bottom: 0; background-color: #f8fafc; border-color: #e2e8f0;">
                                                <h3 class="tw-font-bold text-primary" style="margin: 5px 0;">
                                                    @if ($package->price != 0)
                                                        <span class="display_currency" data-currency_symbol="true">
                                                            {{ $package->price }}
                                                        </span>
                                                        <small class="tw-text-xs tw-text-gray-600">
                                                            / {{ $package->interval_count }} {{ __('lang_v1.' . $package->interval) }}
                                                        </small>
                                                    @else
                                                        @lang('superadmin::lang.free_for_duration', ['duration' => $package->interval_count . ' ' . __('lang_v1.' . $package->interval)])
                                                    @endif
                                                </h3>
                                            </div>
                                        </div>
                                        <!-- /.box-body -->

                                        @if(!empty($package->description))
                                            <div class="box-footer tw-text-xs tw-text-gray-600 tw-text-center" style="background-color: #fafafa; border-top: 1px solid #f0f0f0;">
                                                {{ $package->description }}
                                            </div>
                                        @endif
                                    </div>
                                    <!-- /.box -->
                                </div>
                                @if ($loop->iteration % 3 == 0)
                                    <div class="clearfix"></div>
                                @endif
                            @endforeach

                            <div class="col-md-12">
                                {{ $packages->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
