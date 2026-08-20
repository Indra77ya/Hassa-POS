@extends('layouts.app')
@section('title', __('assetmanagement::lang.view_asset'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('assetmanagement::lang.view_asset') }} - {{ $asset->name }}</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => $asset->name])
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>@lang('assetmanagement::lang.asset_code')</th>
                        <td>{{ $asset->asset_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.asset_category')</th>
                        <td>{{ $asset->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.location')</th>
                        <td>{{ $asset->location->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.purchase_date')</th>
                        <td>{{ @format_date($asset->purchase_date) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.status')</th>
                        <td><span class="label label-{{ $asset->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($asset->status) }}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>@lang('assetmanagement::lang.purchase_price')</th>
                        <td>{{ @num_format($asset->purchase_price) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.salvage_value')</th>
                        <td>{{ @num_format($asset->salvage_value) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.useful_life')</th>
                        <td>{{ $asset->useful_life }} bulan</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.monthly_depreciation')</th>
                        <td>{{ @num_format($asset->monthly_depreciation) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.accumulated_depreciation')</th>
                        <td>{{ @num_format($asset->total_accumulated_depreciation) }}</td>
                    </tr>
                    <tr>
                        <th>@lang('assetmanagement::lang.net_book_value')</th>
                        <td><strong>{{ @num_format($asset->net_book_value) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-info', 'title' => __('assetmanagement::lang.depreciation_logs')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tahun/Bulan</th>
                        <th>Jumlah Penyusutan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asset->depreciationLogs as $log)
                        <tr>
                            <td>{{ @format_date($log->depreciation_date) }}</td>
                            <td>{{ $log->year }} - {{ sprintf('%02d', $log->month) }}</td>
                            <td>{{ @num_format($log->amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Belum ada riwayat penyusutan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endcomponent
</section>
@endsection
