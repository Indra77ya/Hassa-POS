<?php

use Illuminate\Support\Facades\Route;
use Modules\AssetManagement\Http\Controllers\AssetController;
use Modules\AssetManagement\Http\Controllers\AssetCategoryController;
use Modules\AssetManagement\Http\Controllers\AssetSettingController;
use Modules\AssetManagement\Http\Controllers\InstallController;

Route::middleware(['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'])->group(function () {
    Route::resource('assets', AssetController::class);
    Route::resource('asset-categories', AssetCategoryController::class)->except(['create', 'show', 'edit']);
    Route::get('asset-settings', [AssetSettingController::class, 'index'])->name('asset-settings.index');
    Route::post('asset-settings', [AssetSettingController::class, 'store'])->name('asset-settings.store');
});

// Installation routes
Route::middleware(['web', 'SetSessionData', 'auth', 'language', 'timezone'])->prefix('assetmanagement')->group(function () {
    Route::get('install', [InstallController::class, 'index']);
    Route::post('install', [InstallController::class, 'install']);
    Route::get('install/uninstall', [InstallController::class, 'uninstall']);
    Route::get('install/update', [InstallController::class, 'update']);
});
