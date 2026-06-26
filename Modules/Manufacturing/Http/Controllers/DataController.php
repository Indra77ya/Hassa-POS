<?php

namespace Modules\Manufacturing\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;
use Modules\Manufacturing\Utils\ManufacturingUtil;

class DataController extends Controller
{
    /**
     * Defines module as a superadmin package.
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'manufacturing_module',
                'label' => __('manufacturing::lang.manufacturing_module'),
                'default' => false,
            ],
        ];
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
                'value' => 'manufacturing.access_recipe',
                'label' => __('manufacturing::lang.access_recipe'),
                'default' => false,
            ],
            [
                'value' => 'manufacturing.add_recipe',
                'label' => __('manufacturing::lang.add_recipe'),
                'default' => false,
            ],
            [
                'value' => 'manufacturing.edit_recipe',
                'label' => __('manufacturing::lang.edit_recipe'),
                'default' => false,
            ],
            [
                'value' => 'manufacturing.access_production',
                'label' => __('manufacturing::lang.access_production'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Manufacturing menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        $is_mfg_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'manufacturing_module', 'superadmin_package');

        if ($is_mfg_enabled && (auth()->user()->can('manufacturing.access_recipe') || auth()->user()->can('manufacturing.access_production'))) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                        action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'index']),
                        __('manufacturing::lang.manufacturing'),
                        ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 21h18"></path>
                        <path d="M5 21v-12l5 4v-4l5 4h4"></path>
                        <path d="M19 21v-8l-1.436 -9.574a.5 .5 0 0 0 -.495 -.426h-1.145a.5 .5 0 0 0 -.494 .418l-1.43 8.582"></path>
                        <path d="M9 17h1"></path>
                        <path d="M14 17h1"></path>
                      </svg>', 'style' => config('app.env') == 'demo' ? 'background-color: #ff851b;color:white' : '', 'active' => request()->segment(1) == 'manufacturing']
                    )
                ->order(21);
            });
        }
    }

    public function profitLossReportData($data)
    {
        $business_id = $data['business_id'];
        $location_id = ! empty($data['location_id']) ? $data['location_id'] : null;
        $start_date = ! empty($data['start_date']) ? $data['start_date'] : null;
        $end_date = ! empty($data['end_date']) ? $data['end_date'] : null;
        $user_id = ! empty($data['user_id']) ? $data['user_id'] : null;
        $permitted_locations = ! empty($data['permitted_locations']) ? $data['permitted_locations'] : null;

        $mfgUtil = new ManufacturingUtil();

        $production_totals = $mfgUtil->getProductionTotals($business_id, $location_id, $start_date, $end_date, $user_id, $permitted_locations);

        $report_data = [
            //left side data
            [
                [
                    'value' => $production_totals['total_production_cost'],
                    'label' => __('manufacturing::lang.total_production_cost'),
                    'add_to_net_profit' => true,
                ],
            ],

            //right side data
            [],
        ];

        return $report_data;
    }
}
