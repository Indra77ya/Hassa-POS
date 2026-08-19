<?php

namespace Modules\AssetManagement\Http;

use Spatie\Permission\Models\Permission;

class AssetPermission
{
    public static function createPermissions()
    {
        $permissions = [
            'asset.view',
            'asset.create',
            'asset.edit',
            'asset.delete',
            'asset.run_depreciation',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }
}
