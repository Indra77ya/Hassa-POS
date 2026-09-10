<script type="text/javascript">
    function addOrderSheetToCart(order_sheet_id) {
        if (!order_sheet_id) {
            toastr.warning('{{ __("laundry::lang.select_order_sheet") }}');
            return;
        }
        $.ajax({
            url: '/laundry/order-sheet/' + order_sheet_id + '/get-pos-details',
            dataType: 'json',
            success: function(result) {
                if (result.success && result.variation_id) {
                    if (result.contact_id && $('select#customer_id').length) {
                        if ($('select#customer_id option[value="' + result.contact_id + '"]').length) {
                            $('select#customer_id').val(result.contact_id).trigger('change');
                        } else if (result.customer_name) {
                            var newCustomerOption = new Option(result.customer_name, result.contact_id, true, true);
                            $('select#customer_id').append(newCustomerOption).trigger('change');
                        }
                    }
                    if (typeof pos_product_row === 'function') {
                        pos_product_row(result.variation_id, null, null, result.quantity);

                        if (result.due_amount !== undefined && result.due_amount >= 0) {
                            setTimeout(function() {
                                var last_row = $('#pos_table tbody tr').last();
                                if (last_row.length) {
                                    var qty = parseFloat(result.quantity) || 1;
                                    var due = parseFloat(result.due_amount);
                                    var new_unit_price = due / qty;

                                    if (typeof __write_number === 'function') {
                                        __write_number(last_row.find('input.pos_unit_price'), new_unit_price);
                                        __write_number(last_row.find('input.pos_unit_price_inc_tax'), new_unit_price);
                                        __write_number(last_row.find('input.pos_line_total'), due, false);
                                    } else {
                                        last_row.find('input.pos_unit_price').val(new_unit_price);
                                        last_row.find('input.pos_unit_price_inc_tax').val(new_unit_price);
                                        last_row.find('input.pos_line_total').val(due);
                                    }

                                    if (typeof __currency_trans_from_en === 'function') {
                                        last_row.find('span.pos_line_total_text').text(__currency_trans_from_en(due, true));
                                    } else {
                                        last_row.find('span.pos_line_total_text').text(due);
                                    }

                                    if (typeof pos_total_row === 'function') {
                                        pos_total_row();
                                    }
                                }
                            }, 350);
                        }

                        toastr.success('Pesanan laundry berhasil dimasukkan ke keranjang');
                        $('.view_modal').modal('hide');
                    }
                } else {
                    toastr.error('Produk/jasa untuk jenis barang laundry ini tidak ditemukan');
                }
            },
            error: function() {
                toastr.error('Gagal mengambil detail pesanan laundry');
            }
        });
    }

    var is_syncing_laundry_customer = false;

    function syncCustomerFromOrderSheet(order_sheet_id) {
        if (!order_sheet_id || is_syncing_laundry_customer) return;
        is_syncing_laundry_customer = true;
        $.ajax({
            url: '/laundry/order-sheet/' + order_sheet_id + '/get-pos-details',
            dataType: 'json',
            success: function(result) {
                if (result.success && result.contact_id && $('select#customer_id').length) {
                    if ($('select#customer_id option[value="' + result.contact_id + '"]').length) {
                        $('select#customer_id').val(result.contact_id).trigger('change');
                    } else if (result.customer_name) {
                        var newCustomerOption = new Option(result.customer_name, result.contact_id, true, true);
                        $('select#customer_id').append(newCustomerOption).trigger('change');
                    }
                }
                is_syncing_laundry_customer = false;
            },
            error: function() {
                is_syncing_laundry_customer = false;
            }
        });
    }

    function resetLaundryOrderSheetAndCustomer() {
        if (is_syncing_laundry_customer) return;
        is_syncing_laundry_customer = true;

        if (typeof set_default_customer === 'function') {
            set_default_customer();
        } else if ($('#default_customer_id').length && $('select#customer_id').length) {
            var default_customer_id = $('#default_customer_id').val();
            $('select#customer_id').val(default_customer_id).trigger('change');
        }

        $.ajax({
            url: '/laundry/order-sheet/get-order-sheets',
            data: { contact_id: '' },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    var select = $('#laundry_order_sheet_id');
                    var placeholder = '{{ __("laundry::lang.select_order_sheet") }}';
                    select.empty().append(new Option(placeholder, '', true, false));

                    $.each(result.order_sheets, function(id, order_no) {
                        select.append(new Option(order_no, id, false, false));
                    });
                    select.val('');
                }
                is_syncing_laundry_customer = false;
            },
            error: function() {
                is_syncing_laundry_customer = false;
            }
        });
    }

    function filterOrderSheetsByCustomer(contact_id) {
        if (!($('#laundry_order_sheet_id').length) || is_syncing_laundry_customer) return;
        is_syncing_laundry_customer = true;
        var current_order_sheet_id = $('#laundry_order_sheet_id').val();
        var default_customer_id = $('#default_customer_id').length ? $('#default_customer_id').val() : '';

        if (contact_id && contact_id == default_customer_id) {
            contact_id = '';
        }

        $.ajax({
            url: '/laundry/order-sheet/get-order-sheets',
            data: { contact_id: contact_id },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    var select = $('#laundry_order_sheet_id');
                    var placeholder = '{{ __("laundry::lang.select_order_sheet") }}';
                    select.empty().append(new Option(placeholder, '', true, false));

                    var found_current = false;
                    $.each(result.order_sheets, function(id, order_no) {
                        var is_selected = (id == current_order_sheet_id);
                        if (is_selected) found_current = true;
                        select.append(new Option(order_no, id, is_selected, is_selected));
                    });

                    if (!found_current) {
                        select.val('').trigger('change');
                    } else {
                        select.trigger('change');
                    }
                }
                is_syncing_laundry_customer = false;
            },
            error: function() {
                is_syncing_laundry_customer = false;
            }
        });
    }

    $(document).ready(function(){
        if ($('#laundry_order_sheet_id').length) {
            $('#laundry_order_sheet_id').select2({
                placeholder: '{{ __("laundry::lang.select_order_sheet") }}',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
        }

        $(document).off('change', '#laundry_order_sheet_id').on('change', '#laundry_order_sheet_id', function() {
            var order_sheet_id = $(this).val();
            if (order_sheet_id) {
                syncCustomerFromOrderSheet(order_sheet_id);
            } else {
                if (!is_syncing_laundry_customer) {
                    resetLaundryOrderSheetAndCustomer();
                }
            }
        });

        $(document).off('change', 'select#customer_id').on('change', 'select#customer_id', function() {
            var contact_id = $(this).val();
            filterOrderSheetsByCustomer(contact_id);
        });

        $(document).off('click', '#add_laundry_order_sheet_quick_btn').on('click', '#add_laundry_order_sheet_quick_btn', function(e) {
            e.preventDefault();
            var customer_id = $('select#customer_id').length ? $('select#customer_id').val() : '';
            var url = '{{ url("/laundry/order-sheet/create?quick_add=true") }}';
            if (customer_id) {
                url += '&contact_id=' + customer_id;
            }
            $.ajax({
                url: url,
                dataType: 'html',
                success: function(result) {
                    $('.view_modal').html(result).modal('show');
                }
            });
        });

        $(document).off('submit', '#add_status_form, #edit_status_form, #add_process_form, #edit_process_form, #add_service_type_form, #edit_service_type_form, #add_item_type_form, #edit_item_type_form, #update_laundry_status_form, #quick_add_order_sheet_form, #edit_order_sheet_modal_form').on('submit', '#add_status_form, #edit_status_form, #add_process_form, #edit_process_form, #add_service_type_form, #edit_service_type_form, #add_item_type_form, #edit_item_type_form, #update_laundry_status_form, #quick_add_order_sheet_form, #edit_order_sheet_modal_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: form.attr('method'),
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        if (result.data && result.data.id && $('#laundry_order_sheet_id').length) {
                            if (result.data.contact_id && $('select#customer_id').length) {
                                if ($('select#customer_id option[value="' + result.data.contact_id + '"]').length) {
                                    $('select#customer_id').val(result.data.contact_id).trigger('change');
                                } else if (result.data.customer_name) {
                                    var newCustomerOption = new Option(result.data.customer_name, result.data.contact_id, true, true);
                                    $('select#customer_id').append(newCustomerOption).trigger('change');
                                }
                            }
                            var newOption = new Option(result.data.order_no, result.data.id, true, true);
                            $('#laundry_order_sheet_id').append(newOption).trigger('change');
                        }
                        if (typeof statuses_table !== 'undefined') {
                            statuses_table.ajax.reload();
                        }
                        if (typeof processes_table !== 'undefined') {
                            processes_table.ajax.reload();
                        }
                        if (typeof service_types_table !== 'undefined') {
                            service_types_table.ajax.reload();
                        }
                        if (typeof item_types_table !== 'undefined') {
                            item_types_table.ajax.reload();
                        }
                        if (typeof order_sheets_table !== 'undefined') {
                            order_sheets_table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(jqXHR) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.msg) {
                        toastr.error(jqXHR.responseJSON.msg);
                    } else {
                        toastr.error('Terjadi kesalahan');
                    }
                }
            });
        });

        $(document).off('click', '#edit_laundry_order_sheet_btn').on('click', '#edit_laundry_order_sheet_btn', function(e) {
            e.preventDefault();
            var id = $('#laundry_order_sheet_id').val();
            if (!id) {
                toastr.warning('{{ __("laundry::lang.select_order_sheet") }}');
                return;
            }
            $.ajax({
                url: '/laundry/order-sheet/' + id + '/edit',
                dataType: 'html',
                success: function(result) {
                    $('.view_modal').html(result).modal('show');
                }
            });
        });

        $(document).off('click', '#show_laundry_order_sheet_btn').on('click', '#show_laundry_order_sheet_btn', function(e) {
            e.preventDefault();
            var id = $('#laundry_order_sheet_id').val();
            if (!id) {
                toastr.warning('{{ __("laundry::lang.select_order_sheet") }}');
                return;
            }
            $.ajax({
                url: '/laundry/order-sheet/' + id,
                dataType: 'html',
                success: function(result) {
                    $('.view_modal').html(result).modal('show');
                }
            });
        });

        $(document).off('click', '#add_laundry_to_cart_btn').on('click', '#add_laundry_to_cart_btn', function(e) {
            e.preventDefault();
            var id = $('#laundry_order_sheet_id').val();
            addOrderSheetToCart(id);
        });

        $(document).off('click', '.add-laundry-to-cart-modal').on('click', '.add-laundry-to-cart-modal', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            addOrderSheetToCart(id);
        });

        $(document).off('click', '.send_laundry_whatsapp').on('click', '.send_laundry_whatsapp', function(e) {
            e.preventDefault();
            var href = $(this).data('href') || $(this).attr('href');
            var id = $(this).data('id');
            if (!href && id) {
                href = '/laundry/order-sheet/' + id + '/get-whatsapp-link';
            }

            var wa_window = window.open('', '_blank');

            $.ajax({
                url: href,
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        if (result.has_mobile && result.whatsapp_link) {
                            if (wa_window) {
                                wa_window.location.href = result.whatsapp_link;
                            }
                            if (typeof order_sheets_table !== 'undefined') {
                                order_sheets_table.ajax.reload();
                            }
                        } else {
                            if (wa_window) wa_window.close();
                            if ($('#laundry_whatsapp_modal').length === 0) {
                                $('body').append(getLaundryWhatsappModalHtml());
                            }
                            $('#laundry_wa_order_id').val(id);
                            $('#send_laundry_whatsapp_form').attr('action', '/laundry/order-sheet/' + id + '/send-whatsapp-mobile');
                            $('#laundry_wa_customer_info').html('Nomor WhatsApp pelanggan <strong>' + (result.customer_name || '') + '</strong> belum terdaftar. Silakan masukkan nomor WhatsApp untuk mengirim nota order <strong>' + (result.order_no || '') + '</strong>:');
                            if (result.mobile) {
                                $('#laundry_wa_mobile').val(result.mobile);
                            } else {
                                $('#laundry_wa_mobile').val('');
                            }
                            $('#laundry_whatsapp_modal').modal('show');
                        }
                    } else {
                        if (wa_window) wa_window.close();
                        toastr.error(result.msg || 'Gagal mengambil data WhatsApp');
                    }
                },
                error: function() {
                    if (wa_window) wa_window.close();
                    toastr.error('Terjadi kesalahan saat menghubungi server');
                }
            });
        });

        $(document).off('submit', '#send_laundry_whatsapp_form').on('submit', '#send_laundry_whatsapp_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var action = form.attr('action');
            var data = form.serialize();

            var wa_window = window.open('', '_blank');

            $.ajax({
                method: 'POST',
                url: action,
                data: data,
                dataType: 'json',
                success: function(result) {
                    if (result.success && result.whatsapp_link) {
                        if (wa_window) {
                            wa_window.location.href = result.whatsapp_link;
                        }
                        $('#laundry_whatsapp_modal').modal('hide');
                        if (result.msg) {
                            toastr.success(result.msg);
                        }
                        if (typeof order_sheets_table !== 'undefined') {
                            order_sheets_table.ajax.reload();
                        }
                    } else {
                        if (wa_window) wa_window.close();
                        toastr.error(result.msg || 'Gagal membuat link WhatsApp');
                    }
                },
                error: function() {
                    if (wa_window) wa_window.close();
                    toastr.error('Gagal mengirim data nomor WhatsApp');
                }
            });
        });
    });

    function getLaundryWhatsappModalHtml() {
        return '<div class="modal fade" id="laundry_whatsapp_modal" tabindex="-1" role="dialog" aria-labelledby="laundry_whatsapp_modal_label">' +
          '<div class="modal-dialog" role="document">' +
            '<div class="modal-content">' +
              '<div class="modal-header">' +
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<h4 class="modal-title" id="laundry_whatsapp_modal_label"><i class="fab fa-whatsapp fa-fw text-success"></i> Kirim Nota WhatsApp</h4>' +
              '</div>' +
              '<form id="send_laundry_whatsapp_form" method="POST" action="">' +
                '<div class="modal-body">' +
                  '<input type="hidden" id="laundry_wa_order_id" name="order_sheet_id" value="">' +
                  '<p id="laundry_wa_customer_info" class="text-muted"></p>' +
                  '<div class="form-group">' +
                    '<label for="laundry_wa_mobile">Nomor WhatsApp Pelanggan: <span class="text-danger">*</span></label>' +
                    '<input type="text" class="form-control" id="laundry_wa_mobile" name="mobile" placeholder="Contoh: 08123456789" required>' +
                  '</div>' +
                  '<div class="checkbox">' +
                    '<label>' +
                      '<input type="checkbox" id="laundry_wa_save_to_contact" name="save_to_contact" value="1" checked> ' +
                      'Simpan nomor HP ini ke data kontak pelanggan' +
                    '</label>' +
                  '</div>' +
                '</div>' +
                '<div class="modal-footer">' +
                  '<button type="submit" class="btn btn-success" id="laundry_wa_submit_btn"><i class="fab fa-whatsapp"></i> Kirim Ke WhatsApp</button>' +
                  '<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>' +
                '</div>' +
              '</form>' +
            '</div>' +
          '</div>' +
        '</div>';
    }
</script>
