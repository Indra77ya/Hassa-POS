$(document).ready(function() {
    $(document).on('change', '.purchase_quantity', function() {
        update_table_total($(this).closest('table'));
    });
    $(document).on('change', '.unit_price', function() {
        update_table_total($(this).closest('table'));
    });

    $('.os_exp_date').datepicker({
        autoclose: true,
        format: datepicker_date_format,
    });

    $(document).on('click', '.add_stock_row', function() {
        var tr = $(this).data('row-html');
        var key = parseInt($(this).data('sub-key'));
        tr = tr.replace(/\__subkey__/g, key);
        $(this).data('sub-key', key + 1);

        $(tr)
            .insertAfter($(this).closest('tr'))
            .find('.os_exp_date')
            .datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
            
            $(this).closest('tr').next('tr').find('.os_date').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
    });

    $(document).on('click', '.add-opening-stock', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                $('#opening_stock_modal')
                    .html(result)
                    .modal('show');
            },
        });
    });

    // Calculate initial summary for existing OS SN tables
    $('.os_sn_table').each(function() {
        var main_tr = $(this).closest('td').closest('tr');
        calculate_os_sn_table_summary(main_tr);
    });
});

$('#opening_stock_modal').on('shown.bs.modal', function(e) {
    $('#opening_stock_modal .os_sn_table').each(function() {
        var main_tr = $(this).closest('td').closest('tr');
        calculate_os_sn_table_summary(main_tr);
    });
});

// Add new OS Serial Number row
$(document).on('click', '.add_os_sn_row', function() {
    var table = $(this).closest('div').find('.os_sn_table');
    var prefix = table.data('prefix');
    var index = table.find('tbody tr.os_sn_row').length;
    var main_tr = table.closest('td').closest('tr');
    var default_pp = main_tr.find('.unit_price').val() || '';
    var default_sp = main_tr.find('.selling_price').val() || '';

    var new_row = '<tr class="os_sn_row">' +
        '<td><input type="text" name="' + prefix + '[sn_details][' + index + '][serial_number]" class="form-control input-sm os_sn_input" placeholder="Enter Serial / IMEI" required></td>' +
        '<td><input type="text" name="' + prefix + '[sn_details][' + index + '][purchase_price]" value="' + default_pp + '" class="form-control input-sm input_number os_sn_pp_input" placeholder="Harga Beli" required></td>' +
        '<td><input type="text" name="' + prefix + '[sn_details][' + index + '][selling_price]" value="' + default_sp + '" class="form-control input-sm input_number os_sn_sp_input" placeholder="Harga Jual" required></td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger remove_os_sn_row"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    table.find('tbody.os_sn_tbody').append(new_row);

    calculate_os_sn_table_summary(main_tr);
});

// Remove OS Serial Number row
$(document).on('click', '.remove_os_sn_row', function() {
    var main_tr = $(this).closest('table.os_sn_table').closest('td').closest('tr');
    $(this).closest('tr.os_sn_row').remove();
    calculate_os_sn_table_summary(main_tr);
});

// Validate duplicate SN input in opening stock form
$(document).on('change blur', '.os_sn_input', function() {
    var current_input = $(this);
    var current_val = $.trim(current_input.val());
    if (current_val === '') return;

    var form = current_input.closest('form');
    var is_dup = false;

    form.find('.os_sn_input').not(current_input).each(function() {
        if ($.trim($(this).val()).toLowerCase() === current_val.toLowerCase()) {
            is_dup = true;
            return false;
        }
    });

    if (is_dup) {
        toastr.error('Serial Number / IMEI "' + current_val + '" duplikat di dalam form.');
        current_input.val('').focus();
    }
});

// Recalculate main row quantity, average unit cost & selling price when SN row prices or count change
$(document).on('change keyup', '.os_sn_input, .os_sn_pp_input, .os_sn_sp_input', function() {
    var main_tr = $(this).closest('table.os_sn_table').closest('td').closest('tr');
    calculate_os_sn_table_summary(main_tr);
});

function calculate_os_sn_table_summary(main_tr) {
    var sn_table = main_tr.find('.os_sn_table');
    if (!sn_table.length) return;

    var total_pp = 0;
    var total_sp = 0;
    var count = 0;

    sn_table.find('tr.os_sn_row').each(function() {
        var sn = $.trim($(this).find('.os_sn_input').val());
        var pp = __read_number($(this).find('.os_sn_pp_input'));
        var sp = __read_number($(this).find('.os_sn_sp_input'));

        if (sn !== '') {
            count++;
        }
        if (pp != undefined && pp >= 0) {
            total_pp += parseFloat(pp);
        }
        if (sp != undefined && sp >= 0) {
            total_sp += parseFloat(sp);
        }
    });

    var total_rows = sn_table.find('tr.os_sn_row').length;
    var effective_count = count > 0 ? count : total_rows;

    main_tr.find('.purchase_quantity').val(effective_count);

    if (effective_count > 0) {
        var avg_pp = total_pp / effective_count;
        var avg_sp = total_sp / effective_count;

        __write_number(main_tr.find('.unit_price'), avg_pp);
        if (main_tr.find('.selling_price').length) {
            __write_number(main_tr.find('.selling_price'), avg_sp);
        }
    }

    update_table_total(main_tr.closest('table'));
}

$(document).on('click', 'button#add_opening_stock_btn', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = $('form#add_opening_stock_form').serialize();

    $.ajax({
        method: 'POST',
        url: $('form#add_opening_stock_form').attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(btn);
        },
        success: function(result) {
            if (result.success == true) {
                $('#opening_stock_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
    return false;
});

function update_table_total(table) {
    var total_subtotal = 0;
    table.find('tbody tr').each(function() {
        var qty = __read_number($(this).find('.purchase_quantity'));
        var unit_price = __read_number($(this).find('.unit_price'));
        var row_subtotal = qty * unit_price;
        $(this)
            .find('.row_subtotal_before_tax')
            .text(__number_f(row_subtotal));
        total_subtotal += row_subtotal;
    });
    table.find('tfoot tr #total_subtotal').text(__currency_trans_from_en(total_subtotal, true));
    table.find('tfoot tr #total_subtotal_hidden').val(total_subtotal);
}
