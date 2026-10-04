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

        if ($('.os_sn_select').length) {
            init_os_sn_select($('.os_sn_select'));
        }
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

    if ($('.os_sn_select').length) {
        init_os_sn_select($('.os_sn_select'));
    }
});

// Serial number handling for Opening Stock
function init_os_sn_select(element) {
    element.each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
            return;
        }

        $(this).select2({
            tags: true,
            tokenSeparators: [',', '\n', '\t'],
            placeholder: 'Masukkan / Pilih Serial Number / IMEI',
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
        });

        // Move modal to body to avoid z-index/transform stacking issues
        var tr = $(this).closest('tr');
        var modal = tr.find('.sn_price_modal');
        if (modal.length) {
            $('body').append(modal);
        }
    });
}

// Re-initialize datepicker & SN select on modal opening
 $('#opening_stock_modal').on('shown.bs.modal', function(e) {
    $('#opening_stock_modal .os_exp_date').datepicker({
        autoclose: true,
        format: datepicker_date_format,
    });
    $('#opening_stock_modal .os_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
        widgetPositioning: {
            horizontal: 'right',
            vertical: 'bottom'
        }
    });

    if ($('#opening_stock_modal .os_sn_select').length) {
        init_os_sn_select($('#opening_stock_modal .os_sn_select'));
    }
 });

// Handle OS SN select change
$(document).on('change', '.os_sn_select', function() {
    var tr = $(this).closest('tr');
    var selected_sns = $(this).val() || [];
    var count = selected_sns.length;

    // Update quantity
    var qty_input = tr.find('.purchase_quantity');
    qty_input.val(count);

    // Update row average price if custom SN prices are set
    update_os_sn_row_prices(tr, selected_sns);
    update_table_total(tr.closest('table'));
});

// Update modal content on show
$(document).on('show.bs.modal', '.sn_price_modal', function() {
    var modal = $(this);
    var row_idx = modal.data('row_index');
    var select = $('.os_sn_select[data-row_index="' + row_idx + '"]');

    if (!select.length) return;

    var tr = select.closest('tr');
    var default_pp = tr.find('.unit_price').val() || '0';
    var selected_options = select.find('option:selected');
    var tbody = modal.find('.sn_price_table tbody');
    tbody.empty();

    if (selected_options.length === 0) {
        tbody.append('<tr><td colspan="3" class="text-center text-muted">Belum ada Serial Number / IMEI yang dipilih.</td></tr>');
        return;
    }

    selected_options.each(function() {
        var opt = $(this);
        var sn = opt.val();
        var pp = opt.data('purchase_price') !== undefined ? opt.data('purchase_price') : opt.data('purchase-price');
        var sp = opt.data('selling_price') !== undefined ? opt.data('selling_price') : opt.data('selling-price');

        if (pp === undefined || pp === '' || pp === null) pp = default_pp;
        if (sp === undefined || sp === '' || sp === null) sp = '0';

        var name_prefix = select.attr('name').replace('[serial_numbers][]', '');

        var row_html = '<tr>' +
            '<td><strong>' + sn + '</strong></td>' +
            '<td>' +
            '<input type="hidden" name="' + name_prefix + '[sn_details][' + sn + '][serial_number]" value="' + sn + '">' +
            '<input type="text" name="' + name_prefix + '[sn_details][' + sn + '][purchase_price]" class="form-control input-sm input_number os_sn_pp_input" value="' + pp + '" data-sn="' + sn + '">' +
            '</td>' +
            '<td>' +
            '<input type="text" name="' + name_prefix + '[sn_details][' + sn + '][selling_price]" class="form-control input-sm input_number os_sn_sp_input" value="' + sp + '" data-sn="' + sn + '">' +
            '</td>' +
            '</tr>';
        tbody.append(row_html);
    });
});

// Update option data attributes when SN prices change inside modal
$(document).on('change keyup', '.os_sn_pp_input, .os_sn_sp_input', function() {
    var input = $(this);
    var modal = input.closest('.sn_price_modal');
    var row_idx = modal.data('row_index');
    var select = $('.os_sn_select[data-row_index="' + row_idx + '"]');
    var sn = input.data('sn');
    var val = input.val();

    var opt = select.find('option[value="' + sn + '"]');
    if (opt.length) {
        if (input.hasClass('os_sn_pp_input')) {
            opt.attr('data-purchase_price', val).attr('data-purchase-price', val);
        } else {
            opt.attr('data-selling_price', val).attr('data-selling-price', val);
        }
    }

    var tr = select.closest('tr');
    var selected_sns = select.val() || [];
    update_os_sn_row_prices(tr, selected_sns);
    update_table_total(tr.closest('table'));
});

function update_os_sn_row_prices(tr, selected_sns) {
    if (!selected_sns || selected_sns.length === 0) return;

    var select = tr.find('.os_sn_select');
    var total_pp = 0;
    var count = 0;

    select.find('option:selected').each(function() {
        var pp_str = $(this).attr('data-purchase_price') || $(this).attr('data-purchase-price');
        var pp = __number_uf(pp_str);
        if (pp >= 0) {
            total_pp += pp;
            count++;
        }
    });

    if (count > 0) {
        var avg_pp = total_pp / count;
        tr.find('.unit_price').val(__number_f(avg_pp));
    }
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
