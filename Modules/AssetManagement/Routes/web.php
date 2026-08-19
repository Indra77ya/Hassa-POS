<?php

use Illuminate\Support\Facades\Route;
use Modules\AssetManagement\Http\Controllers\AssetCategoryController;
use Modules\AssetManagement\Http\Controllers\AssetController;
use Modules\AssetManagement\Http\Controllers\AssetDepreciationController;

Route::middleware(['web', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])->prefix('asset-management')->group(function () {
    // Categories
    Route::resource('categories', AssetCategoryController::class)->except(['show']);

    // Assets
    Route::resource('assets', AssetController::class)->except(['show']);

    // Depreciation
    Route::get('depreciations', [AssetDepreciationController::class, 'index'])->name('assetmanagement.depreciation.index');
    Route::post('depreciations/run', [AssetDepreciationController::class, 'runDepreciationOnDemand'])->name('assetmanagement.depreciation.run');
});
