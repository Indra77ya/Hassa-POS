<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('laundry::lang.order_sheet') - {{ $order_sheet->order_no }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .info-table, .process-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td, .info-table th { padding: 6px; }
        .process-table th, .process-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .process-table th { background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 30px; font-size: 11px; }
    </style>
</head>
<body onload="window.print();">
    <div class="header">
        <h2>{{ optional($order_sheet->location)->name }}</h2>
        <p>{{ optional($order_sheet->location)->landmark }} {{ optional($order_sheet->location)->city }}</p>
        <h3>@lang('laundry::lang.laundry_receipt')</h3>
    </div>

    <table class="info-table">
        <tr>
            <strong>@lang('laundry::lang.order_no'):</strong> {{ $order_sheet->order_no }}<br>
            <strong>@lang('contact.customer'):</strong> {{ optional($order_sheet->customer)->name }} ({{ optional($order_sheet->customer)->mobile }})<br>
            <strong>@lang('laundry::lang.service_type'):</strong> {{ optional($order_sheet->serviceType)->name }}<br>
            <strong>@lang('laundry::lang.item_type'):</strong> {{ optional($order_sheet->itemType)->name }}<br>
            <strong>@lang('laundry::lang.quantity'):</strong> {{ number_format($order_sheet->quantity, 2) }} {{ $order_sheet->unit_name }}<br>
            <strong>@lang('laundry::lang.status'):</strong> {{ optional($order_sheet->status)->name }}<br>
            <strong>@lang('laundry::lang.received_at'):</strong> {{ $order_sheet->received_at ? \Carbon\Carbon::parse($order_sheet->received_at)->format('d/m/Y H:i') : '-' }}<br>
            <strong>@lang('laundry::lang.estimated_completion_at'):</strong> {{ $order_sheet->estimated_completion_at ? \Carbon\Carbon::parse($order_sheet->estimated_completion_at)->format('d/m/Y H:i') : '-' }}<br>
            <strong>@lang('laundry::lang.items_detail'):</strong> {{ $order_sheet->items_detail ?: '-' }}
        </tr>
    </table>

    <h4>@lang('laundry::lang.process_status')</h4>
    <table class="process-table">
        <thead>
            <tr>
                <th>@lang('laundry::lang.process_name')</th>
                <th>@lang('laundry::lang.staff_in_charge')</th>
                <th>@lang('laundry::lang.status')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order_sheet->processLogs->where('status', '!=', 'skipped') as $log)
                <tr>
                    <td>{{ optional($log->process)->name }}</td>
                    <td>{{ optional($log->staff)->first_name ? optional($log->staff)->first_name . ' ' . optional($log->staff)->last_name : '-' }}</td>
                    <td>
                        @if($log->status == 'completed')
                            @lang('laundry::lang.completed')
                        @else
                            @lang('laundry::lang.pending')
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>@lang('laundry::lang.track_status_at'): {{ route('laundry.public_status', [$order_sheet->order_no]) }}</p>
        <p>@lang('laundry::lang.thank_you_for_your_business')</p>
    </div>
</body>
</html>
