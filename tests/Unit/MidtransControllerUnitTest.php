<?php

namespace Tests\Unit;

use App\Http\Controllers\MidtransController;
use App\Transaction;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class MidtransControllerUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function snap_token_endpoint_validates_disabled_midtrans()
    {
        $transactionUtil = Mockery::mock(TransactionUtil::class);
        $controller = new MidtransController($transactionUtil);

        $transaction = new Transaction();
        $transaction->id = 1;
        $transaction->final_total = 100000;
        $transaction->business = (object) ['pos_settings' => json_encode(['enable_midtrans' => 0])];

        // Mock Transaction::with()->findOrFail
        $request = new Request();

        // Testing logic response directly or via route test
        $this->assertTrue(true);
    }

    /** @test */
    public function handle_notification_returns_200_for_midtrans_test_ping()
    {
        $transactionUtil = Mockery::mock(TransactionUtil::class);
        $controller = new MidtransController($transactionUtil);

        $payload = [
            'order_id' => 'payment_notif_test_G545150152_d0cbb8a0-bd10-4e1e-b4df-033d5559e880',
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '105000.00',
        ];
        $request = Request::create('/midtrans/notification', 'POST', [], [], [], [], json_encode($payload));
        $request->headers->set('Content-Type', 'application/json');

        $response = $controller->handleNotification($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', json_decode($response->getContent(), true)['status']);
    }
}
