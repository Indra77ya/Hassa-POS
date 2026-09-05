@extends('layouts.app')
@section('title', __('laundry::lang.add_order_sheet'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.add_order_sheet')</h1>
</section>

<section class="content">
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
                    {!! Form::select('contact_id', $customers, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_status_id', __('laundry::lang.status') . ':*') !!}
                    {!! Form::select('laundry_status_id', $statuses, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_service_type_id', __('laundry::lang.service_type') . ':*') !!}
                    {!! Form::select('laundry_service_type_id', $service_types, null, ['class' => 'form-control select2', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_item_type_id', __('laundry::lang.item_type') . ':*') !!}
                    {!! Form::select('laundry_item_type_id', $item_types, null, ['class' => 'form-control select2', 'id' => 'laundry_item_type_id', 'required', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('quantity', __('laundry::lang.quantity') . ':*') !!}
                            {!! Form::number('quantity', 1, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('unit_name', __('laundry::lang.unit') . ':*') !!}
                            {!! Form::text('unit_name', 'kg', ['class' => 'form-control', 'id' => 'unit_name', 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
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
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('items_detail', __('laundry::lang.items_detail') . ':') !!}
                    {!! Form::textarea('items_detail', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Contoh: 3 Kemeja, 2 Celana Jeans, 1 Bedcover']) !!}
                </div>
            </div>
        </div>

        <hr>
        <h4 class="text-primary">@lang('laundry::lang.process_staff_assignment')</h4>
        <div class="row">
            @foreach($processes as $proc)
                <div class="col-md-4">
                    <div class="form-group">
                        <label>{{ $proc->name }} (@lang('laundry::lang.points'): {{ $proc->points }}):</label>
                        {!! Form::select("process_staffs[{$proc->id}]", $staffs, null, ['class' => 'form-control select2', 'placeholder' => __('laundry::lang.select_staff')]) !!}
                    </div>
                </div>
            @endforeach
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
</section>
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    $(document).on('change', '#laundry_item_type_id', function() {
        var item_type_id = $(this).val();
        if (item_type_id) {
            $.ajax({
                url: '/laundry/item-types/get-details/' + item_type_id,
                dataType: 'json',
                success: function(result) {
                    if (result && result.unit_name) {
                        $('#unit_name').val(result.unit_name);
                    }
                }
            });
        }
    });
});
</script>
@endsection
