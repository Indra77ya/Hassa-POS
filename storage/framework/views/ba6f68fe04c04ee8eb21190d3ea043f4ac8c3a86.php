<?php $__env->startSection('title', __('report.profit_loss')); ?>

<?php $__env->startSection('content'); ?>

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"><?php echo app('translator')->get('report.profit_loss'); ?>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="print_section">
            <h2><?php echo e(session()->get('business.name'), false); ?> - <?php echo app('translator')->get('report.profit_loss'); ?></h2>
        </div>

        <div class="row no-print">
            <div class="col-md-3 col-xs-12">
                <div class="input-group">
                    <span class="input-group-addon bg-light-blue"><i class="fa fa-map-marker"></i></span>
                    <select class="form-control select2" id="profit_loss_location_filter">
                        <?php $__currentLoopData = $business_locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key, false); ?>"><?php echo e($value, false); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3 col-xs-12">
                <div class="input-group">
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="profit_loss_date_filter">
                        <span><i class="fa fa-calendar"></i> <?php echo e(__('messages.filter_by_date'), false); ?></span>
                        <i class="fa fa-caret-down"></i>
                    </button>
                </div>
            </div>

            <div class="col-md-6 col-xs-12 tw-flex tw-items-center tw-justify-end tw-gap-2">
                <div id="ai-analysis-container" class="ai-analysis-content"></div>
                <button class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm no-print" aria-label="Print"
                    onclick="window.print();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-printer">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                    </svg> <?php echo app('translator')->get('messages.print'); ?>
                </button>
            </div>
        </div>
        <div class="row tw-mt-4">
            <div id="pl_data_div">
            </div>
        </div>
        <div class="row no-print">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#profit_by_products" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_products'); ?></a>
                        </li>

                        <li>
                            <a href="#profit_by_categories" data-toggle="tab" aria-expanded="true"><i class="fa fa-tags"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_categories'); ?></a>
                        </li>

                        <li>
                            <a href="#profit_by_brands" data-toggle="tab" aria-expanded="true"><i class="fa fa-diamond"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_brands'); ?></a>
                        </li>

                        <li>
                            <a href="#profit_by_locations" data-toggle="tab" aria-expanded="true"><i
                                    class="fa fa-map-marker" aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_locations'); ?></a>
                        </li>

                        <li>
                            <a href="#profit_by_invoice" data-toggle="tab" aria-expanded="true"><i class="fa fa-file-alt"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_invoice'); ?></a>
                        </li>

                        <li>
                            <a href="#profit_by_date" data-toggle="tab" aria-expanded="true"><i class="fa fa-calendar"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_date'); ?></a>
                        </li>
                        <li>
                            <a href="#profit_by_customer" data-toggle="tab" aria-expanded="true"><i class="fa fa-user"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_customer'); ?></a>
                        </li>
                        <li>
                            <a href="#profit_by_day" data-toggle="tab" aria-expanded="true"><i class="fa fa-calendar"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_day'); ?></a>
                        </li>
                        <li>
                            <a href="#profit_by_service_staff" data-toggle="tab" aria-expanded="true"><i class="fa fa-user-secret"
                                    aria-hidden="true"></i> <?php echo app('translator')->get('lang_v1.profit_by_service_staff'); ?></a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="profit_by_products">
                            <?php echo $__env->make('report.partials.profit_by_products', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_categories">
                            <?php echo $__env->make('report.partials.profit_by_categories', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_brands">
                            <?php echo $__env->make('report.partials.profit_by_brands', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_locations">
                            <?php echo $__env->make('report.partials.profit_by_locations', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_invoice">
                            <?php echo $__env->make('report.partials.profit_by_invoice', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_date">
                            <?php echo $__env->make('report.partials.profit_by_date', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_customer">
                            <?php echo $__env->make('report.partials.profit_by_customer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>
                        <div class="tab-pane" id="profit_by_service_staff">
                            <?php echo $__env->make('report.partials.profit_by_service_staff', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="tab-pane" id="profit_by_day">

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <!-- /.content -->
<?php $__env->stopSection(); ?>
<?php $__env->startSection('javascript'); ?>
    <script src="<?php echo e(asset('js/report.js?v=' . $asset_v), false); ?>"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            profit_by_products_table = $('#profit_by_products_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                "ajax": {
                    "url": "/reports/get-profit/product",
                    "data": function(d) {
                        d.start_date = $('#profit_loss_date_filter')
                            .data('daterangepicker')
                            .startDate.format('YYYY-MM-DD');
                        d.end_date = $('#profit_loss_date_filter')
                            .data('daterangepicker')
                            .endDate.format('YYYY-MM-DD');
                        d.location_id = $('#profit_loss_location_filter').val();
                    }
                },
                columns: [{
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'gross_profit',
                        "searchable": false
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    var total_profit = 0;
                    for (var r in data) {
                        total_profit += $(data[r].gross_profit).data('orig-value') ?
                            parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
                    }

                    $('#profit_by_products_table .footer_total').html(__currency_trans_from_en(
                        total_profit));
                }
            });

            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr('href');
                if (target == '#profit_by_categories') {
                    if (typeof profit_by_categories_datatable == 'undefined') {
                        profit_by_categories_datatable = $('#profit_by_categories_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/category",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'category',
                                    name: 'C.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_categories_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_categories_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_brands') {
                    if (typeof profit_by_brands_datatable == 'undefined') {
                        profit_by_brands_datatable = $('#profit_by_brands_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/brand",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'brand',
                                    name: 'B.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_brands_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_brands_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_locations') {
                    if (typeof profit_by_locations_datatable == 'undefined') {
                        profit_by_locations_datatable = $('#profit_by_locations_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/location",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'location',
                                    name: 'L.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_locations_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_locations_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_invoice') {
                    if (typeof profit_by_invoice_datatable == 'undefined') {
                        profit_by_invoice_datatable = $('#profit_by_invoice_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/invoice",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'invoice_no',
                                    name: 'sale.invoice_no'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_invoice_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_invoice_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_date') {
                    if (typeof profit_by_date_datatable == 'undefined') {
                        profit_by_date_datatable = $('#profit_by_date_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/date",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'transaction_date',
                                    name: 'sale.transaction_date'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_date_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_date_datatable.ajax.reload();
                    }
                } else if (target == '#profit_by_customer') {
                    if (typeof profit_by_customers_table == 'undefined') {
                        profit_by_customers_table = $('#profit_by_customer_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/customer",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'customer',
                                    name: 'CU.name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_customer_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_customers_table.ajax.reload();
                    }
                } else if (target == '#profit_by_service_staff') {
                    if (typeof profit_by_service_staffs_table == 'undefined') {
                        
                        profit_by_service_staffs_table = $('#profit_by_service_staff_table').DataTable({
                            processing: true,
                            serverSide: true,
                            fixedHeader:false,
                            "ajax": {
                                "url": "/reports/get-profit/service_staff",
                                "data": function(d) {
                                    d.start_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .startDate.format('YYYY-MM-DD');
                                    d.end_date = $('#profit_loss_date_filter')
                                        .data('daterangepicker')
                                        .endDate.format('YYYY-MM-DD');
                                    d.location_id = $('#profit_loss_location_filter').val();
                                }
                            },
                            columns: [{
                                    data: 'staff_name',
                                    name: 'U.first_name'
                                },
                                {
                                    data: 'gross_profit',
                                    "searchable": false
                                },
                            ],
                            footerCallback: function(row, data, start, end, display) {
                                var total_profit = 0;
                                for (var r in data) {
                                    total_profit += $(data[r].gross_profit).data('orig-value') ?
                                        parseFloat($(data[r].gross_profit).data('orig-value')) :
                                        0;
                                }

                                $('#profit_by_service_staff_table .footer_total').html(
                                    __currency_trans_from_en(total_profit));
                            },
                        });
                    } else {
                        profit_by_service_staffs_table.ajax.reload();
                    }
                } else if (target == '#profit_by_day') {
                    var start_date = $('#profit_loss_date_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');

                    var end_date = $('#profit_loss_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');
                    var location_id = $('#profit_loss_location_filter').val();

                    var url = '/reports/get-profit/day?start_date=' + start_date + '&end_date=' + end_date +
                        '&location_id=' + location_id;
                    $.ajax({
                        url: url,
                        dataType: 'html',
                        success: function(result) {
                            $('#profit_by_day').html(result);
                            profit_by_days_table = $('#profit_by_day_table').DataTable({
                                "searching": false,
                                'paging': false,
                                'ordering': false,
                            });
                            var total_profit = sum_table_col($('#profit_by_day_table'),
                                'gross-profit');
                            $('#profit_by_day_table .footer_total').text(total_profit);
                            __currency_convert_recursively($('#profit_by_day_table'));
                        },
                    });
                } else if (target == '#profit_by_products') {
                    profit_by_products_table.ajax.reload();
                }
                $("a.btn").removeClass("btn btn-default buttons-excel buttons-html5");
            });
        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\Hassa POS\resources\views/report/profit_loss.blade.php ENDPATH**/ ?>