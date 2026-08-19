<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])->prefix('asset-management')->group(function () {
    // Categories
    Route::resource('categories', 'AssetCategoryController')->except(['show']);

    // Assets
    Route::resource('assets', 'AssetController')->except(['show']);

    // Depreciation
    Route::get('depreciations', 'AssetDepreciationController@index')->name('assetmanagement.depreciation.index');
    Route::post('depreciations/run', 'AssetDepreciationController@runDepreciationOnDemand')->name('assetmanagement.depreciation.run');
});
