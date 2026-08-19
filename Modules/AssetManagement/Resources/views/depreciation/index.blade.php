@extends('layouts.app')
@section('title', __('assetmanagement::lang.depreciation_logs'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('assetmanagement::lang.depreciation_logs')
        <small>@lang('assetmanagement::lang.depreciation_logs')</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('assetmanagement::lang.depreciation_logs')])
        @slot('tool')
            <div class="box-tools">
                @if(auth()->user()->can('superadmin') || auth()->user()->can('asset.run_depreciation'))
                    <button type="button" class="btn btn-block btn-success btn-run-depreciation">
                        <i class="fa fa-play"></i> @lang('assetmanagement::lang.run_depreciation')</button>
                @endif
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="depreciation_logs_table">
                <thead>
                    <tr>
                        <th>@lang('assetmanagement::lang.assets')</th>
                        <th>SKU / Ref</th>
                        <th>@lang('assetmanagement::lang.purchase_date')</th>
                        <th>@lang('sale.amount')</th>
                        <th>@lang('sale.status')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var depreciation_logs_table = $('#depreciation_logs_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{action([\Modules\AssetManagement\Http\Controllers\AssetDepreciationController::class, "index"])}}',
            columns: [
                { data: 'asset_name', name: 'assets.name' },
                { data: 'asset_sku', name: 'assets.sku' },
                { data: 'depreciation_date', name: 'depreciation_date' },
                { data: 'amount', name: 'amount' },
                { data: 'journal_info', name: 'journal_info', searchable: false, orderable: false }
            ]
        });

        $(document).on('click', '.btn-run-depreciation', function(e) {
            e.preventDefault();
            swal({
                title: 'Run Depreciation?',
                text: 'Proses depresiasi bulan ini akan dihitung dan diposting ke jurnal akuntansi.',
                icon: 'warning',
                buttons: true,
                dangerMode: false,
            }).then(willRun => {
                if (willRun) {
                    $.ajax({
                        method: 'POST',
                        url: '{{action([\Modules\AssetManagement\Http\Controllers\AssetDepreciationController::class, "runDepreciationOnDemand"])}}',
                        dataType: 'json',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(result) {
                            if (result.success === true) {
                                toastr.success(result.msg);
                                depreciation_logs_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
