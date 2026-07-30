<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use DB;

class TrialBalanceReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('fy_start_month')->default(1);
            $table->string('time_zone')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts_transactions');
        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id');
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->dateTime('operation_date');
            $table->timestamps();
        });

        // Insert business
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Test Business',
            'fy_start_month' => 1,
            'time_zone' => 'Asia/Jakarta',
        ]);
    }

    public function testTrialBalanceCalculations()
    {
        // Insert mock accounts
        DB::table('accounting_accounts')->insert([
            ['id' => 1, 'name' => 'Asset Account', 'business_id' => 1, 'account_primary_type' => 'asset'],
            ['id' => 2, 'name' => 'Liability Account', 'business_id' => 1, 'account_primary_type' => 'liability'],
            ['id' => 3, 'name' => 'All Zero Account', 'business_id' => 1, 'account_primary_type' => 'asset'],
        ]);

        // Insert some transactions and matching ledger entries
        // Operation dates:
        // '2023-12-15' (Opening for 2024-01-01)
        // '2024-01-15' (Current period for Jan 2024)
        DB::table('accounting_accounts_transactions')->insert([
            // Opening balance for Asset Account: Debit 1000
            [
                'accounting_account_id' => 1,
                'amount' => 1000,
                'type' => 'debit',
                'operation_date' => '2023-12-15 10:00:00',
            ],
            // Current period for Asset Account: Debit 500, Credit 200
            [
                'accounting_account_id' => 1,
                'amount' => 500,
                'type' => 'debit',
                'operation_date' => '2024-01-15 10:00:00',
            ],
            [
                'accounting_account_id' => 1,
                'amount' => 200,
                'type' => 'credit',
                'operation_date' => '2024-01-20 10:00:00',
            ],

            // Opening balance for Liability Account: Credit 800
            [
                'accounting_account_id' => 2,
                'amount' => 800,
                'type' => 'credit',
                'operation_date' => '2023-12-20 10:00:00',
            ],
            // Current period for Liability Account: Debit 300, Credit 100
            [
                'accounting_account_id' => 2,
                'amount' => 300,
                'type' => 'debit',
                'operation_date' => '2024-01-15 10:00:00',
            ],
            [
                'accounting_account_id' => 2,
                'amount' => 100,
                'type' => 'credit',
                'operation_date' => '2024-01-22 10:00:00',
            ],
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);

        // Put business_id, date_format, and timezone in session
        session([
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
            'business.date_format' => 'Y-m-d',
        ]);

        // Call the trialBalance route/endpoint via ajax with X-Requested-With header
        $response = $this->get('/account/trial-balance?start_date=2024-01-01&end_date=2024-01-31', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('accounts', $data);
        $this->assertArrayHasKey('start_date', $data);
        $this->assertArrayHasKey('end_date', $data);

        $accounts = $data['accounts'];

        // We should have 2 accounts (All Zero Account is skipped)
        $this->assertCount(2, $accounts);

        // Find Asset Account
        $asset = collect($accounts)->firstWhere('name', 'Asset Account');
        $this->assertNotNull($asset);
        $this->assertEquals(1000, $asset['opening_debit']);
        $this->assertEquals(0, $asset['opening_credit']);
        $this->assertEquals(500, $asset['current_debit']);
        $this->assertEquals(200, $asset['current_credit']);
        // Ending balance: 1000 + 500 - 200 = 1300 Debit
        $this->assertEquals(1300, $asset['ending_debit']);
        $this->assertEquals(0, $asset['ending_credit']);

        // Find Liability Account
        $liability = collect($accounts)->firstWhere('name', 'Liability Account');
        $this->assertNotNull($liability);
        $this->assertEquals(0, $liability['opening_debit']);
        $this->assertEquals(800, $liability['opening_credit']);
        $this->assertEquals(300, $liability['current_debit']);
        $this->assertEquals(100, $liability['current_credit']);
        // Ending balance: 800 + 100 - 300 = 600 Credit
        $this->assertEquals(0, $liability['ending_debit']);
        $this->assertEquals(600, $liability['ending_credit']);
    }

    public function testTrialBalanceLocationFilter()
    {
        // Insert accounts
        DB::table('accounting_accounts')->insert([
            ['id' => 1, 'name' => 'Asset Account', 'business_id' => 1, 'account_primary_type' => 'asset'],
        ]);

        // Create transactions for different locations
        DB::table('transactions')->insert([
            ['id' => 10, 'business_id' => 1, 'location_id' => 1, 'type' => 'sell'],
            ['id' => 11, 'business_id' => 1, 'location_id' => 2, 'type' => 'sell'],
        ]);

        // Insert ledger entries mapped to those transactions
        DB::table('accounting_accounts_transactions')->insert([
            // Location 1 transaction
            [
                'accounting_account_id' => 1,
                'transaction_id' => 10,
                'amount' => 500,
                'type' => 'debit',
                'operation_date' => '2024-01-15 10:00:00',
            ],
            // Location 2 transaction
            [
                'accounting_account_id' => 1,
                'transaction_id' => 11,
                'amount' => 900,
                'type' => 'debit',
                'operation_date' => '2024-01-15 10:00:00',
            ],
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);

        session([
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
            'business.date_format' => 'Y-m-d',
        ]);

        // Get Trial Balance filtered by Location 1
        $response1 = $this->get('/account/trial-balance?start_date=2024-01-01&end_date=2024-01-31&location_id=1', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);
        $response1->assertStatus(200);
        $accounts1 = $response1->json('accounts');
        $this->assertCount(1, $accounts1);
        $this->assertEquals(500, $accounts1[0]['current_debit']);

        // Get Trial Balance filtered by Location 2
        $response2 = $this->get('/account/trial-balance?start_date=2024-01-01&end_date=2024-01-31&location_id=2', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);
        $response2->assertStatus(200);
        $accounts2 = $response2->json('accounts');
        $this->assertCount(1, $accounts2);
        $this->assertEquals(900, $accounts2[0]['current_debit']);
    }

    public function testTrialBalanceExpenseAndExpensesCalculations()
    {
        // Insert mock accounts with both 'expense' and 'expenses' as primary types
        DB::table('accounting_accounts')->insert([
            ['id' => 4, 'name' => 'Expense Account Singular', 'business_id' => 1, 'account_primary_type' => 'expense'],
            ['id' => 5, 'name' => 'Expenses Account Plural', 'business_id' => 1, 'account_primary_type' => 'expenses'],
        ]);

        DB::table('accounting_accounts_transactions')->insert([
            // Singular: Opening 500 (debit)
            [
                'accounting_account_id' => 4,
                'amount' => 500,
                'type' => 'debit',
                'operation_date' => '2023-12-15 10:00:00',
            ],
            // Plural: Opening 600 (debit)
            [
                'accounting_account_id' => 5,
                'amount' => 600,
                'type' => 'debit',
                'operation_date' => '2023-12-15 10:00:00',
            ],
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('account.access')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $user->user_type = 'user';
        $user->allow_login = 1;

        $this->actingAs($user);

        session([
            'user.business_id' => 1,
            'business.time_zone' => 'Asia/Jakarta',
            'business.date_format' => 'Y-m-d',
        ]);

        // Get Trial Balance
        $response = $this->get('/account/trial-balance?start_date=2024-01-01&end_date=2024-01-31', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $accounts = $response->json('accounts');

        $singular = collect($accounts)->firstWhere('name', 'Expense Account Singular');
        $this->assertNotNull($singular);
        $this->assertEquals(500, $singular['opening_debit']);
        $this->assertEquals(0, $singular['opening_credit']);

        $plural = collect($accounts)->firstWhere('name', 'Expenses Account Plural');
        $this->assertNotNull($plural);
        $this->assertEquals(600, $plural['opening_debit']);
        $this->assertEquals(0, $plural['opening_credit']);
    }
}
