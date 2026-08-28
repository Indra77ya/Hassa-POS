<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Create Midtrans Snap Token for a transaction
     */
    public function createSnapToken(Request $request, $transaction_id)
    {
        try {
            $user = auth()->user();
            $transaction = Transaction::with(['business', 'contact'])->findOrFail($transaction_id);

            // Authorization check
            if ($user) {
                if ($user->business_id != $transaction->business_id && !$user->can('superadmin')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized action.',
                    ], 403);
                }
            } else {
                // Unauthenticated guest must provide matching invoice_token
                $guestToken = $request->input('token');
                if (empty($guestToken) || empty($transaction->invoice_token) || $guestToken !== $transaction->invoice_token) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized action.',
                    ], 403);
                }
            }

            $pos_settings = empty($transaction->business->pos_settings) ? [] : json_decode($transaction->business->pos_settings, true);

            if (empty($pos_settings['enable_midtrans']) || empty($pos_settings['midtrans_server_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Midtrans payment gateway is not configured or disabled.',
                ], 400);
            }

            $serverKey = trim($pos_settings['midtrans_server_key']);
            $isProduction = (!empty($pos_settings['midtrans_mode']) && $pos_settings['midtrans_mode'] === 'production');
            $baseUrl = $isProduction
                ? 'https://app.midtrans.com/snap/v1/transactions'
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

            $grossAmount = (int) round($transaction->final_total);
            $orderId = 'MID-POS-' . $transaction->id . '-' . time();

            $payload = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $transaction->contact->name ?? 'Customer',
                    'email' => $transaction->contact->email ?? 'customer@example.com',
                    'phone' => $transaction->contact->mobile ?? '',
                ],
                'custom_field1' => (string) $transaction->id,
            ];

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
            ])->post($baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'token' => $data['token'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                    'client_key' => $pos_settings['midtrans_client_key'] ?? '',
                    'is_production' => $isProduction,
                ]);
            } else {
                Log::error('Midtrans Snap Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create Midtrans Snap Token: ' . ($response->json()['error_messages'][0] ?? $response->status()),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Midtrans Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Midtrans Webhook Notification
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = $request->all();
            Log::info('Midtrans Notification received:', $notification);

            $orderId = $notification['order_id'] ?? null;
            $transactionStatus = $notification['transaction_status'] ?? null;
            $fraudStatus = $notification['fraud_status'] ?? null;
            $grossAmount = $notification['gross_amount'] ?? 0;
            $transactionIdCustom = $notification['custom_field1'] ?? null;

            if (!$orderId) {
                return response()->json(['message' => 'Order ID missing'], 400);
            }

            // Handle Midtrans Dashboard "Test notification URL" ping
            if (strpos($orderId, 'payment_notif_test_') === 0 || strpos($orderId, 'test_') === 0) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Test notification received successfully'
                ], 200);
            }

            // Check if Superadmin Subscription order (MID-SUB-{business_id}-{package_id}-{timestamp})
            if (strpos($orderId, 'MID-SUB-') === 0) {
                $serverKey = env('MIDTRANS_SERVER_KEY');
                if (empty($serverKey)) {
                    Log::warning('Superadmin Midtrans Webhook Rejected: Server key not configured');
                    return response()->json(['message' => 'Superadmin Midtrans server key not configured'], 400);
                }

                if (!isset($notification['signature_key']) || !isset($notification['status_code'])) {
                    return response()->json(['message' => 'Missing signature key or status code'], 400);
                }

                $signatureKey = hash('sha512', $orderId . $notification['status_code'] . $grossAmount . $serverKey);
                if ($signatureKey !== $notification['signature_key']) {
                    return response()->json(['message' => 'Invalid signature'], 403);
                }

                if ($transactionStatus == 'settlement' || ($transactionStatus == 'capture' && $fraudStatus == 'accept')) {
                    if (preg_match('/MID-SUB-(\d+)-(\d+)-\d+/', $orderId, $matches)) {
                        $businessId = $matches[1];
                        $packageId = $matches[2];
                        $couponCode = $notification['custom_field3'] ?? null;

                        $subController = new \Modules\Superadmin\Http\Controllers\SubscriptionController();
                        $subController->_add_subscription($couponCode, $grossAmount, $businessId, (int)$packageId, 'midtrans', $orderId, 1);
                    }
                }

                return response()->json(['status' => 'success']);
            }

            // Extract POS transaction ID from order_id format (MID-POS-{id}-{timestamp}) or custom_field1
            $transactionId = $transactionIdCustom;
            if (!$transactionId && preg_match('/MID-POS-(\d+)-\d+/', $orderId, $matches)) {
                $transactionId = $matches[1];
            }

            if (!$transactionId) {
                return response()->json(['message' => 'Transaction ID not recognized'], 400);
            }

            $transaction = Transaction::with('business')->find($transactionId);
            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Verify signature key if server key is present
            $pos_settings = empty($transaction->business->pos_settings) ? [] : json_decode($transaction->business->pos_settings, true);
            $serverKey = $pos_settings['midtrans_server_key'] ?? '';

            if (empty($serverKey)) {
                Log::warning('Midtrans Webhook Rejected: Server key not configured');
                return response()->json(['message' => 'Midtrans server key not configured'], 400);
            }

            if (!isset($notification['signature_key']) || !isset($notification['status_code'])) {
                Log::warning('Midtrans Missing Signature Key or Status Code');
                return response()->json(['message' => 'Missing signature key or status code'], 400);
            }

            $statusCode = $notification['status_code'];
            $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if ($signatureKey !== $notification['signature_key']) {
                Log::warning('Midtrans Invalid Signature Key');
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            // Check status and update payment if settled/captured
            $isPaid = false;
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    // Challenge by FDS
                } else if ($fraudStatus == 'accept') {
                    $isPaid = true;
                }
            } else if ($transactionStatus == 'settlement') {
                $isPaid = true;
            }

            if ($isPaid) {
                $this->finalizeAndPayTransaction($transaction, $orderId);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Exception: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Client-side POS callback endpoint to sync payment after Midtrans Snap success
     */
    public function syncPayment(Request $request, $transaction_id)
    {
        try {
            $user = auth()->user();
            $transaction = Transaction::with(['business', 'sell_lines.product'])->findOrFail($transaction_id);

            // Authorization check
            if ($user) {
                if ($user->business_id != $transaction->business_id && !$user->can('superadmin')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized action.',
                    ], 403);
                }
            } else {
                $guestToken = $request->input('token');
                if (empty($guestToken) || empty($transaction->invoice_token) || $guestToken !== $transaction->invoice_token) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized action.',
                    ], 403);
                }
            }

            $orderId = $request->input('order_id', 'MID-POS-SYNC-' . $transaction->id);
            $this->finalizeAndPayTransaction($transaction, $orderId);

            return response()->json([
                'success' => true,
                'message' => 'Payment synced successfully.',
                'payment_status' => $transaction->fresh()->payment_status,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Sync Payment Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to finalize transaction, decrease stock, add payment line, and fire accounting events
     */
    public function finalizeAndPayTransaction(Transaction $transaction, $orderId = '')
    {
        // Determine payment account (Kas / Bank) from business location default settings if available
        $accountId = null;
        $location = \App\BusinessLocation::find($transaction->location_id);
        if ($location && !empty($location->default_payment_accounts)) {
            $defaultPaymentAccounts = json_decode($location->default_payment_accounts, true);
            $accountId = $defaultPaymentAccounts['custom_pay_1']['account']
                ?? $defaultPaymentAccounts['card']['account']
                ?? $defaultPaymentAccounts['cash']['account']
                ?? null;
        }

        if ($transaction->status != 'final') {
            // Update transaction status to final & generate invoice number
            $invoiceNo = $this->transactionUtil->getInvoiceNumber($transaction->business_id, 'final', $transaction->location_id);
            $transaction->status = 'final';
            $transaction->invoice_no = $invoiceNo;
            $transaction->transaction_date = \Carbon\Carbon::now()->toDateTimeString();
            $transaction->save();

            // Decrease product stock for stock-managed items
            $productUtil = new \App\Utils\ProductUtil();
            foreach ($transaction->sell_lines as $sell_line) {
                $decrease_qty = $sell_line->quantity;
                if ($sell_line->product && $sell_line->product->enable_stock == 1) {
                    $productUtil->decreaseProductQuantity(
                        $sell_line->product_id,
                        $sell_line->variation_id,
                        $transaction->location_id,
                        $decrease_qty
                    );
                }
            }
        }

        if ($transaction->payment_status != 'paid') {
            // Add payment line
            $payment_data = [
                'amount' => $transaction->final_total,
                'method' => 'midtrans',
                'paid_on' => \Carbon\Carbon::now()->toDateTimeString(),
                'created_by' => $transaction->created_by,
                'account_id' => $accountId,
                'note' => 'Midtrans Order ID: ' . $orderId,
            ];
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, [$payment_data], $transaction->business_id);
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
        }

        // Fire SellCreatedOrModified event to trigger Accounting Module mapping (Revenue, Cash/Bank, COGS)
        if (class_exists('\App\Events\SellCreatedOrModified')) {
            event(new \App\Events\SellCreatedOrModified($transaction));
        }
    }
}
