<?php

namespace Tests\Feature;

use App\Business;
use App\User;
use Database\Seeders\CreateRolesAndUsersSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComprehensiveGranularRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function () {
            return true;
        });

        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('business');

        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('currency_id')->nullable();
            $table->string('start_date')->nullable();
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->integer('fy_start_month')->default(1);
            $table->string('accounting_method')->default('fifo');
            $table->string('default_sales_discount')->default('0.00');
            $table->string('sell_price_tax')->default('includes');
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('surname')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('language')->default('id');
            $table->integer('business_id')->unsigned();
            $table->boolean('is_cmmsn_agnt')->default(0);
            $table->decimal('cmmsn_percent', 4, 2)->default(0.00);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('business_id')->unsigned();
            $table->string('guard_name')->default('web');
            $table->boolean('is_default')->default(0);
            $table->boolean('is_service_staff')->default(0);
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->string('model_type');
            $table->integer('model_id')->unsigned();
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->string('model_type');
            $table->integer('model_id')->unsigned();
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Business::create([
            'id' => 1,
            'name' => 'Test Business',
            'currency_id' => 1,
            'start_date' => '2023-01-01',
            'time_zone' => 'Asia/Jakarta',
            'fy_start_month' => 1,
            'accounting_method' => 'fifo',
            'default_sales_discount' => '0.00',
            'sell_price_tax' => 'includes',
            'created_by' => 1,
        ]);
    }

    public function test_migration_creates_granular_permissions_and_assigns_to_admin()
    {
        $admin_role = Role::create([
            'name' => 'Admin#1',
            'business_id' => 1,
            'guard_name' => 'web',
            'is_default' => 1,
        ]);

        $migration = include database_path('migrations/2026_09_14_000000_add_comprehensive_granular_permissions.php');
        $migration->up();

        $granular_permissions = [
            'product.export', 'product.import',
            'purchase.add_payment', 'purchase.print', 'purchase.export',
            'sell.add_payment', 'sell.print', 'sell.export',
            'customer.add_payment', 'customer.view_ledger',
            'expense.add_payment',
            'laundry.view_dashboard', 'laundry.update_status',
            'manufacturing.delete_recipe', 'manufacturing.delete_production',
            'repair.create', 'asset.create',
        ];

        foreach ($granular_permissions as $perm_name) {
            $this->assertDatabaseHas('permissions', ['name' => $perm_name]);
            $this->assertTrue($admin_role->fresh()->hasPermissionTo($perm_name));
        }
    }

    public function test_seeder_creates_and_syncs_permissions_for_staff_roles()
    {
        $seeder = new CreateRolesAndUsersSeeder();
        $seeder->run();

        $sales_role = Role::where('name', 'Sales#1')->first();
        $this->assertNotNull($sales_role);
        $this->assertTrue($sales_role->hasPermissionTo('sell.view'));
        $this->assertTrue($sales_role->hasPermissionTo('sell.create'));

        $akunting_role = Role::where('name', 'Akunting#1')->first();
        $this->assertNotNull($akunting_role);
        $this->assertTrue($akunting_role->hasPermissionTo('accounting.view_journal'));
        $this->assertTrue($akunting_role->hasPermissionTo('accounting.add_journal'));

        $gudang_role = Role::where('name', 'Gudang#1')->first();
        $this->assertNotNull($gudang_role);
        $this->assertTrue($gudang_role->hasPermissionTo('product.view'));
        $this->assertTrue($gudang_role->hasPermissionTo('purchase.view'));
    }

    public function test_custom_403_view_renders_correctly()
    {
        \Illuminate\Support\Facades\Route::get('/test-403', function () {
            abort(403, 'Akses Ditolak: Testing 403 response.');
        });

        $response = $this->get('/test-403');
        $response->assertStatus(403);
        $response->assertSee('403 | Akses Ditolak');
        $response->assertSee('Kembali ke Dashboard');
    }
}
