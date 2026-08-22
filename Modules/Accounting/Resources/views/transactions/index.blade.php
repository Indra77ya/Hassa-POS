@extends('layouts.app')

@section('title', __('accounting::lang.transactions'))

@section('content')

    @include('accounting::layouts.nav')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-text-black">@lang('accounting::lang.transactions')</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);">
                    <ul class="nav nav-tabs nav-justified" style="border-radius: 12px 12px 0 0; border-bottom: 1px solid #e5e7eb;">
                        <li class="active">
                            <a href="#sales_tab" data-toggle="tab" aria-expanded="true" class="tw-text-sm md:tw-text-base">
                                <i class="fas fa-shopping-cart text-primary"></i> @lang('sale.sale')
                            </a>
                        </li>
                        <li>
                            <a href="#sales_payments_tab" data-toggle="tab" aria-expanded="true" class="tw-text-sm md:tw-text-base">
                                <i class="fas fa-money-bill-alt text-success"></i> @lang('accounting::lang.sales_payments')
                            </a>
                        </li>
                        <li>
                            <a href="#purchases_tab" data-toggle="tab" aria-expanded="true" class="tw-text-sm md:tw-text-base">
                                <i class="fas fa-shopping-bag text-warning"></i> @lang('purchase.purchases')
                            </a>
                        </li>
                        <li>
                            <a href="#purchase_payments_tab" data-toggle="tab" aria-expanded="true" class="tw-text-sm md:tw-text-base">
                                <i class="fas fa-credit-card text-info"></i> @lang('accounting::lang.purchase_payments')
                            </a>
                        </li>
                        <li>
                            <a href="#expenses_tab" data-toggle="tab" aria-expanded="true" class="tw-text-sm md:tw-text-base">
                                <i class="fas fa-minus-circle text-danger"></i> @lang('accounting::lang.expenses')
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" style="border-radius: 0 0 12px 12px; padding: 20px;">
                        <div class="tab-pane active" id="sales_tab">
                            @include('accounting::transactions.partials.sales')
                        </div>
                        <div class="tab-pane" id="sales_payments_tab">
                            @include('accounting::transactions.partials.payments', [
                                'id' => 'sell_payment_table',
                            ])
                        </div>
                        <div class="tab-pane" id="purchases_tab">
                            @include('accounting::transactions.partials.purchases')
                        </div>
                        <div class="tab-pane" id="purchase_payments_tab">
                            @include('accounting::transactions.partials.payments', [
                                'id' => 'purchase_payment_table',
                            ])
                        </div>
                        <div class="tab-pane" id="expenses_tab">
                            @include('accounting::transactions.partials.expenses')
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <!-- /.content -->
@stop

@section('javascript')
    @include('accounting::accounting.common_js')
    <script type="text/javascript">
        $(document).ready(function() {
            sell_table = $('#sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": base_path + "/accounting/transactions?type=sell&datatable=sell",
                    "data": function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.is_direct_sale = 1;

                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                        d.service_staffs = $('#service_staffs').val();

                        if ($('#shipping_status').length) {
                            d.shipping_status = $('#shipping_status').val();
                        }

                        if ($('#sell_list_filter_source').length) {
                            d.source = $('#sell_list_filter_source').val();
                        }

                        if ($('#only_subscriptions').is(':checked')) {
                            d.only_subscriptions = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'payment_methods',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid',
                        "searchable": false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                    {
                        data: 'additional_notes',
                        name: 'additional_notes'
                    },
                    {
                        data: 'staff_note',
                        name: 'staff_note'
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
                }
            });

            sell_payment_table = $('#sell_payment_table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                "ajax": {
                    "url": base_path + "/accounting/transactions?transaction_type=sell&datatable=payment",
                    "data": function(d) {
                        // d.account_id = $('#account_id').val();
                        // var start_date = '';
                        // var endDate = '';
                        // if($('#date_filter').val()){
                        //     var start_date = $('#date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        //     var endDate = $('#date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        // }
                        // d.start_date = start_date;
                        // d.end_date = endDate;
                    }
                },
                columnDefs: [{
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'paid_on',
                        name: 'paid_on'
                    },
                    {
                        data: 'payment_ref_no',
                        name: 'payment_ref_no'
                    },
                    {
                        data: 'transaction_number',
                        name: 'transaction_number'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'type',
                        name: 'T.type'
                    },
                    {
                        data: 'details',
                        name: 'details',
                        "searchable": false
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_payment_table'));
                }
            });
            purchase_payment_table = $('#purchase_payment_table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                "ajax": {
                    "url": base_path +
                        "/accounting/transactions?transaction_type=purchase&datatable=payment",
                    "data": function(d) {
                        // d.account_id = $('#account_id').val();
                        // var start_date = '';
                        // var endDate = '';
                        // if($('#date_filter').val()){
                        //     var start_date = $('#date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        //     var endDate = $('#date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        // }
                        // d.start_date = start_date;
                        // d.end_date = endDate;
                    }
                },
                columnDefs: [{
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'paid_on',
                        name: 'paid_on'
                    },
                    {
                        data: 'payment_ref_no',
                        name: 'payment_ref_no'
                    },
                    {
                        data: 'transaction_number',
                        name: 'transaction_number'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'type',
                        name: 'T.type'
                    },
                    {
                        data: 'details',
                        name: 'details',
                        "searchable": false
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_payment_table'));
                }
            });

            //Purchase table
            purchase_table = $('#purchase_table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '/accounting/transactions?datatable=purchase',
                    data: function(d) {
                        if ($('#purchase_list_filter_location_id').length) {
                            d.location_id = $('#purchase_list_filter_location_id').val();
                        }
                        if ($('#purchase_list_filter_supplier_id').length) {
                            d.supplier_id = $('#purchase_list_filter_supplier_id').val();
                        }
                        if ($('#purchase_list_filter_payment_status').length) {
                            d.payment_status = $('#purchase_list_filter_payment_status').val();
                        }
                        if ($('#purchase_list_filter_status').length) {
                            d.status = $('#purchase_list_filter_status').val();
                        }

                        var start = '';
                        var end = '';
                        if ($('#purchase_list_filter_date_range').val()) {
                            start = $('input#purchase_list_filter_date_range')
                                .data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            end = $('input#purchase_list_filter_date_range')
                                .data('daterangepicker')
                                .endDate.format('YYYY-MM-DD');
                        }
                        d.start_date = start;
                        d.end_date = end;

                        d = __datatable_ajax_callback(d);
                    },
                },
                aaSorting: [
                    [1, 'desc']
                ],
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    {
                        data: 'location_name',
                        name: 'BS.name'
                    },
                    {
                        data: 'name',
                        name: 'contacts.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'payment_due',
                        name: 'payment_due',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#purchase_table'));
                }
            });

            $(document).on('submit', "form#save_accounting_map", function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();
                transaction_type = $('#transaction_type').val();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div.view_modal').modal('hide');
                            toastr.success(result.msg);
                            if (transaction_type == 'sell') {
                                sell_table.ajax.reload();
                            } else if (transaction_type == 'sell_payment') {
                                sell_payment_table.ajax.reload();
                            } else if (transaction_type == 'purchase') {
                                purchase_table.ajax.reload();
                            } else if (transaction_type == 'purchase_payment') {
                                purchase_payment_table.ajax.reload();
                            } else if (transaction_type == 'expense') {
                                transaction_expense_table.ajax.reload();
                            }
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });


            });

            // expense_table
            transaction_expense_table = $('#transaction_expense_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/accounting/transactions?type=expense&datatable=expense',
                    data: function(d) {},
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    {
                        data: 'recur_details',
                        name: 'recur_details',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category',
                        name: 'ec.name'
                    },
                    {
                        data: 'sub_category',
                        name: 'esc.name'
                    },
                    {
                        data: 'location_name',
                        name: 'bl.name'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status',
                        orderable: false
                    },
                    {
                        data: 'tax',
                        name: 'tr.name'
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'payment_due',
                        name: 'payment_due'
                    },
                    {
                        data: 'expense_for',
                        name: 'expense_for'
                    },
                    {
                        data: 'contact_name',
                        name: 'c.name'
                    },
                    {
                        data: 'additional_notes',
                        name: 'additional_notes'
                    },
                    {
                        data: 'added_by',
                        name: 'usr.first_name'
                    }
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#transaction_expense_table'));
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if (typeof sell_table !== 'undefined') { sell_table.columns.adjust(); }
                if (typeof sell_payment_table !== 'undefined') { sell_payment_table.columns.adjust(); }
                if (typeof purchase_table !== 'undefined') { purchase_table.columns.adjust(); }
                if (typeof purchase_payment_table !== 'undefined') { purchase_payment_table.columns.adjust(); }
                if (typeof transaction_expense_table !== 'undefined') { transaction_expense_table.columns.adjust(); }
            });
        });
    </script>
@stop
