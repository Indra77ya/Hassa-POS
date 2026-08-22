<?php

namespace App\Http\Controllers;

use App\User;
use App\Utils\Util;
use DataTables;
use DB;
use Illuminate\Http\Request;

class SalesCommissionAgentController extends Controller
{
    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('user.view') && ! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $users = User::where('business_id', $business_id)
                        ->where('is_cmmsn_agnt', 1)
                        ->select(['id',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
                            'email', 'contact_no', 'address', 'cmmsn_percent', ]);

            return Datatables::of($users)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            @lang("messages.actions") <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                            @can("user.update")
                                <li>
                                    <button type="button" data-href="{{action(\'App\Http\Controllers\SalesCommissionAgentController@edit\', [$id])}}" data-container=".commission_agent_modal" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100 hover:tw-text-gray-900 tw-bg-transparent tw-border-none tw-outline-none btn-modal">
                                        <i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")
                                    </button>
                                </li>
                            @endcan
                            @can("user.delete")
                                <li>
                                    <button type="button" data-href="{{action(\'App\Http\Controllers\SalesCommissionAgentController@destroy\', [$id])}}" class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100 hover:tw-text-gray-900 tw-bg-transparent tw-border-none tw-outline-none delete_commsn_agnt_button">
                                        <i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")
                                    </button>
                                </li>
                            @endcan
                        </ul>
                    </div>'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('sales_commission_agent.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('sales_commission_agent.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['surname', 'first_name', 'last_name', 'email', 'address', 'contact_no', 'cmmsn_percent']);
            $input['cmmsn_percent'] = $this->commonUtil->num_uf($input['cmmsn_percent']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $input['allow_login'] = 0;
            $input['is_cmmsn_agnt'] = 1;

            $user = User::create($input);

            $output = ['success' => true,
                'msg' => __('lang_v1.commission_agent_added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);

        return view('sales_commission_agent.edit')
                    ->with(compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['surname', 'first_name', 'last_name', 'email', 'address', 'contact_no', 'cmmsn_percent']);
                $input['cmmsn_percent'] = $this->commonUtil->num_uf($input['cmmsn_percent']);
                $business_id = $request->session()->get('user.business_id');

                $user = User::where('id', $id)
                            ->where('business_id', $business_id)
                            ->where('is_cmmsn_agnt', 1)
                            ->first();
                $user->update($input);

                $output = ['success' => true,
                    'msg' => __('lang_v1.commission_agent_updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                User::where('id', $id)
                    ->where('business_id', $business_id)
                    ->where('is_cmmsn_agnt', 1)
                    ->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.commission_agent_deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
}
