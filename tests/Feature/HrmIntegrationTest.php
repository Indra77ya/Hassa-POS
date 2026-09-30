<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Business;
use App\BusinessLocation;
use App\Transaction;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Essentials\Events\PayrollCreatedOrModified;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HrmIntegrationTest extends TestCase
{
    /**
     * Test payroll event triggers double-entry accounting transaction mapping.
     */
    public function test_payroll_creates_accounting_journal_entry()
    {
        $business = Business::first();
        if (!$business) {
            $this->assertTrue(true);
            return;
        }

        $user = User::where('business_id', $business->id)->first();
        if (!$user) {
            $this->assertTrue(true);
            return;
        }

        $location = BusinessLocation::where('business_id', $business->id)->first();

        // Create test payroll transaction
        $payroll = Transaction::create([
            'business_id' => $business->id,
            'location_id' => $location?->id,
            'type' => 'payroll',
            'status' => 'final',
            'payment_status' => 'paid',
            'expense_for' => $user->id,
            'final_total' => 5000000,
            'total_before_tax' => 5000000,
            'created_by' => $user->id,
            'transaction_date' => now()->toDateTimeString(),
            'ref_no' => 'PAY-TEST-001',
        ]);

        // Dispatch PayrollCreatedOrModified event
        event(new PayrollCreatedOrModified($payroll));

        // Verify accounting account mapping exists
        $mappings = AccountingAccountsTransaction::where('transaction_id', $payroll->id)->get();
        $this->assertNotEmpty($mappings);
        $this->assertEquals(2, $mappings->count());

        $debit = $mappings->where('type', 'debit')->first();
        $credit = $mappings->where('type', 'credit')->first();

        $this->assertEquals(5000000, $debit->amount);
        $this->assertEquals(5000000, $credit->amount);
    }
}
