@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | Coupons')

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('superadmin::lang.all_coupon')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
    <div
        class="tw-transition-all lg:tw-col-span-1 tw-duration-200 tw-bg-white tw-shadow-sm tw-rounded-xl tw-ring-1 hover:tw-shadow-md  tw-ring-gray-200">
        <div class="tw-p-4 sm:tw-p-5">
            <div class="tw-flex tw-justify-end tw-gap-2.5">
                <a class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full pull-right"
                    href="{{ action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'create']) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg> @lang('messages.add')
                </a>
                
            </div>
            <div class="tw-flow-root tw-mt-5 tw-border-b tw-border-gray-200">
                <div class="tw-mx-4 tw--my-2 tw-overflow-x-auto sm:tw--mx-5">
                    <div class="tw-inline-block tw-min-w-full tw-py-2 tw-align-middle sm:tw-px-5">
                        @can('superadmin')
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="superadmin_Coupons_table">
                                    <thead>
                                        <tr>
                                            <th>
                                                @lang('superadmin::lang.coupon_code')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.discount_type')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.discount')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.expiry_date')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.applied_on_packages')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.applied_on_business')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.status')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.created_at')
                                            </th>
                                            <th>
                                                @lang('superadmin::lang.action')
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    </section>

    <!-- /.content -->

@endsection

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            superadmin_business_table = $('#superadmin_Coupons_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                ajax: {
                    url: "{{ action([\Modules\Superadmin\Http\Controllers\CouponController::class, 'index']) }}",
                },
                aaSorting: [
                    [6, 'desc']
                ],
                columns: [{
                        data: 'coupon_code',
                        name: 'coupons.coupon_code'
                    },
                    {
                        data: 'discount_type',
                        name: 'coupons.discount_type'
                    },
                    {
                        data: 'discount',
                        name: 'coupons.discount'
                    },
                    {
                        data: 'expiry_date',
                        name: 'coupons.expiry_date'
                    },
                    {
                        data: 'applied_on_packages',
                        name: 'coupons.applied_on_packages'
                    },
                    {
                        data: 'applied_on_business',
                        name: 'coupons.applied_on_business'
                    },
                    {
                        data: 'is_active',
                        name: 'coupons.is_active'
                    },
                    {
                        data: 'created_at',
                        name: 'coupons.created_at'
                    },
                    {
                        data: 'action',
                        name: 'coupons.action'
                    },
                ]
            });

            $(document).on('click', 'a.delete_coupon_confirmation', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: "Once deleted, you will not be able to recover this Coupon !",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        window.location.href = $(this).attr('href');
                    }
                });
            });
        });
    </script>

@endsection
