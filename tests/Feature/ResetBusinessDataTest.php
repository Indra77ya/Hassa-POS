<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Business;
use App\Contact;
use App\TransactionPayment;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Modules\Superadmin\Http\Controllers\BusinessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use DB;

class ResetBusinessDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Bypass Spatie permission DB queries
        Gate::before(function () {
            return true;
        });

        // Set up the business table
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        // Set up the contacts table
        Schema::dropIfExists('contacts');
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_default')->default(0);
            $table->string('supplier_business_name')->nullable();
            $table->timestamps();
        });

        // Set up transaction_payments table
        Schema::dropIfExists('transaction_payments');
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->nullable();
            $table->integer('business_id')->nullable();
            $table->decimal('amount', 22, 4)->default(0);
            $table->boolean('is_advance')->default(0);
            $table->integer('payment_for')->nullable();
            $table->string('method')->nullable();
            $table->timestamps();
        });

        // Transactions
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        // Transaction sell lines
        Schema::dropIfExists('transaction_sell_lines');
        Schema::create('transaction_sell_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id');
            $table->integer('product_id')->nullable();
            $table->timestamps();
        });

        // Transaction sell lines purchase lines
        Schema::dropIfExists('transaction_sell_lines_purchase_lines');
        Schema::create('transaction_sell_lines_purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sell_line_id')->nullable();
            $table->integer('purchase_line_id')->nullable();
            $table->timestamps();
        });

        // Purchase lines
        Schema::dropIfExists('purchase_lines');
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        // Account transactions
        Schema::dropIfExists('account_transactions');
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('account_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->timestamps();
        });

        // Bookings
        Schema::dropIfExists('bookings');
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Stock adjustments lines
        Schema::dropIfExists('stock_adjustment_lines');
        Schema::create('stock_adjustment_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transaction_id')->nullable();
            $table->timestamps();
        });

        // Laundry tables
        Schema::dropIfExists('laundry_order_process_logs');
        Schema::dropIfExists('laundry_order_sheets');
        Schema::dropIfExists('laundry_item_types');
        Schema::dropIfExists('laundry_service_types');
        Schema::dropIfExists('laundry_processes');
        Schema::dropIfExists('laundry_statuses');

        Schema::create('laundry_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('laundry_processes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('laundry_service_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('laundry_item_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('laundry_order_sheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('order_no')->nullable();
            $table->integer('contact_id')->nullable();
            $table->timestamps();
        });

        Schema::create('laundry_order_process_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_sheet_id');
            $table->unsignedBigInteger('laundry_process_id')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasColumn('transactions', 'laundry_order_sheet_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('laundry_order_sheet_id')->nullable();
            });
        }

        // Accounts
        Schema::dropIfExists('accounts');
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Account types
        Schema::dropIfExists('account_types');
        Schema::create('account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Products
        Schema::dropIfExists('products');
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Variations
        Schema::dropIfExists('variations');
        Schema::create('variations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->nullable();
            $table->timestamps();
        });

        // Variation location details
        Schema::dropIfExists('variation_location_details');
        Schema::create('variation_location_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->nullable();
            $table->decimal('qty_available', 22, 4)->default(0);
            $table->timestamps();
        });

        // Business locations
        Schema::dropIfExists('business_locations');
        Schema::create('business_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('accounting_default_map')->nullable();
            $table->timestamps();
        });

        // Categories
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        // Expense categories
        Schema::dropIfExists('expense_categories');
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        // Accounting accounts
        Schema::dropIfExists('accounting_accounts');
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->integer('business_id');
            $table->timestamps();
        });

        // Accounting accounts transactions
        Schema::dropIfExists('accounting_accounts_transactions');
        Schema::create('accounting_accounts_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('accounting_account_id')->nullable();
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->timestamps();
        });

        // Accounting budgets
        Schema::dropIfExists('accounting_budgets');
        Schema::create('accounting_budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('accounting_account_id');
            $table->timestamps();
        });

        // Accounting acc trans mappings
        Schema::dropIfExists('accounting_acc_trans_mappings');
        Schema::create('accounting_acc_trans_mappings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Accounting account types
        Schema::dropIfExists('accounting_account_types');
        Schema::create('accounting_account_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->timestamps();
        });

        // Create a test business
        Business::create([
            'id' => 1,
            'name' => 'Reset Test Business',
        ]);
    }

    /**
     * Test that resetting sales deletes only customer advance payments.
     */
    public function testResetSalesDeletesCustomerAdvancePayments()
    {
        // Create a Customer Contact
        $customer = Contact::create([
            'business_id' => 1,
            'name' => 'Customer A',
            'type' => 'customer',
        ]);

        // Create a Supplier Contact
        $supplier = Contact::create([
            'business_id' => 1,
            'name' => 'Supplier B',
            'type' => 'supplier',
        ]);

        // Create customer advance payment
        $cust_payment = TransactionPayment::create([
            'business_id' => 1,
            'amount' => 50000,
            'is_advance' => 1,
            'payment_for' => $customer->id,
        ]);

        // Create supplier advance payment
        $supp_payment = TransactionPayment::create([
            'business_id' => 1,
            'amount' => 75000,
            'is_advance' => 1,
            'payment_for' => $supplier->id,
        ]);

        // Create standard payment
        $std_payment = TransactionPayment::create([
            'business_id' => 1,
            'amount' => 10000,
            'is_advance' => 0,
            'transaction_id' => 999,
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);

        // Call postResetData for sales
        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $request = new Request();
        $request->merge([
            'reset_transactions' => ['sales']
        ]);

        $response = $controller->postResetData($request, 1);
        $result = $response->getData(true);

        $this->assertTrue($result['success']);

        // Assert customer advance payment is deleted
        $this->assertNull(TransactionPayment::find($cust_payment->id));

        // Assert supplier advance payment still exists
        $this->assertNotNull(TransactionPayment::find($supp_payment->id));

        // Assert standard payment still exists
        $this->assertNotNull(TransactionPayment::find($std_payment->id));
    }

    /**
     * Test that resetting purchases deletes only supplier advance payments.
     */
    public function testResetPurchasesDeletesSupplierAdvancePayments()
    {
        // Create a Customer Contact
        $customer = Contact::create([
            'business_id' => 1,
            'name' => 'Customer A',
            'type' => 'customer',
        ]);

        // Create a Supplier Contact
        $supplier = Contact::create([
            'business_id' => 1,
            'name' => 'Supplier B',
            'type' => 'supplier',
        ]);

        // Create customer advance payment
        $cust_payment = TransactionPayment::create([
            'business_id' => 1,
            'amount' => 50000,
            'is_advance' => 1,
            'payment_for' => $customer->id,
        ]);

        // Create supplier advance payment
        $supp_payment = TransactionPayment::create([
            'business_id' => 1,
            'amount' => 75000,
            'is_advance' => 1,
            'payment_for' => $supplier->id,
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);

        // Call postResetData for purchases
        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $request = new Request();
        $request->merge([
            'reset_transactions' => ['purchases']
        ]);

        $response = $controller->postResetData($request, 1);
        $result = $response->getData(true);

        $this->assertTrue($result['success']);

        // Assert customer advance payment still exists
        $this->assertNotNull(TransactionPayment::find($cust_payment->id));

        // Assert supplier advance payment is deleted
        $this->assertNull(TransactionPayment::find($supp_payment->id));
    }

    /**
     * Test that resetting finance deletes all advance payments.
     */
    public function testResetFinanceDeletesAllAdvancePayments()
    {
        // Create a Customer Contact
        $customer = Contact::create([
            'business_id' => 1,
            'name' => 'Customer A',
            'type' => 'customer',
        ]);

        // Create customer advance payment
        $cust_payment = TransactionPayment::create([
            'business_id' => 1,
            'amount' => 50000,
            'is_advance' => 1,
            'payment_for' => $customer->id,
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);

        // Call postResetData for finance
        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $request = new Request();
        $request->merge([
            'reset_transactions' => ['finance']
        ]);

        $response = $controller->postResetData($request, 1);
        $result = $response->getData(true);

        $this->assertTrue($result['success']);

        // Assert customer advance payment is deleted
        $this->assertNull(TransactionPayment::find($cust_payment->id));
    }

    /**
     * Test that resetting master categories deletes both product categories and expense categories.
     */
    public function testResetCategoriesDeletesProductAndExpenseCategories()
    {
        // Create a product category
        $prod_cat = DB::table('categories')->insertGetId([
            'business_id' => 1,
            'name' => 'Prod Cat A'
        ]);

        // Create an expense category
        $exp_cat = DB::table('expense_categories')->insertGetId([
            'business_id' => 1,
            'name' => 'Exp Cat B'
        ]);

        // Mock login
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);

        // Call postResetData for categories master data
        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $request = new Request();
        $request->merge([
            'reset_master' => ['categories']
        ]);

        $response = $controller->postResetData($request, 1);
        $result = $response->getData(true);

        $this->assertTrue($result['success']);

        // Assert both categories are deleted
        $this->assertNull(DB::table('categories')->find($prod_cat));
        $this->assertNull(DB::table('expense_categories')->find($exp_cat));
    }

    /**
     * Test that resetting Laundry module deletes order sheets, logs, item types, service types, processes, statuses and unlinks transactions.
     */
    public function testResetLaundryDeletesAllLaundryDataAndUnlinksTransactions()
    {
        // Seed Laundry master data
        $status_id = DB::table('laundry_statuses')->insertGetId([
            'business_id' => 1,
            'name' => 'Diproses'
        ]);
        $process_id = DB::table('laundry_processes')->insertGetId([
            'business_id' => 1,
            'name' => 'Cuci'
        ]);
        $service_id = DB::table('laundry_service_types')->insertGetId([
            'business_id' => 1,
            'name' => 'Reguler'
        ]);
        $item_id = DB::table('laundry_item_types')->insertGetId([
            'business_id' => 1,
            'name' => 'Kiloan'
        ]);

        // Seed Laundry order sheet
        $sheet_id = DB::table('laundry_order_sheets')->insertGetId([
            'business_id' => 1,
            'location_id' => 1,
            'order_no' => 'LND-0001',
            'contact_id' => 1
        ]);

        // Seed Laundry process log
        $log_id = DB::table('laundry_order_process_logs')->insertGetId([
            'order_sheet_id' => $sheet_id,
            'laundry_process_id' => $process_id
        ]);

        // Seed Transaction linked to laundry sheet
        $tx_id = DB::table('transactions')->insertGetId([
            'business_id' => 1,
            'location_id' => 1,
            'type' => 'sell',
            'laundry_order_sheet_id' => $sheet_id
        ]);

        // Seed data for another business (should NOT be deleted)
        $sheet_other = DB::table('laundry_order_sheets')->insertGetId([
            'business_id' => 2,
            'location_id' => 1,
            'order_no' => 'LND-0002',
            'contact_id' => 2
        ]);
        $status_other = DB::table('laundry_statuses')->insertGetId([
            'business_id' => 2,
            'name' => 'Selesai'
        ]);

        // Mock login as superadmin
        $user = \Mockery::mock(\App\User::class)->makePartial();
        $user->shouldReceive('can')->with('superadmin')->andReturn(true);
        $user->id = 1;
        $user->business_id = 1;
        $this->actingAs($user);

        // Call postResetData for laundry module
        $controller = new BusinessController(app(BusinessUtil::class), app(ModuleUtil::class));
        $request = new Request();
        $request->merge([
            'reset_modules' => ['laundry']
        ]);

        $response = $controller->postResetData($request, 1);
        $result = $response->getData(true);

        $this->assertTrue($result['success']);

        // Assert business 1 laundry data deleted
        $this->assertNull(DB::table('laundry_order_process_logs')->find($log_id));
        $this->assertNull(DB::table('laundry_order_sheets')->find($sheet_id));
        $this->assertNull(DB::table('laundry_item_types')->find($item_id));
        $this->assertNull(DB::table('laundry_service_types')->find($service_id));
        $this->assertNull(DB::table('laundry_processes')->find($process_id));
        $this->assertNull(DB::table('laundry_statuses')->find($status_id));

        // Assert transaction's laundry_order_sheet_id is set to null
        $tx = DB::table('transactions')->find($tx_id);
        $this->assertNotNull($tx);
        $this->assertNull($tx->laundry_order_sheet_id);

        // Assert business 2 data preserved
        $this->assertNotNull(DB::table('laundry_order_sheets')->find($sheet_other));
        $this->assertNotNull(DB::table('laundry_statuses')->find($status_other));
    }
}
