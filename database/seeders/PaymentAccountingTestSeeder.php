<?php

namespace Database\Seeders;

use App\Account;
use App\AccountTransaction;
use App\Business;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentAccountingTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info("Seeding Payment Accounts and Accounting Accounts for testing...");

        // Ensure we have at least one business to seed for
        $businesses = Business::all();
        if ($businesses->isEmpty()) {
            $this->command->warn("No businesses found! Creating a default test business...");
            $business = Business::create([
                'name' => 'Test Business',
                'currency_id' => 1,
                'start_date' => Carbon::today(),
                'time_zone' => 'Asia/Jakarta',
            ]);
            $businesses = collect([$business]);
        }

        foreach ($businesses as $business) {
            $this->command->info("Seeding data for Business: {$business->name} (ID: {$business->id})...");

            // Enable real-time model listeners syncing
            Account::$is_syncing = false;
            if (class_exists(AccountingAccount::class)) {
                AccountingAccount::$is_syncing = false;
            }
            AccountTransaction::$is_syncing = false;
            if (class_exists(AccountingAccountsTransaction::class)) {
                AccountingAccountsTransaction::$is_syncing = false;
            }

            // 1. Create a Payment Account "Kas Tunai Toko" via POS core
            // This should automatically trigger creation of the synced AccountingAccount via model hooks
            $account1 = Account::create([
                'name' => 'Kas Tunai Toko (Seeded)',
                'business_id' => $business->id,
                'created_by' => 1,
                'note' => 'Kas tunai harian toko',
                'account_number' => '101001',
                'is_closed' => 0,
            ]);

            // 2. Create another Payment Account "Bank Mandiri - Bisnis"
            $account2 = Account::create([
                'name' => 'Bank Mandiri - Bisnis (Seeded)',
                'business_id' => $business->id,
                'created_by' => 1,
                'note' => 'Rekening utama bisnis Mandiri',
                'account_number' => '101002',
                'is_closed' => 0,
            ]);

            // 3. Create an Accounting Account directly in Accounting module (Cash/Bank sub-type)
            // This should automatically trigger creation of the synced POS Payment Account via model hooks
            if (class_exists(AccountingAccount::class)) {
                $accounting_account = AccountingAccount::create([
                    'name' => 'Bank BCA - Operasional (Seeded)',
                    'business_id' => $business->id,
                    'created_by' => 1,
                    'description' => 'Rekening operasional bank BCA',
                    'gl_code' => '101003',
                    'status' => 'active',
                    'account_primary_type' => 'asset',
                    'account_sub_type_id' => 3, // Cash and cash equivalents
                ]);
            }

            // 4. Seed transaction entries and verify they sync in real-time
            // Create transaction on $account1
            $tx1 = AccountTransaction::create([
                'account_id' => $account1->id,
                'type' => 'debit',
                'amount' => 500000.00,
                'sub_type' => 'deposit',
                'operation_date' => Carbon::now(),
                'note' => 'Setor tunai awal (Seeded)',
                'created_by' => 1,
            ]);

            // Create transaction on $account2
            $tx2 = AccountTransaction::create([
                'account_id' => $account2->id,
                'type' => 'debit',
                'amount' => 15000000.00,
                'sub_type' => 'opening_balance',
                'operation_date' => Carbon::now(),
                'note' => 'Saldo awal bank Mandiri (Seeded)',
                'created_by' => 1,
            ]);

            $this->command->info("Successfully seeded and synchronized accounts & transactions for Business ID {$business->id}!");
        }

        $this->command->info("All testing data for Payment Accounts and Accounting modules seeded successfully!");
    }
}
