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
