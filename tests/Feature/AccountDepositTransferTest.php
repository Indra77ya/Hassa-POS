<?php

namespace Tests\Feature;

use App\Account;
use App\AccountTransaction;
use App\Business;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountDepositTransferTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $business;
    protected $kasAccount;
    protected $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::firstOrCreate(
            ['name' => 'Test Business'],
            ['currency_id' => 1, 'start_date' => '2020-01-01', 'time_zone' => 'Asia/Jakarta']
        );

        $this->user = User::factory()->create([
            'business_id' => $this->business->id,
            'surname' => 'Test',
            'first_name' => 'User',
            'email' => 'testuser_' . uniqid() . '@example.com',
        ]);

        $this->kasAccount = Account::create([
            'business_id' => $this->business->id,
            'name' => 'Kas',
            'account_number' => '101',
            'created_by' => $this->user->id,
        ]);

        $this->bankAccount = Account::create([
            'business_id' => $this->business->id,
            'name' => 'Bank',
            'account_number' => '102',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_cannot_deposit_to_same_account()
    {
        $response = $this->post(action([\App\Http\Controllers\AccountController::class, 'postDeposit']), [
            'account_id' => $this->kasAccount->id,
            'from_account' => $this->kasAccount->id,
            'amount' => '100000',
            'operation_date' => date('Y-m-d H:i:s'),
        ]);

        $response->assertJson([
            'success' => false,
            'msg' => __('account.cannot_transfer_to_same_account'),
        ]);
    }

    public function test_cannot_transfer_to_same_account()
    {
        $response = $this->post(action([\App\Http\Controllers\AccountController::class, 'postFundTransfer']), [
            'from_account' => $this->kasAccount->id,
            'to_account' => $this->kasAccount->id,
            'amount' => '100000',
            'operation_date' => date('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(action([\App\Http\Controllers\AccountController::class, 'index']));
        $response->assertSessionHas('status', [
            'success' => false,
            'msg' => __('account.cannot_transfer_to_same_account'),
        ]);
    }

    public function test_destroy_account_transaction_deletes_both_paired_transactions()
    {
        // Create valid deposit from Bank to Kas
        $depositData = [
            'amount' => 2015000,
            'account_id' => $this->kasAccount->id,
            'type' => 'debit',
            'sub_type' => 'deposit',
            'operation_date' => date('Y-m-d H:i:s'),
            'created_by' => $this->user->id,
        ];
        $deposit = AccountTransaction::createAccountTransaction($depositData);

        $sourceData = $depositData;
        $sourceData['type'] = 'credit';
        $sourceData['account_id'] = $this->bankAccount->id;
        $sourceData['transfer_transaction_id'] = $deposit->id;
        $source = AccountTransaction::createAccountTransaction($sourceData);

        $deposit->transfer_transaction_id = $source->id;
        $deposit->save();

        $this->assertDatabaseHas('account_transactions', ['id' => $deposit->id]);
        $this->assertDatabaseHas('account_transactions', ['id' => $source->id]);

        // Give delete_account_transaction permission
        \Permission::firstOrCreate(['name' => 'delete_account_transaction', 'guard_name' => 'web']);
        $this->user->givePermissionTo('delete_account_transaction');

        $response = $this->get(action([\App\Http\Controllers\AccountController::class, 'destroyAccountTransaction'], [$deposit->id]), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'success' => true,
            'msg' => __('lang_v1.deleted_success'),
        ]);

        $this->assertSoftDeleted('account_transactions', ['id' => $deposit->id]);
        $this->assertSoftDeleted('account_transactions', ['id' => $source->id]);
    }
}
