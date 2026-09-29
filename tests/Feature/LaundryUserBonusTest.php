<?php

namespace Tests\Feature;

use App\Business;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Laundry\Entities\LaundryOrderProcessLog;
use Modules\Laundry\Entities\LaundryOrderSheet;
use Modules\Laundry\Entities\LaundryProcess;
use Tests\TestCase;

class LaundryUserBonusTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::dropIfExists('activity_log');
        \Illuminate\Support\Facades\Schema::dropIfExists('users');
        \Illuminate\Support\Facades\Schema::dropIfExists('business');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_sheets');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_processes');
        \Illuminate\Support\Facades\Schema::dropIfExists('laundry_order_process_logs');
        \Illuminate\Support\Facades\Schema::dropIfExists('permissions');
        \Illuminate\Support\Facades\Schema::dropIfExists('roles');
        \Illuminate\Support\Facades\Schema::dropIfExists('currencies');

        \Illuminate\Support\Facades\Schema::create('permissions', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->integer('business_id')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('model_has_roles', function ($table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        \Illuminate\Support\Facades\Schema::create('model_has_permissions', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
        });

        \Illuminate\Support\Facades\Schema::create('role_has_permissions', function ($table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });

        \Illuminate\Support\Facades\Schema::create('currencies', function ($table) {
            $table->id();
            $table->string('country')->default('Indonesia');
            $table->string('currency')->default('Rupiah');
            $table->string('code')->default('IDR');
            $table->string('symbol')->default('Rp');
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('business', function ($table) {
            $table->id();
            $table->string('name')->default('Test Business');
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->string('accounting_method')->default('fifo');
            $table->text('keyboard_shortcuts')->nullable();
            $table->text('pos_settings')->nullable();
            $table->text('laundry_settings')->nullable();
            $table->decimal('default_sales_discount', 5, 2)->nullable();
            $table->integer('default_sales_tax')->nullable();
            $table->integer('currency_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('user_type')->default('user');
            $table->decimal('essentials_salary', 22, 4)->nullable();
            $table->string('essentials_pay_period')->nullable();
            $table->decimal('laundry_bonus_per_point', 22, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_processes', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name');
            $table->decimal('points', 8, 2)->default(0);
            $table->integer('sort_order')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_order_sheets', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('order_no');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('laundry_item_type_id')->nullable();
            $table->decimal('quantity', 15, 2)->default(1.00);
            $table->string('unit_name')->nullable();
            $table->decimal('total_amount', 22, 4)->default(0);
            $table->dateTime('received_at')->nullable();
            $table->integer('created_by')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('laundry_order_process_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_sheet_id');
            $table->unsignedBigInteger('laundry_process_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('points_earned', 8, 2)->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transactions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('location_id')->nullable();
            $table->string('type')->default('sell');
            $table->string('status')->default('final');
            $table->string('payment_status')->default('due');
            $table->integer('contact_id')->nullable();
            $table->integer('expense_for')->nullable();
            $table->integer('commission_agent')->nullable();
            $table->unsignedBigInteger('laundry_order_sheet_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('shipping_charges', 22, 4)->default(0);
            $table->decimal('final_total', 22, 4)->default(0);
            $table->integer('created_by')->default(1);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transaction_payments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->default('cash');
            $table->boolean('is_return')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('business_locations', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('name')->default('Main Location');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('essentials_user_sales_targets', function ($table) {
            $table->id();
            $table->integer('user_id');
            $table->decimal('target_start', 22, 4)->default(0);
            $table->decimal('target_end', 22, 4)->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('tax_rates', function ($table) {
            $table->id();
            $table->integer('business_id')->default(1);
            $table->string('name')->default('VAT');
            $table->decimal('amount', 22, 4)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('transaction_sell_lines', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->decimal('quantity', 22, 4)->default(1);
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('essentials_leaves', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('user_id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('essentials_attendances', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('user_id');
            $table->dateTime('clock_in_time')->nullable();
            $table->dateTime('clock_out_time')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('essentials_allowances_and_deductions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->string('description')->nullable();
            $table->string('type')->default('allowance');
            $table->string('amount_type')->default('fixed');
            $table->decimal('amount', 22, 4)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('essentials_user_allowance_and_deductions', function ($table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('allowance_deduction_id');
            $table->timestamps();
        });

        app()->register(\Modules\Laundry\Providers\RouteServiceProvider::class);
        app()->register(\Modules\Essentials\Providers\RouteServiceProvider::class);
        \Illuminate\Support\Facades\View::addNamespace('essentials', base_path('Modules/Essentials/Resources/views'));
        \Illuminate\Support\Facades\View::addNamespace('laundry', base_path('Modules/Laundry/Resources/views'));
    }

    public function test_user_laundry_bonus_rate_saving_and_report_calculation()
    {
        $business = Business::create([
            'name' => 'Bonus Business',
            'currency_id' => 1,
        ]);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        $admin = User::create([
            'business_id' => $business->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin_bonus_' . time(),
            'email' => 'admin_bonus_' . time() . '@test.com',
            'user_type' => 'user',
        ]);
        $admin->givePermissionTo('superadmin');

        $staff = User::create([
            'business_id' => $business->id,
            'first_name' => 'Tukang',
            'last_name' => 'Cuci',
            'username' => 'tukang_cuci_' . time(),
            'email' => 'tukang_cuci_' . time() . '@test.com',
            'user_type' => 'user',
            'laundry_bonus_per_point' => 500.0000,
        ]);

        $this->assertEquals(500.0, (float) $staff->laundry_bonus_per_point);

        $orderSheet = LaundryOrderSheet::create([
            'business_id' => $business->id,
            'order_no' => 'LND-TEST-001',
            'quantity' => 10,
            'unit_name' => 'kg',
            'total_amount' => 50000,
            'received_at' => Carbon::now(),
            'created_by' => $admin->id,
        ]);

        $process = LaundryProcess::create([
            'business_id' => $business->id,
            'name' => 'Pencucian',
            'points' => 2.5,
            'sort_order' => 1,
        ]);

        $log = LaundryOrderProcessLog::create([
            'order_sheet_id' => $orderSheet->id,
            'laundry_process_id' => $process->id,
            'staff_id' => $staff->id,
            'status' => 'completed',
            'points_earned' => 25.0,
            'completed_at' => Carbon::now(),
        ]);

        $this->actingAs($admin);

        // Test LaundryReportController AJAX report data
        $response = $this->withSession(['user.business_id' => $business->id])
            ->getJson(route('laundry.reports.staff_points'), [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);

        // Verify report contains calculated bonus (25 points * 500 = 12,500)
        $found = false;
        foreach ($data['data'] as $row) {
            if ($row['order_no'] === 'LND-TEST-001') {
                $found = true;
                $this->assertStringContainsString('12,500', $row['total_bonus']);
                break;
            }
        }
        $this->assertTrue($found, 'Report row with LND-TEST-001 was found');
    }

    public function test_laundry_bonus_integration_with_hrm_payroll()
    {
        $business = Business::create([
            'name' => 'Payroll Bonus Business',
            'currency_id' => 1,
        ]);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        $admin = User::create([
            'business_id' => $business->id,
            'first_name' => 'Payroll',
            'last_name' => 'Admin',
            'username' => 'payroll_admin_' . time(),
            'email' => 'payroll_admin_' . time() . '@test.com',
            'user_type' => 'user',
        ]);
        $admin->givePermissionTo('superadmin');

        $staff = User::create([
            'business_id' => $business->id,
            'first_name' => 'Tukang',
            'last_name' => 'Cuci',
            'username' => 'tukang_cuci_payroll_' . time(),
            'email' => 'tukang_cuci_payroll_' . time() . '@test.com',
            'user_type' => 'user',
            'essentials_salary' => 2000000,
            'essentials_pay_period' => 'month',
            'laundry_bonus_per_point' => 500.0000,
        ]);

        $now = Carbon::now();
        $orderSheet = LaundryOrderSheet::create([
            'business_id' => $business->id,
            'order_no' => 'LND-PAYROLL-001',
            'quantity' => 10,
            'unit_name' => 'kg',
            'total_amount' => 50000,
            'received_at' => $now,
            'created_by' => $admin->id,
        ]);

        $process = LaundryProcess::create([
            'business_id' => $business->id,
            'name' => 'Pencucian',
            'points' => 2.0,
            'sort_order' => 1,
        ]);

        LaundryOrderProcessLog::create([
            'order_sheet_id' => $orderSheet->id,
            'laundry_process_id' => $process->id,
            'staff_id' => $staff->id,
            'status' => 'completed',
            'points_earned' => 20.0,
            'completed_at' => $now->startOfMonth()->addDays(5),
        ]);

        $this->actingAs($admin);

        $monthYear = $now->format('m/Y');
        session(['user.business_id' => $business->id]);

        $request = \Illuminate\Http\Request::create('/hrm/payroll/create', 'GET', [
            'employee_ids' => [$staff->id],
            'month_year' => $monthYear,
            'primary_work_location' => null,
        ]);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $essentialsUtilMock = \Mockery::mock(\Modules\Essentials\Utils\EssentialsUtil::class)->makePartial();
        $essentialsUtilMock->shouldReceive('getTotalWorkDuration')->andReturn(160);
        $essentialsUtilMock->shouldReceive('getTotalLeavesForGivenDateOfAnEmployee')->andReturn(0);
        $essentialsUtilMock->shouldReceive('getTotalDaysWorkedForGivenDateOfAnEmployee')->andReturn(20);
        $essentialsUtilMock->shouldReceive('getEssentialsSettings')->andReturn([]);
        $essentialsUtilMock->shouldReceive('getEmployeeAllowancesAndDeductions')->andReturn(collect([]));

        $controller = new \Modules\Essentials\Http\Controllers\PayrollController(
            app(\App\Utils\ModuleUtil::class),
            $essentialsUtilMock,
            app(\App\Utils\Util::class),
            app(\App\Utils\TransactionUtil::class),
            app(\App\Utils\BusinessUtil::class)
        );

        $view = $controller->create();
        $viewData = $view->getData();

        $this->assertArrayHasKey('payrolls', $viewData);
        $this->assertArrayHasKey($staff->id, $viewData['payrolls']);

        $staffPayroll = $viewData['payrolls'][$staff->id];
        $this->assertArrayHasKey('allowances', $staffPayroll);

        // 20 points * 500 = 10,000 bonus
        $allowanceNames = $staffPayroll['allowances']['allowance_names'];
        $allowanceAmounts = $staffPayroll['allowances']['allowance_amounts'];

        $bonusIndex = array_search(__('laundry::lang.laundry_bonus_per_point'), $allowanceNames);
        $this->assertNotFalse($bonusIndex, 'Laundry bonus allowance found in payroll');
        $this->assertEquals(10000.0, (float) $allowanceAmounts[$bonusIndex]);
    }
}
