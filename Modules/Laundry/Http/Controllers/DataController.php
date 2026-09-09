<?php

namespace Modules\Laundry\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;
use Modules\Laundry\Entities\LaundryOrderSheet;
use Modules\Laundry\Entities\LaundryStatus;
use Modules\Laundry\Entities\LaundryServiceType;
use Modules\Laundry\Entities\LaundryItemType;

class DataController extends Controller
{
    /**
     * Sets sell fields from module
     *
     * @param  array  $data
     * @return obj
     */
    public function after_sale_saved($data)
    {
        $transaction = $data['transaction'];
        $input = $data['input'];

        if (isset($input['laundry_order_sheet_id'])) {
            $transaction->laundry_order_sheet_id = $input['laundry_order_sheet_id'];
            $transaction->save();
        }

        return $transaction;
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'laundry.view_dashboard',
                'label' => __('laundry::lang.view_laundry_dashboard'),
                'default' => false,
            ],
            [
                'value' => 'laundry.view',
                'label' => __('laundry::lang.view_laundry'),
                'default' => false,
            ],
            [
                'value' => 'laundry.create',
                'label' => __('laundry::lang.add_laundry_order'),
                'default' => false,
            ],
            [
                'value' => 'laundry.update',
                'label' => __('laundry::lang.edit_laundry_order'),
                'default' => false,
            ],
            [
                'value' => 'laundry.delete',
                'label' => __('laundry::lang.delete_laundry_order'),
                'default' => false,
            ],
            [
                'value' => 'laundry.update_status',
                'label' => __('laundry::lang.update_laundry_status'),
                'default' => false,
            ],
            [
                'value' => 'laundry.log_process',
                'label' => __('laundry::lang.log_laundry_process'),
                'default' => false,
            ],
            [
                'value' => 'laundry.manage_master_data',
                'label' => __('laundry::lang.manage_laundry_master_data'),
                'default' => false,
            ],
            [
                'value' => 'laundry.view_staff_points',
                'label' => __('laundry::lang.view_staff_points'),
                'default' => false,
            ],
        ];
    }

    /**
     * Registers module in superadmin package
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'laundry_module',
                'label' => __('laundry::lang.laundry_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Laundry menus to admin sidebar
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        $is_laundry_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'laundry_module');

        $can_view_laundry_menu = auth()->check() && (
            auth()->user()->can('superadmin') ||
            auth()->user()->can('laundry.view_dashboard') ||
            auth()->user()->can('laundry.view') ||
            auth()->user()->can('laundry.create') ||
            auth()->user()->can('laundry.update') ||
            auth()->user()->can('laundry.delete') ||
            auth()->user()->can('laundry.update_status') ||
            auth()->user()->can('laundry.log_process') ||
            auth()->user()->can('laundry.manage_master_data') ||
            auth()->user()->can('laundry.view_staff_points')
        );

        if ($is_laundry_enabled && $can_view_laundry_menu) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->dropdown(
                    __('laundry::lang.laundry'),
                    function ($sub) {
                        if (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view_dashboard')) {
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\DashboardController::class, 'index']),
                                __('laundry::lang.dashboard'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'dashboard']
                            );
                        }
                        if (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view') || auth()->user()->can('laundry.create') || auth()->user()->can('laundry.update') || auth()->user()->can('laundry.delete') || auth()->user()->can('laundry.update_status') || auth()->user()->can('laundry.log_process')) {
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'index']),
                                __('laundry::lang.order_sheets'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'order-sheet']
                            );
                        }
                        if (auth()->user()->can('superadmin') || auth()->user()->can('laundry.manage_master_data')) {
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\LaundryStatusController::class, 'index']),
                                __('laundry::lang.statuses'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'statuses']
                            );
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\LaundryProcessController::class, 'index']),
                                __('laundry::lang.processes'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'processes']
                            );
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class, 'index']),
                                __('laundry::lang.service_types'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'service-types']
                            );
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\LaundryItemTypeController::class, 'index']),
                                __('laundry::lang.item_types'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'item-types']
                            );
                        }
                        if (auth()->user()->can('superadmin') || auth()->user()->can('laundry.view_staff_points')) {
                            $sub->url(
                                action([\Modules\Laundry\Http\Controllers\LaundryReportController::class, 'staffPointsReport']),
                                __('laundry::lang.staff_points_report'),
                                ['active' => request()->segment(1) == 'laundry' && request()->segment(2) == 'reports']
                            );
                        }
                    },
                    [
                        'icon' => '<svg class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>',
                        'active' => request()->segment(1) == 'laundry',
                    ]
                )->order(24);
            });
        }
    }

    /**
     * Returns view/js path with required extra data for POS screen
     *
     * @return array
     */
    public function get_pos_screen_view($params)
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        $is_laundry_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'laundry_module');

        if ($is_laundry_enabled && (!is_null($params['sub_type']) && $params['sub_type'] == 'laundry')) {
            $statuses = LaundryStatus::forDropdown($business_id);
            $service_types = LaundryServiceType::forDropdown($business_id);
            $item_types = LaundryItemType::forDropdown($business_id);
            $order_sheets_list = LaundryOrderSheet::where('business_id', $business_id)
                ->with(['itemType', 'transactions'])
                ->get();

            $commonUtil = new \App\Utils\Util();
            $order_sheets = [];
            foreach ($order_sheets_list as $os) {
                $status_label = '';
                if ($os->payment_status == 'partial') {
                    $due = $os->total_amount - $os->total_paid;
                    if ($due < 0) $due = 0;
                    $status_label = ' (' . __('lang_v1.partial') . ' - ' . __('purchase.payment_due') . ': ' . $commonUtil->num_f($due) . ')';
                } elseif ($os->payment_status == 'due') {
                    $status_label = ' (' . __('lang_v1.due') . ')';
                } elseif ($os->payment_status == 'paid') {
                    $status_label = ' (' . __('lang_v1.paid') . ')';
                }
                $order_sheets[$os->id] = $os->order_no . $status_label;
            }

            return [
                'view_path' => 'laundry::laundry.partials.laundry_pos',
                'view_data' => [
                    'statuses' => $statuses,
                    'service_types' => $service_types,
                    'item_types' => $item_types,
                    'order_sheets' => $order_sheets,
                ],
                'module_js_path' => 'laundry::layouts.partials.javascripts',
                'go_back_url' => action([\Modules\Laundry\Http\Controllers\DashboardController::class, 'index']),
                'transaction_sub_type' => 'laundry',
            ];
        } else {
            return [];
        }
    }
}
