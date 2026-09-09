@extends('layouts.app')
@section('title', __('laundry::lang.laundry_dashboard'))

@section('content')
<section class="content-header">
    <h1>@lang('laundry::lang.laundry_dashboard')
        {!! Form::open(['url' => route('laundry.import_demo_data'), 'method' => 'post', 'style' => 'display:inline-block; float: right;']) !!}
            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Apakah Anda yakin ingin memasukkan data demo laundry?')">
                <i class="fa fa-database"></i> @lang('laundry::lang.import_demo_data')
            </button>
        {!! Form::close() !!}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-basket"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">@lang('laundry::lang.total_orders')</span>
                    <span class="info-box-number" style="font-size: 24px; font-weight: bold;">{{ $total_orders }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">@lang('laundry::lang.pending_orders')</span>
                    <span class="info-box-number" style="font-size: 24px; font-weight: bold;">{{ $pending_orders }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-muted" style="font-size: 13px; font-weight: 600; text-transform: uppercase;">@lang('laundry::lang.completed_orders')</span>
                    <span class="info-box-number" style="font-size: 24px; font-weight: bold;">{{ $completed_orders }}</span>
                </div>
            </div>
        </div>
    </div>

    @component('components.widget', ['class' => 'box-primary', 'title' => __('laundry::lang.recent_orders')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>@lang('laundry::lang.order_no')</th>
                        <th>@lang('contact.customer')</th>
                        <th>@lang('laundry::lang.service_type')</th>
                        <th>@lang('laundry::lang.quantity')</th>
                        <th>@lang('laundry::lang.status')</th>
                        <th>@lang('sale.payment_status')</th>
                        <th>@lang('laundry::lang.received_at')</th>
                        <th>@lang('laundry::lang.estimated_completion_at')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_orders as $order)
                        <tr>
                            <td><a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'show'], [$order->id]) }}" style="font-weight: 600;">{{ $order->order_no }}</a></td>
                            <td>{{ optional($order->customer)->name }}</td>
                            <td>{{ optional($order->serviceType)->name }}</td>
                            <td>{{ @format_quantity($order->quantity) }} {{ $order->unit_name }}</td>
                            <td>
                                @if($order->status)
                                    <span class="label" style="background-color: {{ $order->status->color }}; color: #ffffff; padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; display: inline-block;">{{ $order->status->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($order->payment_status == 'paid')
                                    <span class="label bg-green" style="padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; display: inline-block;">@lang('lang_v1.paid')</span>
                                @elseif($order->payment_status == 'partial')
                                    <span class="label bg-yellow" style="padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; display: inline-block; color: #ffffff;">@lang('lang_v1.partial')</span>
                                @else
                                    <span class="label bg-red" style="padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; display: inline-block;">@lang('lang_v1.due')</span>
                                @endif
                            </td>
                            <td>{{ $order->received_at ? \Carbon\Carbon::parse($order->received_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $order->estimated_completion_at ? \Carbon\Carbon::parse($order->estimated_completion_at)->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">@lang('lang_v1.no_data')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</section>
@endsection
