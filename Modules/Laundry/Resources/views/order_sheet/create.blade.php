@extends('layouts.app')
@section('title', __('laundry::lang.order_sheets'))

@section('content')
@include('laundry::layouts.partials.header')

<!-- Content Header (Page header) -->
<section class="content-header tw-p-4 md:tw-p-6">
    <h1 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-gray-800">
        @lang('laundry::lang.create_order_sheet')
    </h1>
</section>

<!-- Main content -->
<section class="content tw-p-4 md:tw-p-6">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'store']), 'method' => 'post', 'id' => 'add_order_sheet_form']) !!}
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::select('contact_id', $customers, $contact_id, ['class' => 'form-control select2', 'id' => 'contact_id', 'required', 'placeholder' => __('messages.please_select'), 'style' => 'width:100%']) !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name="" @if(auth()->check() && !auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_service_type_id', __('laundry::lang.service_type') . ':*') !!}
                    {!! Form::select('laundry_service_type_id', $service_types, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_status_id', __('laundry::lang.order_status') . ':*') !!}
                    {!! Form::select('laundry_status_id', $statuses, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('delivery_type', __('laundry::lang.delivery_type') . ':') !!}
                    {!! Form::select('delivery_type', ['self_service' => __('laundry::lang.self_service'), 'pickup_delivery' => __('laundry::lang.pickup_delivery')], 'self_service', ['class' => 'form-control select2']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('received_at', __('laundry::lang.received_at') . ':') !!}
                    {!! Form::text('received_at', \Carbon\Carbon::now()->format('Y-m-d H:i'), ['class' => 'form-control date-time-picker']) !!}
                </div>
            </div>
        </div>

        <hr>
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-primary pull-left" style="margin-top:0;">
                    <i class="fa fa-shopping-basket"></i> Item Cucian Laundry (Multi-Item)
                </h4>
                <button type="button" class="btn btn-success btn-xs pull-right" id="add_item_row_btn">
                    <i class="fa fa-plus"></i> Tambah Item Cucian
                </button>
            </div>
        </div>

        <div class="table-responsive" style="margin-bottom: 20px;">
            <table class="table table-bordered table-striped" id="item_rows_table">
                <thead>
                    <tr class="bg-gray">
                        <th class="col-md-3">@lang('laundry::lang.item_type')</th>
                        <th class="col-md-2">@lang('laundry::lang.quantity')</th>
                        <th class="col-md-2">@lang('laundry::lang.unit')</th>
                        <th class="col-md-2">Harga / Satuan</th>
                        <th class="col-md-2">Subtotal</th>
                        <th class="col-md-1 text-center"><i class="fa fa-trash"></i></th>
                    </tr>
                </thead>
                <tbody id="item_rows_container">
                    <tr class="item-row">
                        <td>
                            <select name="items[0][laundry_item_type_id]" class="form-control select2 item-type-select" required style="width:100%">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($item_types_all as $it)
                                    <option value="{{ $it->id }}" data-price="{{ $it->default_price }}" data-unit="{{ $it->unit_name }}">
                                        {{ $it->name }} (Rp {{ @num_format($it->default_price) }}/{{ $it->unit_name }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control item-qty" value="1" step="0.01" required>
                        </td>
                        <td>
                            <input type="text" name="items[0][unit_name]" class="form-control item-unit" value="kg" required>
                        </td>
                        <td>
                            <input type="number" name="items[0][unit_price]" class="form-control item-price" value="0" step="0.01" required>
                        </td>
                        <td>
                            <input type="text" class="form-control item-subtotal" value="0" readonly>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs remove-item-row"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Total Est. Harga Laundry:</th>
                        <th colspan="2" class="text-primary font-bold"><span id="grand_total_items_text">Rp 0</span></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('items_detail', __('laundry::lang.items_detail') . ':') !!}
                    {!! Form::textarea('items_detail', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Contoh: 1 Bedcover ukuran No. 1, 2 Pasang Sepatu Nike']) !!}
                </div>
            </div>
        </div>

        <hr>
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-primary pull-left" style="margin-top:0;">@lang('laundry::lang.process_staff_assignment')</h4>
                <button type="button" class="btn btn-success btn-xs pull-right" id="add_process_row_btn">
                    <i class="fa fa-plus"></i> @lang('laundry::lang.add_process_row')
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="process_rows_table">
                <thead>
                    <tr>
                        <th class="col-md-4">@lang('laundry::lang.process_name')</th>
                        <th class="col-md-4">@lang('laundry::lang.staff_in_charge')</th>
                        <th class="col-md-3">@lang('laundry::lang.process_status')</th>
                        <th class="col-md-1 text-center">@lang('messages.action')</th>
                    </tr>
                </thead>
                <tbody id="process_rows_container">
                    @foreach($processes as $index => $proc)
                        <tr class="process-row">
                            <td>
                                <select name="process_rows[{{ $index }}][process_id]" class="form-control select2 process-select" required style="width:100%">
                                    <option value="">@lang('laundry::lang.select_process')</option>
                                    @foreach($processes as $p)
                                        <option value="{{ $p->id }}" {{ $p->id == $proc->id ? 'selected' : '' }}>
                                            {{ $p->name }} (@lang('laundry::lang.points'): {{ $p->points }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="process_rows[{{ $index }}][staff_id]" class="form-control select2 staff-select" style="width:100%">
                                    <option value="">@lang('laundry::lang.select_staff')</option>
                                    @foreach($staffs as $s_id => $s_name)
                                        <option value="{{ $s_id }}">{{ $s_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="process_rows[{{ $index }}][status]" class="form-control select2 status-select" style="width:100%">
                                    <option value="pending">@lang('laundry::lang.pending')</option>
                                    <option value="in_progress">@lang('laundry::lang.in_progress')</option>
                                    <option value="completed">@lang('laundry::lang.completed')</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-xs remove-process-row"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('notes', __('brand.note') . ':') !!}
                    {!! Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary btn-big">@lang('messages.save')</button>
            </div>
        </div>
    @endcomponent
    {!! Form::close() !!}

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    var item_options_html = `<option value="">{{ __('messages.please_select') }}</option>`;
    @foreach($item_types_all as $it)
        item_options_html += `<option value="{{ $it->id }}" data-price="{{ $it->default_price }}" data-unit="{{ e($it->unit_name) }}">{{ e($it->name) }} (Rp {{ @num_format($it->default_price) }}/{{ e($it->unit_name) }})</option>`;
    @endforeach

    function calculateItemSubtotal($row) {
        var qty = parseFloat($row.find('.item-qty').val()) || 0;
        var price = parseFloat($row.find('.item-price').val()) || 0;
        var subtotal = qty * price;
        $row.find('.item-subtotal').val(subtotal.toLocaleString('id-ID'));
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        var total = 0;
        $('#item_rows_container tr.item-row').each(function() {
            var qty = parseFloat($(this).find('.item-qty').val()) || 0;
            var price = parseFloat($(this).find('.item-price').val()) || 0;
            total += (qty * price);
        });
        $('#grand_total_items_text').text('Rp ' + total.toLocaleString('id-ID'));
    }

    function addItemRow() {
        var idx = new Date().getTime() + Math.floor(Math.random() * 1000);
        var row_html = `<tr class="item-row">
            <td>
                <select name="items[${idx}][laundry_item_type_id]" class="form-control select2 item-type-select" required style="width:100%">
                    ${item_options_html}
                </select>
            </td>
            <td>
                <input type="number" name="items[${idx}][quantity]" class="form-control item-qty" value="1" step="0.01" required>
            </td>
            <td>
                <input type="text" name="items[${idx}][unit_name]" class="form-control item-unit" value="kg" required>
            </td>
            <td>
                <input type="number" name="items[${idx}][unit_price]" class="form-control item-price" value="0" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control item-subtotal" value="0" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs remove-item-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;

        var $row = $(row_html);
        $('#item_rows_container').append($row);
        $row.find('.select2').select2();
    }

    $(document).on('click', '#add_item_row_btn', function() {
        addItemRow();
    });

    $(document).on('click', '.remove-item-row', function() {
        if ($('#item_rows_container tr.item-row').length > 1) {
            $(this).closest('tr.item-row').remove();
            calculateGrandTotal();
        } else {
            toastr.warning('Minimal harus ada 1 item cucian.');
        }
    });

    $(document).on('change', '.item-type-select', function() {
        var $row = $(this).closest('tr.item-row');
        var selected_opt = $(this).find('option:selected');
        var price = selected_opt.data('price') || 0;
        var unit = selected_opt.data('unit') || 'kg';

        $row.find('.item-price').val(price);
        $row.find('.item-unit').val(unit);
        calculateItemSubtotal($row);
    });

    $(document).on('input change', '.item-qty, .item-price', function() {
        var $row = $(this).closest('tr.item-row');
        calculateItemSubtotal($row);
    });

    var process_options_html = `<option value="">{{ __('laundry::lang.select_process') }}</option>`;
    @foreach($processes as $p)
        process_options_html += `<option value="{{ $p->id }}">{{ e($p->name) }} ({{ __('laundry::lang.points') }}: {{ $p->points }})</option>`;
    @endforeach

    var staff_options_html = `<option value="">{{ __('laundry::lang.select_staff') }}</option>`;
    @foreach($staffs as $s_id => $s_name)
        staff_options_html += `<option value="{{ $s_id }}">{{ e($s_name) }}</option>`;
    @endforeach

    function addProcessRow(selected_process_id, selected_staff_id, selected_status) {
        var idx = new Date().getTime() + Math.floor(Math.random() * 1000);
        var row_html = `<tr class="process-row">
            <td>
                <select name="process_rows[${idx}][process_id]" class="form-control select2 process-select" required style="width:100%">
                    ${process_options_html}
                </select>
            </td>
            <td>
                <select name="process_rows[${idx}][staff_id]" class="form-control select2 staff-select" style="width:100%">
                    ${staff_options_html}
                </select>
            </td>
            <td>
                <select name="process_rows[${idx}][status]" class="form-control select2 status-select" style="width:100%">
                    <option value="pending">{{ __('laundry::lang.pending') }}</option>
                    <option value="in_progress">{{ __('laundry::lang.in_progress') }}</option>
                    <option value="completed">{{ __('laundry::lang.completed') }}</option>
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs remove-process-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;

        var $row = $(row_html);
        if (selected_process_id) $row.find('.process-select').val(selected_process_id);
        if (selected_staff_id) $row.find('.staff-select').val(selected_staff_id);
        if (selected_status) $row.find('.status-select').val(selected_status);

        $('#process_rows_container').append($row);
        $row.find('.select2').select2();
    }

    $(document).on('click', '#add_process_row_btn', function() {
        addProcessRow(null, null);
    });

    $(document).on('click', '.remove-process-row', function() {
        $(this).closest('tr.process-row').remove();
    });
});
</script>
@endsection
