@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Business')

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('superadmin::lang.view_business')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold"> {{ $business->name }} </small>
        </h1>
        <!-- <ol class="breadcrumb">
                                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                    <li class="active">Here</li>
                                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div
            class=" tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-gap-2.5">
                    <strong><i class="fa fa-briefcase margin-r-5"></i>
                        {{ $business->name }}</strong>
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            <div class="col-sm-3">
                                <div class="well well-sm">
                                    <strong><i class="fa fa-briefcase margin-r-5"></i>
                                        @lang('business.business_name')</strong>
                                    <p class="text-muted">
                                        {{ $business->name }}
                                    </p>

                                    <strong><i class="fa fa-money margin-r-5"></i>
                                        @lang('business.currency')</strong>
                                    <p class="text-muted">
                                        {{ $business->currency->currency }}
                                    </p>

                                    <strong><i class="fa fa-file-text-o margin-r-5"></i>
                                        @lang('business.tax_number1')</strong>
                                    <p class="text-muted">
                                        @if (!empty($business->tax_number_1))
                                            {{ $business->tax_label_1 }}: {{ $business->tax_number_1 }}
                                        @endif
                                    </p>

                                    <strong><i class="fa fa-file-text-o margin-r-5"></i>
                                        @lang('business.tax_number2')</strong>
                                    <p class="text-muted">
                                        @if (!empty($business->tax_number_2))
                                            {{ $business->tax_label_2 }}: {{ $business->tax_number_2 }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="well well-sm">
                                    <strong><i class="fa fa-location-arrow margin-r-5"></i>
                                        @lang('business.time_zone')</strong>
                                    <p class="text-muted">
                                        {{ $business->time_zone }}
                                    </p>

                                    <strong><i class="fa fa-toggle-on margin-r-5"></i>
                                        @lang('business.is_active')</strong>
                                    @if ($business->is_active == 0)
                                        <p class="text-muted">
                                            Inactive
                                        </p>
                                    @else
                                        <p class="text-muted">
                                            Active
                                        </p>
                                    @endif

                                    <strong><i class="fa fa-user-circle-o margin-r-5"></i>
                                        @lang('business.created_by')</strong>
                                    @if (!empty($created_by))
                                        <p class="text-muted">
                                            {{ $created_by->surname }} {{ $created_by->first_name }}
                                            {{ $created_by->last_name }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="well well-sm">
                                    <strong><i class="fa fa-user-circle-o margin-r-5"></i>
                                        @lang('business.owner')</strong>
                                    @if (!empty($business->owner))
                                        <p class="text-muted">
                                            {{ $business->owner->surname }} {{ $business->owner->first_name }}
                                            {{ $business->owner->last_name }}
                                        </p>
                                    @endif

                                    <strong><i class="fa fa-envelope margin-r-5"></i>
                                        @lang('business.email')</strong>
                                    <p class="text-muted">
                                        {{ $business->owner->email }}
                                    </p>

                                    <strong><i class="fa fa-address-book-o margin-r-5"></i>
                                        @lang('business.mobile')</strong>
                                    <p class="text-muted">
                                        {{ $business->owner->contact_no }}
                                    </p>

                                    <strong><i class="fa fa-map-marker margin-r-5"></i>
                                        @lang('business.address')</strong>
                                    <p class="text-muted">
                                        {{ $business->owner->address }}
                                    </p>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div>
                                    @if (!empty($business->logo))
                                        <img class="img-responsive"
                                            src="{{ url('uploads/business_logos/' . $business->logo) }}"
                                            alt="Business Logo">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tw-mt-5 tw-transition-all  lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-gap-2.5">
                    <strong><i class="fa fa-map-marker margin-r-5"></i>
                        @lang('superadmin::lang.business_location')</strong>
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>@lang('lang_v1.name')</th>
                                        <th>@lang('lang_v1.location_id')</th> 
                                        <th>@lang('business.landmark')</th>
                                        <th>@lang('business.city')</th>
                                        <th>@lang('business.zip_code')</th>
                                        <th>@lang('business.state')</th>
                                        <th>@lang('business.country')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($business->locations as $location)
                                        <tr>
                                            <td>{{ $location->name }}</td>
                                            <td>{{ $location->location_id }}</td>
                                            <td>{{ $location->landmark }}</td>
                                            <td>{{ $location->city }}</td>
                                            <td>{{ $location->zip_code }}</td>
                                            <td>{{ $location->state }}</td>
                                            <td>{{ $location->country }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tw-mt-5 tw-transition-all  lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-gap-2.5">
                    <strong><i class="fa fa-refresh margin-r-5"></i>
                        @lang('superadmin::lang.package_subscription')</strong>
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            <!-- location table-->
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>@lang('superadmin::lang.package_name')</th>
                                        <th>@lang('superadmin::lang.start_date')</th>
                                        <th>@lang('superadmin::lang.trial_end_date')</th>
                                        <th>@lang('superadmin::lang.end_date')</th>
                                        <th>@lang('superadmin::lang.paid_via')</th>
                                        <th>@lang('superadmin::lang.payment_transaction_id')</th>
                                        <th>@lang('lang_v1.created_at')</th>
                                        <th>@lang('business.created_by')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($business->subscriptions as $subscription)
                                        <tr>
                                            <td>{{ $subscription->package_details['name'] }}</td>
                                            <td>
                                                @if (!empty($subscription->start_date))
                                                    {{ @format_date($subscription->start_date) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($subscription->trial_end_date))
                                                    {{ @format_date($subscription->trial_end_date) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if (!empty($subscription->end_date))
                                                    {{ @format_date($subscription->end_date) }}
                                                @endif
                                            </td>
                                            <td>{{ $subscription->paid_via }}</td>
                                            <td>{{ $subscription->payment_transaction_id }}</td>
                                            <td>{{ $subscription->created_at }}</td>
                                            <td>
                                                @if (!empty($subscription->created_user))
                                                    {{ $subscription->created_user->user_full_name }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="tw-mt-5 tw-transition-all  lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
            <div class="tw-p-4 sm:tw-p-5">
                <div class="tw-flex tw-gap-2.5">
                    <strong>{{ __('user.all_users') }}</strong>
                </div>
                <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                    <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                        <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                            <!-- location table-->
                            <table class="table table-bordered table-striped" id="users_table">
                                <thead>
                                    <tr>
                                        <th>@lang('business.username')</th>
                                        <th>@lang('user.name')</th>
                                        <th>@lang('user.role')</th>
                                        <th>@lang('business.email')</th>
                                        <th>@lang('messages.action')</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('superadmin::business.update_password_modal')
    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script type="text/javascript">
        //Roles table
        $(document).ready(function() {
            var users_table = $('#users_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: '/superadmin/users/' + "{{ $business->id }}",
                columnDefs: [{
                    "targets": [4],
                    "orderable": false,
                    "searchable": false
                }],
                "columns": [{
                        "data": "username"
                    },
                    {
                        "data": "full_name"
                    },
                    {
                        "data": "role"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "action"
                    }
                ]
            });

        });

        $(document).on('click', '.update_user_password', function(e) {
            e.preventDefault();
            $('form#password_update_form, #user_id').val($(this).data('user_id'));
            $('span#user_name').text($(this).data('user_name'));
            $('#update_password_modal').modal('show');
        });

        password_update_form_validator = $('form#password_update_form').validate();

        $('#update_password_modal').on('hidden.bs.modal', function() {
            password_update_form_validator.resetForm();
            $('form#password_update_form')[0].reset();
        });

        $(document).on('submit', 'form#password_update_form', function(e) {
            e.preventDefault();
            $(this)
                .find('button[type="submit"]')
                .attr('disabled', true);
            var data = $(this).serialize();
            $.ajax({
                method: 'post',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('#update_password_modal').modal('hide');
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                    $('form#password_update_form')
                        .find('button[type="submit"]')
                        .attr('disabled', false);
                },
            });
        });
    </script>
@endsection
