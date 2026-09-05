<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@lang('laundry::lang.laundry_status_lookup')</title>
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
    <style>
        body { background-color: #f8fafc; padding-top: 40px; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .status-badge { font-size: 16px; padding: 8px 15px; border-radius: 20px; color: #fff; display: inline-block; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <div class="card text-center">
            <h2 class="text-primary"><i class="glyphicon glyphicon-search"></i> @lang('laundry::lang.laundry_status_lookup')</h2>
            <p class="text-muted">@lang('laundry::lang.enter_order_no_or_phone')</p>

            <form action="{{ route('laundry.public_status_search') }}" method="POST" class="form-inline" style="margin-top: 20px;">
                @csrf
                <div class="form-group" style="width: 70%;">
                    <input type="text" name="search_key" class="form-control input-lg" style="width: 100%;" placeholder="Misal: LND-2024-0001 atau 08123456789" value="{{ $search ?? $order_no }}" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><i class="glyphicon glyphicon-search"></i> @lang('messages.search')</button>
            </form>
        </div>

        @if(!empty($order_sheet))
            <div class="card">
                <div class="row">
                    <div class="col-md-8">
                        <h3>@lang('laundry::lang.order_no'): <strong>{{ $order_sheet->order_no }}</strong></h3>
                        <p><strong>@lang('contact.customer'):</strong> {{ optional($order_sheet->customer)->name }}</p>
                        <p><strong>@lang('laundry::lang.service_type'):</strong> {{ optional($order_sheet->serviceType)->name }}</p>
                        <p><strong>@lang('laundry::lang.item_type'):</strong> {{ optional($order_sheet->itemType)->name }} ({{ number_format($order_sheet->quantity, 2) }} {{ $order_sheet->unit_name }})</p>
                        <p><strong>@lang('laundry::lang.received_at'):</strong> {{ $order_sheet->received_at ? \Carbon\Carbon::parse($order_sheet->received_at)->format('d/m/Y H:i') : '-' }}</p>
                        <p><strong>@lang('laundry::lang.estimated_completion_at'):</strong> {{ $order_sheet->estimated_completion_at ? \Carbon\Carbon::parse($order_sheet->estimated_completion_at)->format('d/m/Y H:i') : '-' }}</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4>@lang('laundry::lang.current_status')</h4>
                        @if($order_sheet->status)
                            <span class="status-badge" style="background-color: {{ $order_sheet->status->color }};">
                                {{ $order_sheet->status->name }}
                            </span>
                        @else
                            <span class="status-badge bg-gray">-</span>
                        @endif
                    </div>
                </div>

                <hr>
                <h4>@lang('laundry::lang.process_tracking')</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-info">
                                <th>@lang('laundry::lang.process_name')</th>
                                <th>@lang('laundry::lang.status')</th>
                                <th>@lang('laundry::lang.completed_at')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order_sheet->processLogs->where('status', '!=', 'skipped') as $log)
                                <tr>
                                    <td>{{ optional($log->process)->name }}</td>
                                    <td>
                                        @if($log->status == 'completed')
                                            <span class="label bg-green">@lang('laundry::lang.completed')</span>
                                        @else
                                            <span class="label bg-yellow">@lang('laundry::lang.pending')</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->completed_at ? \Carbon\Carbon::parse($log->completed_at)->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(isset($search))
            <div class="alert alert-warning text-center">
                <h4><i class="glyphicon glyphicon-exclamation-sign"></i> @lang('laundry::lang.order_not_found')</h4>
                <p>@lang('laundry::lang.check_search_key_and_retry')</p>
            </div>
        @endif
    </div>
</body>
</html>
