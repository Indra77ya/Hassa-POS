@extends('layouts.app')
@section('title', __('laundry::lang.order_sheet_detail'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.order_sheet_detail') ({{ $order_sheet->order_no }})</h1>
</section>

<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>@lang('laundry::lang.order_no'):</th>
                        <td>{{ $order_sheet->order_no }}</td>
                    </tr>
                    <tr>
                        <th>@lang('contact.customer'):</th>
                        <td>{{ optional($order_sheet->customer)->name }} ({{ optional($order_sheet->customer)->mobile }})</td>
                    </tr>
                    <tr>
                        <th>@lang('purchase.business_location'):</th>
                        <td>{{ optional($order_sheet->location)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('laundry::lang.service_type'):</th>
                        <td>{{ optional($order_sheet->serviceType)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('laundry::lang.item_type'):</th>
                        <td>{{ optional($order_sheet->itemType)->name }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>@lang('laundry::lang.quantity'):</th>
                        <td>{{ number_format($order_sheet->quantity, 2) }} {{ $order_sheet->unit_name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('laundry::lang.status'):</th>
                        <td>
                            @if($order_sheet->status)
                                <span class="label" style="background-color: {{ $order_sheet->status->color }}">{{ $order_sheet->status->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>@lang('laundry::lang.received_at'):</th>
                        <td>{{ $order_sheet->received_at ? \Carbon\Carbon::parse($order_sheet->received_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>@lang('laundry::lang.estimated_completion_at'):</th>
                        <td>{{ $order_sheet->estimated_completion_at ? \Carbon\Carbon::parse($order_sheet->estimated_completion_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>@lang('laundry::lang.delivery_type'):</th>
                        <td>{{ $order_sheet->delivery_type == 'pickup_delivery' ? __('laundry::lang.pickup_delivery') : __('laundry::lang.self_service') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <strong>@lang('laundry::lang.items_detail'):</strong>
                <p class="well well-sm">{{ $order_sheet->items_detail ?: '-' }}</p>
            </div>
            <div class="col-md-12">
                <strong>@lang('brand.note'):</strong>
                <p class="well well-sm">{{ $order_sheet->notes ?: '-' }}</p>
            </div>
        </div>

        <hr>
        <h4 class="text-primary">@lang('laundry::lang.process_history')</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>@lang('laundry::lang.process_name')</th>
                        <th>@lang('laundry::lang.staff_in_charge')</th>
                        <th>@lang('laundry::lang.process_status')</th>
                        <th>@lang('laundry::lang.points_earned')</th>
                        <th>@lang('laundry::lang.completed_at')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order_sheet->processLogs as $log)
                        <tr>
                            <td>{{ optional($log->process)->name }}</td>
                            <td>{{ optional($log->staff)->user_full_name ?? (optional($log->staff)->first_name ? optional($log->staff)->first_name . ' ' . optional($log->staff)->last_name : '-') }}</td>
                            <td>
                                @if($log->status == 'completed')
                                    <span class="label bg-green">@lang('laundry::lang.completed')</span>
                                @else
                                    <span class="label bg-yellow">@lang('laundry::lang.pending')</span>
                                @endif
                            </td>
                            <td>{{ number_format($log->points_earned, 2) }}</td>
                            <td>{{ $log->completed_at ? \Carbon\Carbon::parse($log->completed_at)->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">@lang('lang_v1.no_data')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="row no-print">
            <div class="col-md-12 text-center">
                <a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'print'], [$order_sheet->id]) }}" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> @lang('messages.print')</a>
                <a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'edit'], [$order_sheet->id]) }}" class="btn btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang('messages.edit')</a>
            </div>
        </div>
    @endcomponent
</section>
@endsection
