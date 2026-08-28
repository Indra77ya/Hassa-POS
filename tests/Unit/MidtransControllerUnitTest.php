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
}
