<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Account;
use App\AccountType;
use App\User;
use App\Business;
use App\Transaction;
use App\Utils\Util;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;

class ExpensePaymentValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure clean testing schema for custom labels and business configurations
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('custom_labels')->nullable();
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->timestamps();
        });

        // Set up dummy Auth/User
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('permitted_locations')->andReturn('all');
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);
    }

    /**
     * Test that payment_types method correctly filters out undefined custom payment methods.
     */
    public function testPaymentTypesMethodFiltersUndefinedCustomPayments()
    {
        // 1. Create a business with only custom_pay_1 defined
        $custom_labels = [
            'payments' => [
                'custom_pay_1' => 'LinkAja',
                'custom_pay_2' => '', // undefined
                'custom_pay_3' => null // undefined
            ]
        ];

        $business = Business::create([
            'id' => 1,
            'name' => 'Test Business',
            'custom_labels' => json_encode($custom_labels)
        ]);

        $util = new Util();
        $payment_types = $util->payment_types(null, false, 1);

        // Assert standard ones are there
        $this->assertArrayHasKey('cash', $payment_types);
        $this->assertArrayHasKey('bank_transfer', $payment_types);

        // Assert custom_pay_1 is there
        $this->assertArrayHasKey('custom_pay_1', $payment_types);
        $this->assertEquals('LinkAja', $payment_types['custom_pay_1']);

        // Assert undefined custom payments are filtered out
        $this->assertArrayNotHasKey('custom_pay_2', $payment_types);
        $this->assertArrayNotHasKey('custom_pay_3', $payment_types);
        $this->assertArrayNotHasKey('custom_pay_4', $payment_types);
    }
}
