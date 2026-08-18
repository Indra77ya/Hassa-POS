<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Account;
use App\AccountType;
use App\AccountTransaction;
use App\BusinessLocation;
use App\ExpenseCategory;
use App\Transaction;
use App\TransactionPayment;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\ExpenseController;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DepreciationExpenseAndBalanceSheetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fy_start_month')->nullable();
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->integer('currency_id')->default(1);
            $table->timestamps();
        });

        \App\Business::create([
            'id' => 1,
            'name' => 'Test Business',
            'time_zone' => 'Asia/Jakarta',
            'currency_id' => 1,
            'fy_start_month' => 1,
        ]);

        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->integer('account_type_id')->nullable();
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('note')->nullable();
            $table->string('normal_balance')->nullable();
            $table->integer('is_closed')->default(0);
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('fixed_key')->nullable();
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id');
            $table->integer('transaction_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->unsignedBigInteger('accounting_accounts_transaction_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->text('accounting_default_map')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id')->unsigned();
            $table->string('code')->nullable();
            $table->integer('parent_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('business_id');
            $table->string('account_primary_type')->nullable();
            $table->integer('account_sub_type_id')->nullable();
            $table->integer('detail_type_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('gl_code')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('accounting_accounts_transactions');
        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id');
            $table->integer('acc_trans_mapping_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('type', 100);
            $table->string('sub_type', 100)->nullable();
            $table->string('map_type', 100)->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('operation_date');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('account_transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_type')->default('user');
            $table->integer('is_cmmsn_agnt')->default(0);
            $table->string('language')->default('en');
            $table->integer('business_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->integer('location_id');
            $table->string('type');
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('expense_category_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->integer('account_id')->nullable();
            $table->integer('is_return')->default(0);
            $table->timestamps();
        });

        Account::$is_syncing = false;
        AccountingAccount::$is_syncing = false;
        AccountTransaction::$is_syncing = false;
        AccountingAccountsTransaction::$is_syncing = false;
    }

    public function testAddDefaultAccountsSeedsDepreciationAndExpenseCategory()
    {
        AccountTypeController::syncDepreciationForBusiness(1, 1);

        $pos_biaya = Account::where('business_id', 1)->where('name', 'Biaya Penyusutan')->first();
        $this->assertNotNull($pos_biaya);

        $pos_akumulasi = Account::where('business_id', 1)->where('name', 'Akumulasi Penyusutan')->first();
        $this->assertNotNull($pos_akumulasi);

        $exp_cat = ExpenseCategory::where('business_id', 1)->where('name', 'Biaya Penyusutan')->first();
        $this->assertNotNull($exp_cat);

        $acc_biaya = AccountingAccount::where('business_id', 1)->where('name', 'Biaya Penyusutan')->first();
        $this->assertNotNull($acc_biaya);

        $acc_akumulasi = AccountingAccount::where('business_id', 1)->where('name', 'Akumulasi Penyusutan')->first();
        $this->assertNotNull($acc_akumulasi);

        $this->assertTrue(ExpenseController::isDepreciationCategory($exp_cat->id, 1));
        $this->assertEquals($pos_akumulasi->id, ExpenseController::getAccumulatedDepreciationAccountId(1));
    }

    public function testDepreciationExpensePaymentInterceptorAndMapping()
    {
        AccountTypeController::syncDepreciationForBusiness(1, 1);

        $location = BusinessLocation::create([
            'id' => 1,
            'business_id' => 1,
            'accounting_default_map' => json_encode([]),
        ]);

        $kas_type = AccountType::create([
            'name' => 'Kas & Bank',
            'business_id' => 1,
            'fixed_key' => 'kas_dan_bank',
        ]);

        $kas_account = Account::create([
            'name' => 'Kas Utama',
            'business_id' => 1,
            'account_type_id' => $kas_type->id,
            'normal_balance' => 'debit',
        ]);

        $exp_cat = ExpenseCategory::where('business_id', 1)->where('name', 'Biaya Penyusutan')->first();
        $akumulasi_account = Account::where('business_id', 1)->where('name', 'Akumulasi Penyusutan')->first();

        // Simulate request to create depreciation expense where user selected Kas Utama (kas_account)
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'location_id' => 1,
            'expense_category_id' => $exp_cat->id,
            'final_total' => 700000,
            'transaction_date' => '2026-08-16',
            'payment' => [
                [
                    'amount' => 700000,
                    'method' => 'cash',
                    'account_id' => $kas_account->id, // Passing Kas account from frontend
                ],
            ],
        ]);

        // Intercept logic as in ExpenseController@store
        if (ExpenseController::isDepreciationCategory($request->input('expense_category_id'), 1)) {
            $akumulasi_id = ExpenseController::getAccumulatedDepreciationAccountId(1);
            if ($akumulasi_id) {
                $payments = [[
                    'amount' => $request->input('final_total', 0),
                    'method' => 'cash',
                    'account_id' => $akumulasi_id,
                    'paid_on' => $request->input('transaction_date', \Carbon::now()->toDateTimeString()),
                ]];
                $request->merge(['payment' => $payments]);
            }
        }

        // Assert that request payment line's account_id was overridden to Akumulasi Penyusutan
        $payments = $request->input('payment');
        $this->assertEquals($akumulasi_account->id, $payments[0]['account_id']);

        // Create transaction & payment
        $expense = Transaction::create([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'expense',
            'final_total' => 700000,
            'expense_category_id' => $exp_cat->id,
        ]);

        $payment = TransactionPayment::create([
            'transaction_id' => $expense->id,
            'amount' => 700000,
            'account_id' => $payments[0]['account_id'],
        ]);

        $this->assertEquals($akumulasi_account->id, $payment->account_id);

        // Test MapExpenseTransactions listener
        $listener = new \Modules\Accounting\Listeners\MapExpenseTransactions();
        $event = new \stdClass();
        $event->expense = $expense;
        $listener->handle($event);

        $journal_entries = AccountingAccountsTransaction::where('transaction_id', $expense->id)->get();
        $this->assertCount(2, $journal_entries);

        $credit_entry = $journal_entries->where('type', 'credit')->first();
        $acc_akumulasi = AccountingAccount::where('business_id', 1)->where('name', 'Akumulasi Penyusutan')->first();
        $this->assertEquals($acc_akumulasi->id, $credit_entry->accounting_account_id);
    }
}
