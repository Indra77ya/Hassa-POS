<?php

namespace Modules\AssetManagement\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'assetmanagement_module',
                'label' => __('assetmanagement::lang.assetmanagement_module'),
                'default' => true,
            ],
        ];
    }

    /**
     * Adds sidebar menu
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_assetmanagement_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'assetmanagement_module');

        if (auth()->user()->can('asset.view') || auth()->user()->can('asset.create') || $is_assetmanagement_enabled) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) {
                    $menu->dropdown(
                        __('assetmanagement::lang.asset_management'),
                        function ($sub) {
                            $sub->url(
                                action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'index']),
                                __('assetmanagement::lang.assets'),
                                ['icon' => '', 'active' => request()->segment(1) == 'assets' && request()->segment(2) == '']
                            );
                            $sub->url(
                                action([\Modules\AssetManagement\Http\Controllers\AssetCategoryController::class, 'index']),
                                __('assetmanagement::lang.asset_categories'),
                                ['icon' => '', 'active' => request()->segment(1) == 'asset-categories']
                            );
                            $sub->url(
                                action([\Modules\AssetManagement\Http\Controllers\AssetSettingController::class, 'index']),
                                __('assetmanagement::lang.asset_settings'),
                                ['icon' => '', 'active' => request()->segment(1) == 'asset-settings']
                            );
                        },
                        ['icon' => '<svg class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>', 'active' => in_array(request()->segment(1), ['assets', 'asset-categories', 'asset-settings'])]
                    )->order(48);
                }
            );
        }
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
                'value' => 'asset.view',
                'label' => __('assetmanagement::lang.view_asset'),
                'default' => false,
            ],
            [
                'value' => 'asset.create',
                'label' => __('assetmanagement::lang.add_asset'),
                'default' => false,
            ],
            [
                'value' => 'asset.edit',
                'label' => __('assetmanagement::lang.edit_asset'),
                'default' => false,
            ],
            [
                'value' => 'asset.delete',
                'label' => __('assetmanagement::lang.delete_asset'),
                'default' => false,
            ],
        ];
    }
}
