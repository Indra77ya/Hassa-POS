<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Entities\AccountingAccount;
use App\ExpenseCategory;
use App\BusinessLocation;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('accounting_accounts') || !Schema::hasTable('expense_categories')) {
            return;
        }

        // 1. Ensure any Payment Accounts in `accounts` table of type Operating Expense are synced to `AccountingAccount`
        if (Schema::hasTable('accounts') && Schema::hasTable('account_types')) {
            $payment_accounts = \App\Account::whereHas('account_type', function ($q) {
                $q->where('fixed_key', 'beban_operasional');
            })->where('is_closed', 0)->get();

            foreach ($payment_accounts as $p_account) {
                if (empty($p_account->accounting_account_id)) {
                    $acc_account = AccountingAccount::where('business_id', $p_account->business_id)
                        ->where('name', $p_account->name)
                        ->first();

                    if (!$acc_account) {
                        $acc_account = AccountingAccount::create([
                            'name' => $p_account->name,
                            'business_id' => $p_account->business_id,
                            'created_by' => $p_account->created_by ?? 1,
                            'description' => $p_account->note,
                            'gl_code' => $p_account->account_number,
                            'status' => 'active',
                            'account_primary_type' => 'expenses',
                            'account_sub_type_id' => 14,
                            'account_id' => $p_account->id,
                        ]);
                    }

                    if ($acc_account) {
                        $p_account->accounting_account_id = $acc_account->id;
                        $p_account->save();
                    }
                }
            }
        }

        $operating_expense_accounts = AccountingAccount::where('account_sub_type_id', 14)
            ->where('status', 'active')
            ->get();

        foreach ($operating_expense_accounts as $account) {
            $business_id = $account->business_id;

            // Find existing category by name or gl_code/code
            $category = ExpenseCategory::where('business_id', $business_id)
                ->where(function ($q) use ($account) {
                    $q->where('name', $account->name);
                    if (!empty($account->gl_code)) {
                        $q->orWhere('code', $account->gl_code);
                    }
                })->first();

            if ($category) {
                $category->update([
                    'name' => $account->name,
                    'code' => $account->gl_code,
                ]);
            } else {
                $category = ExpenseCategory::create([
                    'name' => $account->name,
                    'business_id' => $business_id,
                    'code' => $account->gl_code,
                ]);
            }

            // Find primary active Cash Account (sub_type_id 3)
            $cash_account = AccountingAccount::where('business_id', $business_id)
                ->where('account_sub_type_id', 3)
                ->where('status', 'active')
                ->orderBy('id', 'asc')
                ->first();

            $cash_account_id = $cash_account ? $cash_account->id : null;

            // Update default map in BusinessLocation
            $locations = BusinessLocation::where('business_id', $business_id)->get();
            foreach ($locations as $loc) {
                $map = json_decode($loc->accounting_default_map, true) ?: [];
                $map['expense_' . $category->id] = [
                    'deposit_to' => $account->id,
                    'payment_account' => $cash_account_id,
                ];
                $loc->update(['accounting_default_map' => json_encode($map)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No destructive rollback needed
    }
};
