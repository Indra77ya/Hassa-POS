<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Account;
use App\AccountType;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;

class PaymentAccountFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create minimal schema for testing
        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id');
            $table->string('fixed_key')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('created_by')->nullable();
            $table->string('note')->nullable();
            $table->string('account_number')->nullable();
            $table->integer('account_type_id')->nullable();
            $table->tinyInteger('is_closed')->default(0);
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->softDeletes();
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
     * Test that Account::forDropdown properly filters based on only_cash_bank flag.
     */
    public function testPaymentAccountFilteringByCashAndBankOnly()
    {
        $business_id = 1;

        // 1. Create POS Account Types
        $cash_bank_type = AccountType::create([
            'name' => 'Cash & Bank',
            'business_id' => $business_id,
            'fixed_key' => 'kas_dan_bank'
        ]);

        $liability_type = AccountType::create([
            'name' => 'Liability',
            'business_id' => $business_id,
            'fixed_key' => 'hutang_usaha'
        ]);

        $expense_type = AccountType::create([
            'name' => 'Expenses',
            'business_id' => $business_id,
            'fixed_key' => 'beban_operasional'
        ]);

        // 2. Create Accounts
        $acc_kas = Account::create([
            'name' => 'Kas',
            'business_id' => $business_id,
            'account_type_id' => $cash_bank_type->id
        ]);

        $acc_bank = Account::create([
            'name' => 'Bank',
            'business_id' => $business_id,
            'account_type_id' => $cash_bank_type->id
        ]);

        $acc_hutang = Account::create([
            'name' => 'Hutang Usaha',
            'business_id' => $business_id,
            'account_type_id' => $liability_type->id
        ]);

        $acc_beban = Account::create([
            'name' => 'Beban Gaji',
            'business_id' => $business_id,
            'account_type_id' => $expense_type->id
        ]);

        // 3. Test with $only_cash_bank = true
        $filtered_dropdown = Account::forDropdown($business_id, false, false, false, true);

        // Assert only Kas and Bank are present
        $this->assertArrayHasKey($acc_kas->id, $filtered_dropdown);
        $this->assertArrayHasKey($acc_bank->id, $filtered_dropdown);
        $this->assertArrayNotHasKey($acc_hutang->id, $filtered_dropdown);
        $this->assertArrayNotHasKey($acc_beban->id, $filtered_dropdown);

        // 4. Test with $only_cash_bank = false
        $unfiltered_dropdown = Account::forDropdown($business_id, false, false, false, false);

        // Assert all accounts are present
        $this->assertArrayHasKey($acc_kas->id, $unfiltered_dropdown);
        $this->assertArrayHasKey($acc_bank->id, $unfiltered_dropdown);
        $this->assertArrayHasKey($acc_hutang->id, $unfiltered_dropdown);
        $this->assertArrayHasKey($acc_beban->id, $unfiltered_dropdown);
    }
}
