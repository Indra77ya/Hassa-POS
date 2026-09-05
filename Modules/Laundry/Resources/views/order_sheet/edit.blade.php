@extends('layouts.app')
@section('title', __('laundry::lang.edit_order_sheet'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.edit_order_sheet') ({{ $order_sheet->order_no }})</h1>
</section>

<section class="content">
    {!! Form::open(['url' => action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'update'], [$order_sheet->id]), 'method' => 'put', 'id' => 'edit_order_sheet_form']) !!}
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location') . ':*') !!}
                    {!! Form::select('location_id', $business_locations, $order_sheet->location_id, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_id', __('contact.customer') . ':*') !!}
                    {!! Form::select('contact_id', $customers, $order_sheet->contact_id, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_status_id', __('laundry::lang.status') . ':*') !!}
                    {!! Form::select('laundry_status_id', $statuses, $order_sheet->laundry_status_id, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_service_type_id', __('laundry::lang.service_type') . ':*') !!}
                    {!! Form::select('laundry_service_type_id', $service_types, $order_sheet->laundry_service_type_id, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('laundry_item_type_id', __('laundry::lang.item_type') . ':*') !!}
                    {!! Form::select('laundry_item_type_id', $item_types, $order_sheet->laundry_item_type_id, ['class' => 'form-control select2', 'required']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('quantity', __('laundry::lang.quantity') . ':*') !!}
                            {!! Form::number('quantity', $order_sheet->quantity, ['class' => 'form-control', 'step' => '0.01', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('unit_name', __('laundry::lang.unit') . ':*') !!}
                            {!! Form::text('unit_name', $order_sheet->unit_name, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('delivery_type', __('laundry::lang.delivery_type') . ':') !!}
                    {!! Form::select('delivery_type', ['self_service' => __('laundry::lang.self_service'), 'pickup_delivery' => __('laundry::lang.pickup_delivery')], $order_sheet->delivery_type, ['class' => 'form-control select2']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('received_at', __('laundry::lang.received_at') . ':') !!}
                    {!! Form::text('received_at', $order_sheet->received_at ? \Carbon\Carbon::parse($order_sheet->received_at)->format('Y-m-d H:i') : null, ['class' => 'form-control date-time-picker']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('items_detail', __('laundry::lang.items_detail') . ':') !!}
                    {!! Form::textarea('items_detail', $order_sheet->items_detail, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
            </div>
        </div>

        <hr>
        <h4 class="text-primary">@lang('laundry::lang.process_staff_assignment')</h4>
        <div class="row">
            @php
                $existing_logs = $order_sheet->processLogs->pluck('staff_id', 'laundry_process_id')->toArray();
            @endphp
            @foreach($processes as $proc)
                <div class="col-md-4">
                    <div class="form-group">
                        <label>{{ $proc->name }} (@lang('laundry::lang.points'): {{ $proc->points }}):</label>
                        {!! Form::select("process_staffs[{$proc->id}]", $staffs, isset($existing_logs[$proc->id]) ? $existing_logs[$proc->id] : null, ['class' => 'form-control select2', 'placeholder' => __('laundry::lang.select_staff')]) !!}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label('notes', __('brand.note') . ':') !!}
                    {!! Form::textarea('notes', $order_sheet->notes, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary btn-big">@lang('messages.update')</button>
            </div>
        </div>
    @endcomponent
    {!! Form::close() !!}
</section>
@endsection
