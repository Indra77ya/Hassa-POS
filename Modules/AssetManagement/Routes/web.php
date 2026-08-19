<?php

use Illuminate\Support\Facades\Route;
use Modules\AssetManagement\Http\Controllers\AssetController;
use Modules\AssetManagement\Http\Controllers\AssetCategoryController;
use Modules\AssetManagement\Http\Controllers\AssetSettingController;

Route::middleware(['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'])->group(function () {
    Route::resource('assets', AssetController::class);
    Route::resource('asset-categories', AssetCategoryController::class)->except(['create', 'show', 'edit']);
    Route::get('asset-settings', [AssetSettingController::class, 'index'])->name('asset-settings.index');
    Route::post('asset-settings', [AssetSettingController::class, 'store'])->name('asset-settings.store');
});
