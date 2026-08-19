<?php

namespace Modules\AssetManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetDepreciationLog;
use App\Utils\Util;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;

class AssetDepreciationController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.view'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $logs = AssetDepreciationLog::where('asset_depreciation_logs.business_id', $business_id)
                ->join('assets', 'asset_depreciation_logs.asset_id', '=', 'assets.id')
                ->select([
                    'asset_depreciation_logs.id',
                    'asset_depreciation_logs.depreciation_date',
                    'asset_depreciation_logs.amount',
                    'asset_depreciation_logs.accounting_accounts_transaction_debit_id',
                    'asset_depreciation_logs.accounting_accounts_transaction_credit_id',
                    'assets.name as asset_name',
                    'assets.sku as asset_sku'
                ]);

            return DataTables::of($logs)
                ->editColumn('amount', function ($row) {
                    return $this->commonUtil->num_f($row->amount, true);
                })
                ->editColumn('depreciation_date', function ($row) {
                    return $this->commonUtil->format_date($row->depreciation_date);
                })
                ->addColumn('journal_info', function ($row) {
                    if (!empty($row->accounting_accounts_transaction_debit_id) && !empty($row->accounting_accounts_transaction_credit_id)) {
                        return '<span class="label bg-green">' . __('assetmanagement::lang.journal_posted') . ' (#' . $row->accounting_accounts_transaction_debit_id . ')</span>';
                    }
                    return '<span class="label bg-yellow">' . __('assetmanagement::lang.pending') . '</span>';
                })
                ->rawColumns(['journal_info'])
                ->make(true);
        }

        return view('assetmanagement::depreciation.index');
    }

    public function runDepreciationOnDemand(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('asset.run_depreciation'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Call artisan command for the given business
            $exitCode = Artisan::call('asset:run-depreciation', [
                '--business_id' => $business_id,
            ]);

            $output = [
                'success' => true,
                'msg' => __('assetmanagement::lang.depreciation_executed_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong') . ': ' . $e->getMessage(),
            ];
        }

        return $output;
    }
}
