<script type="text/javascript">
    $(document).ready(function(){
        $(document).on('submit', '#add_status_form, #edit_status_form, #add_process_form, #edit_process_form, #add_service_type_form, #edit_service_type_form, #add_item_type_form, #edit_item_type_form, #update_laundry_status_form, #quick_add_order_sheet_form, #edit_order_sheet_modal_form', function(e) {
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

        $(document).on('click', '#edit_laundry_order_sheet_btn', function(e) {
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

        $(document).on('click', '#show_laundry_order_sheet_btn', function(e) {
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
    });
</script>
