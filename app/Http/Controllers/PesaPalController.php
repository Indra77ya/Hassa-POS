<?php

namespace App\Http\Controllers;

class PesaPalController extends Controller
{
    //Used for pesapal payment confirmation for superadmin module.
    public function pesaPalPaymentConfirmation($transaction_id, $status, $payment_method, $merchant_reference)
    {
        if (class_exists('\Modules\Superadmin\Http\Controllers\PesaPalController')) {
            $class = new \Modules\Superadmin\Http\Controllers\PesaPalController();
            \Log::emergency('transaction_id:'.$transaction_id.'status:'.$status.'payment_method:'.$payment_method);

            $class->pesaPalPaymentConfirmation($transaction_id, $status, $payment_method, $merchant_reference);
        } else {
            \Log::emergency('Superadmin PesaPalController not found.');
            abort(404);
        }
    }
}
