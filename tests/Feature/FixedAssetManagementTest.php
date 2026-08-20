<?php

namespace Tests\Feature;

use App\Business;
use App\BusinessLocation;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\Accounting\Entities\AccountingAccount;
use Modules\Accounting\Entities\AccountingAccountsTransaction;
use Modules\Accounting\Entities\AccountingAccTransMapping;
use Modules\AssetManagement\Entities\Asset;
use Modules\AssetManagement\Entities\AssetCategory;
use Modules\AssetManagement\Entities\AssetDepreciationLog;
use Modules\AssetManagement\Entities\AssetSetting;
use Tests\TestCase;

class FixedAssetManagementTest extends TestCase
{
    protected $user;
    protected $business;
    protected $location;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

        $this->app->register(\Modules\Accounting\Providers\AccountingServiceProvider::class);
        $this->app->register(\Modules\AssetManagement\Providers\AssetManagementServiceProvider::class);

        // Register SQLite MySQL IF function
        if (DB::connection()->getDriverName() === 'sqlite') {
            $pdo = DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('IF', function ($condition, $expr1, $expr2) {
                return $condition ? $expr1 : $expr2;
            });
        }

        // Bypass Spatie permissions
        \Illuminate\Support\Facades\Gate::before(function () {
            return true;
        });

        // Set up in-memory database schema for testing
        if (!Schema::hasTable('business')) {
            Schema::create('business', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('currency_id')->default(1);
                $table->date('start_date')->nullable();
                $table->string('time_zone')->default('Asia/Jakarta');
                $table->integer('fy_start_month')->default(1);
                $table->string('accounting_method')->default('fifo');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('account_transactions')) {
            Schema::create('account_transactions', function (Blueprint $table) {
                $table->id();
                $table->integer('account_id')->unsigned();
                $table->integer('transaction_id')->unsigned()->nullable();
                $table->integer('transaction_payment_id')->unsigned()->nullable();
                $table->decimal('amount', 22, 4);
                $table->string('type');
                $table->string('sub_type')->nullable();
                $table->integer('created_by')->unsigned();
                $table->dateTime('operation_date');
                $table->text('note')->nullable();
                $table->integer('accounting_accounts_transaction_id')->unsigned()->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->integer('business_id')->unsigned()->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->integer('role_id')->unsigned();
                $table->string('model_type');
                $table->integer('model_id')->unsigned();
            });
        }

        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->integer('permission_id')->unsigned();
                $table->string('model_type');
                $table->integer('model_id')->unsigned();
            });
        }

        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();
            });
        }

        if (!Schema::hasTable('account_types')) {
            Schema::create('account_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('fixed_key')->nullable();
                $table->integer('parent_account_type_id')->nullable();
                $table->integer('business_id')->unsigned()->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->string('name');
                $table->string('account_number')->nullable();
                $table->integer('account_type_id')->unsigned()->nullable();
                $table->text('note')->nullable();
                $table->boolean('is_closed')->default(0);
                $table->integer('accounting_account_id')->unsigned()->nullable();
                $table->integer('created_by')->unsigned();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_account_types')) {
            Schema::create('accounting_account_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('account_type');
                $table->string('account_primary_type')->nullable();
                $table->boolean('show_balance')->default(1);
                $table->integer('parent_id')->nullable();
                $table->integer('business_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('business_locations')) {
            Schema::create('business_locations', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->string('name');
                $table->string('location_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('surname')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('username')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->integer('business_id')->unsigned()->nullable();
                $table->string('user_type')->default('user');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_accounts')) {
            Schema::create('accounting_accounts', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->string('name');
                $table->string('account_number')->nullable();
                $table->string('gl_code')->nullable();
                $table->string('account_primary_type')->nullable();
                $table->integer('account_sub_type_id')->unsigned()->nullable();
                $table->integer('account_id')->unsigned()->nullable();
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->integer('created_by')->unsigned()->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_acc_trans_mappings')) {
            Schema::create('accounting_acc_trans_mappings', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->string('ref_no')->nullable();
                $table->text('note')->nullable();
                $table->string('type')->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->date('operation_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('accounting_accounts_transactions')) {
            Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
                $table->id();
                $table->integer('accounting_account_id')->unsigned();
                $table->decimal('amount', 22, 4);
                $table->string('type'); // debit, credit
                $table->string('sub_type')->nullable();
                $table->integer('acc_trans_mapping_id')->unsigned()->nullable();
                $table->integer('created_by')->unsigned()->nullable();
                $table->date('operation_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->integer('created_by')->unsigned();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->string('name');
                $table->string('asset_code')->nullable();
                $table->foreignId('asset_category_id')->nullable();
                $table->integer('location_id')->unsigned()->nullable();
                $table->date('purchase_date');
                $table->decimal('purchase_price', 22, 4);
                $table->decimal('salvage_value', 22, 4)->default(0);
                $table->integer('useful_life');
                $table->integer('fixed_asset_account_id')->nullable();
                $table->integer('payment_account_id')->nullable();
                $table->integer('accounting_acc_trans_mapping_id')->nullable();
                $table->string('depreciation_method')->default('straight_line');
                $table->string('status')->default('active');
                $table->text('description')->nullable();
                $table->integer('created_by')->unsigned();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asset_depreciation_logs')) {
            Schema::create('asset_depreciation_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->foreignId('asset_id');
                $table->date('depreciation_date');
                $table->integer('year');
                $table->integer('month');
                $table->decimal('amount', 22, 4);
                $table->integer('accounting_acc_trans_mapping_id')->unsigned()->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('asset_settings')) {
            Schema::create('asset_settings', function (Blueprint $table) {
                $table->id();
                $table->integer('business_id')->unsigned();
                $table->integer('depreciation_expense_account_id')->unsigned()->nullable();
                $table->integer('accumulated_depreciation_account_id')->unsigned()->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reference_counts')) {
            Schema::create('reference_counts', function (Blueprint $table) {
                $table->id();
                $table->string('ref_type');
                $table->integer('ref_count');
                $table->integer('business_id');
                $table->timestamps();
            });
        }

        // Create Business
        $this->business = Business::create([
            'name' => 'Asset Test Business',
            'currency_id' => 1,
            'start_date' => '2026-01-01',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
            'accounting_method' => 'fifo',
        ]);

        // Create Business Location
        $this->location = BusinessLocation::create([
            'business_id' => $this->business->id,
            'name' => 'Main Office',
            'location_id' => 'LOC-01',
        ]);

        // Create User
        $this->user = User::create([
            'surname' => 'Mr',
            'first_name' => 'Asset',
            'last_name' => 'Admin',
            'username' => 'asset_admin',
            'email' => 'admin@assettest.com',
            'password' => bcrypt('123456'),
            'business_id' => $this->business->id,
            'user_type' => 'user',
        ]);

        $this->actingAs($this->user);
        session([
            'user' => ['id' => $this->user->id, 'business_id' => $this->business->id],
            'business' => ['date_format' => 'Y-m-d', 'time_zone' => 'Asia/Jakarta'],
        ]);

        // Auto-seed default accounts and asset settings
        AssetSetting::forBusiness($this->business->id);
    }

    public function test_asset_creation_and_auto_seeding()
    {
        // Check that settings and default accounts were auto-seeded
        $setting = AssetSetting::where('business_id', $this->business->id)->first();
        $this->assertNotNull($setting);
        $this->assertNotNull($setting->depreciation_expense_account_id);
        $this->assertNotNull($setting->accumulated_depreciation_account_id);

        // Check that payment accounts (POS accounts) were synchronized bi-directionally
        $expenseAcc = AccountingAccount::find($setting->depreciation_expense_account_id);
        $this->assertNotNull($expenseAcc);
        $this->assertNotNull($expenseAcc->account_id);
        $this->assertNotNull(\App\Account::find($expenseAcc->account_id));

        $accumAcc = AccountingAccount::find($setting->accumulated_depreciation_account_id);
        $this->assertNotNull($accumAcc);
        $this->assertNotNull($accumAcc->account_id);
        $this->assertNotNull(\App\Account::find($accumAcc->account_id));

        // Create asset category
        $category = AssetCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kendaraan Operasional',
            'code' => 'VEH',
            'created_by' => $this->user->id,
        ]);

        // Create asset
        $asset = Asset::create([
            'business_id' => $this->business->id,
            'name' => 'Mobil Box Operasional',
            'asset_code' => 'AST-001',
            'asset_category_id' => $category->id,
            'location_id' => $this->location->id,
            'purchase_date' => '2026-01-01',
            'purchase_price' => 120000000, // 120 Million
            'salvage_value' => 20000000,   // 20 Million (Depreciable: 100 Million)
            'useful_life' => 50,          // 50 Months -> Monthly: 2,000,000
            'depreciation_method' => 'straight_line',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(2000000, $asset->monthly_depreciation);
        $this->assertEquals(0, $asset->total_accumulated_depreciation);
        $this->assertEquals(120000000, $asset->net_book_value);
    }

    public function test_cron_job_depreciation_and_journal_synchronization()
    {
        // Create asset
        $asset = Asset::create([
            'business_id' => $this->business->id,
            'name' => 'Komputer Kantor',
            'asset_code' => 'AST-002',
            'purchase_date' => '2026-01-01',
            'purchase_price' => 12000000, // 12 Million
            'salvage_value' => 0,          // 0 Salvage Value
            'useful_life' => 12,          // 12 Months -> Monthly: 1,000,000
            'depreciation_method' => 'straight_line',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);

        // Run command directly
        $command = new \Modules\AssetManagement\Console\RunDepreciationCommand();
        $this->app->instance(\Modules\AssetManagement\Console\RunDepreciationCommand::class, $command);
        $this->artisan('asset:run-depreciation', ['--date' => '2026-01-31']);

        // 1. Verify log entry in asset_depreciation_logs
        $log = AssetDepreciationLog::where('asset_id', $asset->id)
            ->where('year', 2026)
            ->where('month', 1)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(1000000, $log->amount);

        // 2. Verify Double-Entry Journal Entry generated
        $mapping = AccountingAccTransMapping::find($log->accounting_acc_trans_mapping_id);
        $this->assertNotNull($mapping);
        $this->assertEquals('journal_entry', $mapping->type);

        $txs = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->get();
        $this->assertCount(2, $txs);

        $setting = AssetSetting::forBusiness($this->business->id);

        $debitTx = $txs->where('type', 'debit')->first();
        $this->assertEquals($setting->depreciation_expense_account_id, $debitTx->accounting_account_id);
        $this->assertEquals(1000000, $debitTx->amount);

        $creditTx = $txs->where('type', 'credit')->first();
        $this->assertEquals($setting->accumulated_depreciation_account_id, $creditTx->accounting_account_id);
        $this->assertEquals(1000000, $creditTx->amount);

        // 3. Test Locking Mechanism (Double Depreciation Prevention)
        $this->artisan('asset:run-depreciation', ['--date' => '2026-01-31']);

        $logsCount = AssetDepreciationLog::where('asset_id', $asset->id)
            ->where('year', 2026)
            ->where('month', 1)
            ->count();
        $this->assertEquals(1, $logsCount, 'Should not generate duplicate depreciation logs for the same month.');

        // 4. Verify asset accumulated depreciation & net book value
        $this->assertEquals(1000000, $asset->total_accumulated_depreciation);
        $this->assertEquals(11000000, $asset->net_book_value);
    }

    public function test_asset_acquisition_auto_journal_lifecycle()
    {
        // 1. Create Fixed Asset Account & Payment Account
        $fixedAssetAccount = AccountingAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Bangunan & Ruko',
            'gl_code' => '1405',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 4, // aktiva_tetap
            'created_by' => $this->user->id,
        ]);

        $bankAccount = AccountingAccount::create([
            'business_id' => $this->business->id,
            'name' => 'Bank BCA',
            'gl_code' => '1102',
            'account_primary_type' => 'asset',
            'account_sub_type_id' => 3, // kas_dan_bank
            'created_by' => $this->user->id,
        ]);

        // 2. HTTP POST Store Asset
        $response = $this->post(action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'store']), [
            'name' => 'Ruko Dua Lantai',
            'asset_code' => 'RUKO-01',
            'purchase_date' => '2026-01-15',
            'purchase_price' => '25000000',
            'useful_life' => 120,
            'fixed_asset_account_id' => $fixedAssetAccount->id,
            'payment_account_id' => $bankAccount->id,
        ]);

        if (session('status') && isset(session('status')['msg']) && session('status')['success'] === false) {
            $this->fail('Asset store failed with error: ' . session('status')['msg']);
        }

        $response->assertRedirect();

        $asset = Asset::where('name', 'Ruko Dua Lantai')->first();
        $this->assertNotNull($asset);
        $this->assertEquals(25000000, $asset->purchase_price);
        $this->assertNotNull($asset->accounting_acc_trans_mapping_id);

        // Verify Journal Entry created
        $mapping = AccountingAccTransMapping::find($asset->accounting_acc_trans_mapping_id);
        $this->assertNotNull($mapping);

        $txs = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->get();
        $this->assertCount(2, $txs);

        $debitTx = $txs->where('type', 'debit')->first();
        $this->assertEquals($fixedAssetAccount->id, $debitTx->accounting_account_id);
        $this->assertEquals(25000000, $debitTx->amount);

        $creditTx = $txs->where('type', 'credit')->first();
        $this->assertEquals($bankAccount->id, $creditTx->accounting_account_id);
        $this->assertEquals(25000000, $creditTx->amount);

        // 3. HTTP PUT Update Asset Amount to 30.000.000
        $updateResponse = $this->put(action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'update'], [$asset->id]), [
            'name' => 'Ruko Dua Lantai (Renovated)',
            'purchase_date' => '2026-01-15',
            'purchase_price' => '30000000',
            'useful_life' => 120,
            'fixed_asset_account_id' => $fixedAssetAccount->id,
            'payment_account_id' => $bankAccount->id,
        ]);

        $updateResponse->assertRedirect();
        $asset->refresh();
        $this->assertEquals(30000000, $asset->purchase_price);

        $txsUpdated = AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->get();
        $this->assertEquals(30000000, $txsUpdated->where('type', 'debit')->first()->amount);

        // 4. Test Manual Per-Asset Depreciation HTTP POST
        $depreciateResponse = $this->post(action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'depreciate'], [$asset->id]));
        $depreciateResponse->assertJson(['success' => true]);

        $this->assertEquals(1, AssetDepreciationLog::where('asset_id', $asset->id)->count());

        // 5. HTTP DELETE Asset
        $deleteResponse = $this->delete(action([\Modules\AssetManagement\Http\Controllers\AssetController::class, 'destroy'], [$asset->id]));
        $deleteResponse->assertJson(['success' => true]);

        $this->assertNull(Asset::find($asset->id));
        $this->assertNull(AccountingAccTransMapping::find($mapping->id));
        $this->assertEquals(0, AccountingAccountsTransaction::where('acc_trans_mapping_id', $mapping->id)->count());
    }
}
