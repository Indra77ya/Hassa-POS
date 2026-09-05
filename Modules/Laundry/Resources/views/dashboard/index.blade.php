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
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-shopping-basket"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('laundry::lang.total_orders')</span>
                    <span class="info-box-number">{{ $total_orders }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('laundry::lang.pending_orders')</span>
                    <span class="info-box-number">{{ $pending_orders }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">@lang('laundry::lang.completed_orders')</span>
                    <span class="info-box-number">{{ $completed_orders }}</span>
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
                        <th>@lang('laundry::lang.received_at')</th>
                        <th>@lang('laundry::lang.estimated_completion_at')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_orders as $order)
                        <tr>
                            <td><a href="{{ action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'show'], [$order->id]) }}">{{ $order->order_no }}</a></td>
                            <td>{{ optional($order->customer)->name }}</td>
                            <td>{{ optional($order->serviceType)->name }}</td>
                            <td>{{ number_format($order->quantity, 2) }} {{ $order->unit_name }}</td>
                            <td>
                                @if($order->status)
                                    <span class="label" style="background-color: {{ $order->status->color }}">{{ $order->status->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $order->received_at ? \Carbon\Carbon::parse($order->received_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $order->estimated_completion_at ? \Carbon\Carbon::parse($order->estimated_completion_at)->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">@lang('lang_v1.no_data')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</section>
@endsection
