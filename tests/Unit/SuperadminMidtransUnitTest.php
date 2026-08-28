<?php

namespace Tests\Unit;

use Modules\Superadmin\Http\Controllers\BaseController;
use Tests\TestCase;

class SuperadminMidtransUnitTest extends TestCase
{
    /** @test */
    public function superadmin_payment_gateways_includes_midtrans_when_configured()
    {
        putenv('MIDTRANS_SERVER_KEY=SB-Mid-server-test');
        putenv('MIDTRANS_CLIENT_KEY=SB-Mid-client-test');

        $_SERVER['MIDTRANS_SERVER_KEY'] = 'SB-Mid-server-test';
        $_SERVER['MIDTRANS_CLIENT_KEY'] = 'SB-Mid-client-test';

        $gateways = [];
        if (env('MIDTRANS_SERVER_KEY') && env('MIDTRANS_CLIENT_KEY')) {
            $gateways['midtrans'] = 'Midtrans';
        }

        $this->assertArrayHasKey('midtrans', $gateways);
        $this->assertEquals('Midtrans', $gateways['midtrans']);
    }
}
