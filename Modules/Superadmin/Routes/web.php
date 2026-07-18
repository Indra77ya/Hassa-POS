<?php

// use App\Http\Controllers\BusinessController;
// use App\Http\Controllers\Modules;
// use Illuminate\Support\Facades\Route;

Route::get('/pricing', [Modules\Superadmin\Http\Controllers\PricingController::class, 'index'])->name('pricing');
Route::get('/package-duration-update', [Modules\Superadmin\Http\Controllers\PricingController::class, 'package_duration_update'])->name('package_duration_update');

Route::middleware('web', 'auth', 'SetSessionData', 'language', 'AdminSidebarMenu', 'superadmin')->prefix('superadmin')->group(function () {
    Route::get('/install', [Modules\Superadmin\Http\Controllers\InstallController::class, 'index']);
    Route::post('/install', [Modules\Superadmin\Http\Controllers\InstallController::class, 'install']);
    Route::get('/install/update', [Modules\Superadmin\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [Modules\Superadmin\Http\Controllers\InstallController::class, 'uninstall']);
    Route::post('/install/update', [Modules\Superadmin\Http\Controllers\InstallController::class, 'updateExecute']);

    Route::get('/', [Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']);
    Route::get('/stats', [Modules\Superadmin\Http\Controllers\SuperadminController::class, 'stats']);

    Route::get('/{business_id}/toggle-active/{is_active}', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'toggleActive']);

    Route::get('/users/{business_id}', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'usersList']);
    Route::post('/update-password', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'updatePassword']);

    Route::get('/business/{id}/reset', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'showResetModal']);
    Route::post('/business/{id}/reset', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'resetBusinessData']);
    Route::resource('/business', Modules\Superadmin\Http\Controllers\BusinessController::class);
    Route::get('/business/{id}/destroy', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'destroy']);

    Route::resource('/packages', 'Modules\Superadmin\Http\Controllers\PackagesController');
    Route::get('/packages/{id}/destroy', [Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy']);

    Route::resource('/coupons', 'Modules\Superadmin\Http\Controllers\CouponController');
    Route::get('/coupons/{id}/destroy', [Modules\Superadmin\Http\Controllers\CouponController::class, 'destroy']);
    
    Route::get('/settings', [Modules\Superadmin\Http\Controllers\SuperadminSettingsController::class, 'edit']);
    Route::put('/settings', [Modules\Superadmin\Http\Controllers\SuperadminSettingsController::class, 'update']);
    Route::get('/edit-subscription/{id}', [Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'editSubscription']);
    Route::post('/update-subscription', [Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'updateSubscription']);
    Route::resource('/superadmin-subscription', 'Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController');

    Route::get('/communicator', [Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'index']);
    Route::post('/communicator/send', [Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'send']);
    Route::get('/communicator/get-history', [Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'getHistory']);

    Route::resource('/frontend-pages', 'Modules\Superadmin\Http\Controllers\PageController');
});

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {
    //Routes related to paypal checkout
    Route::post('/paypal-express-checkout', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'paypalExpressCheckout'])->name('paypalExpressCheckout');

    Route::post('/capture-paypal-order', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'capturePaypalOrder'])->name('capturePaypalOrder');


    Route::get('/subscription/post-flutterwave-payment', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'postFlutterwavePaymentCallback']);

    Route::post('/subscription/pay-stack', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'getRedirectToPaystack']);
    Route::get('/subscription/post-payment-pay-stack-callback', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'postPaymentPaystackCallback']);

    //Routes related to pesapal checkout
    Route::get('/subscription/{package_id}/pesapal-callback', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pesapalCallback'])->name('pesapalCallback');

    Route::get('/subscription/{package_id}/pay', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pay']);
    Route::any('/subscription/{package_id?}/confirm', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'confirm'])->name('subscription-confirm');
    Route::get('/all-subscriptions', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'allSubscriptions']);

    Route::get('/subscription/{package_id}/register-pay', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'registerPay'])->name('register-pay');

    Route::resource('/subscription', 'Modules\Superadmin\Http\Controllers\SubscriptionController');

    Route::get('/subscription/{subcription_id}/force-active', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'forceActive'])->name('force-active');
    Route::get('/myfatoorah-callback', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'myfatoorahcallback'])->name('myfatoorah_callback');

});

Route::get('/page/{slug}', [Modules\Superadmin\Http\Controllers\PageController::class, 'showPage'])->name('frontend-pages');
