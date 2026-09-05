<?php

use Illuminate\Support\Facades\Route;

Route::get('/laundry/status/{order_no?}', [\Modules\Laundry\Http\Controllers\PublicStatusController::class, 'index'])->name('laundry.public_status');
Route::post('/laundry/status/search', [\Modules\Laundry\Http\Controllers\PublicStatusController::class, 'search'])->name('laundry.public_status_search');

Route::middleware(['web', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])->prefix('laundry')->group(function () {
    Route::get('/dashboard', [\Modules\Laundry\Http\Controllers\DashboardController::class, 'index'])->name('laundry.dashboard');

    Route::resource('order-sheet', \Modules\Laundry\Http\Controllers\OrderSheetController::class);
    Route::get('order-sheet/{id}/print', [\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'print'])->name('laundry.order_sheet.print');
    Route::post('order-sheet/{id}/update-status', [\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'updateStatus'])->name('laundry.order_sheet.update_status');
    Route::get('order-sheet/{id}/get-status-modal', [\Modules\Laundry\Http\Controllers\OrderSheetController::class, 'getStatusModal'])->name('laundry.order_sheet.get_status_modal');

    Route::resource('statuses', \Modules\Laundry\Http\Controllers\LaundryStatusController::class);
    Route::resource('processes', \Modules\Laundry\Http\Controllers\LaundryProcessController::class);
    Route::resource('service-types', \Modules\Laundry\Http\Controllers\LaundryServiceTypeController::class);
    Route::resource('item-types', \Modules\Laundry\Http\Controllers\LaundryItemTypeController::class);

    Route::get('reports/staff-points', [\Modules\Laundry\Http\Controllers\LaundryReportController::class, 'staffPointsReport'])->name('laundry.reports.staff_points');

    Route::get('/install', [\Modules\Laundry\Http\Controllers\InstallController::class, 'index']);
    Route::post('/install', [\Modules\Laundry\Http\Controllers\InstallController::class, 'install']);
    Route::get('/install/uninstall', [\Modules\Laundry\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('/install/update', [\Modules\Laundry\Http\Controllers\InstallController::class, 'update']);
});
