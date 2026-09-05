<?php

use Illuminate\Support\Facades\Route;

Route::get('/laundry/status/{order_no?}', 'PublicStatusController@index')->name('laundry.public_status');
Route::post('/laundry/status/search', 'PublicStatusController@search')->name('laundry.public_status_search');

Route::middleware(['web', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])->prefix('laundry')->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('laundry.dashboard');

    Route::resource('order-sheet', 'OrderSheetController');
    Route::get('order-sheet/{id}/print', 'OrderSheetController@print')->name('laundry.order_sheet.print');
    Route::post('order-sheet/{id}/update-status', 'OrderSheetController@updateStatus')->name('laundry.order_sheet.update_status');
    Route::get('order-sheet/{id}/get-status-modal', 'OrderSheetController@getStatusModal')->name('laundry.order_sheet.get_status_modal');

    Route::resource('statuses', 'LaundryStatusController');
    Route::resource('processes', 'LaundryProcessController');
    Route::resource('service-types', 'LaundryServiceTypeController');
    Route::resource('item-types', 'LaundryItemTypeController');

    Route::get('reports/staff-points', 'LaundryReportController@staffPointsReport')->name('laundry.reports.staff_points');

    Route::get('/install', 'InstallController@index');
    Route::post('/install', 'InstallController@install');
    Route::get('/install/uninstall', 'InstallController@uninstall');
    Route::get('/install/update', 'InstallController@update');
});
