<?php

namespace Tests\Feature;

use App\Business;
use App\User;
use Database\Seeders\CreateRolesAndUsersSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateRolesAndUsersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Bypass permission checks
        Gate::before(function () {
            return true;
        });

        // Create necessary tables in SQLite memory DB
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

        // Create a test business
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

    public function test_seeder_creates_roles_and_users_with_correct_permissions_and_password()
    {
        $seeder = new CreateRolesAndUsersSeeder();
        $seeder->run();

        $expected_roles = ['Sales#1', 'Akunting#1', 'Gudang#1', 'Manufaktur#1', 'Teknisi#1'];
        foreach ($expected_roles as $role_name) {
            $role = Role::where('name', $role_name)->where('business_id', 1)->first();
            $this->assertNotNull($role, "Role {$role_name} should exist.");
        }

        $sales_role = Role::where('name', 'Sales#1')->first();
        $this->assertTrue($sales_role->hasPermissionTo('sell.view'));
        $this->assertTrue($sales_role->hasPermissionTo('view_cash_register'));

        $akunting_role = Role::where('name', 'Akunting#1')->first();
        $this->assertTrue($akunting_role->hasPermissionTo('account.access'));
        $this->assertTrue($akunting_role->hasPermissionTo('expense.access'));

        $gudang_role = Role::where('name', 'Gudang#1')->first();
        $this->assertTrue($gudang_role->hasPermissionTo('product.view'));
        $this->assertTrue($gudang_role->hasPermissionTo('purchase.create'));

        $manufaktur_role = Role::where('name', 'Manufaktur#1')->first();
        $this->assertTrue($manufaktur_role->hasPermissionTo('manufacturing.access_recipe'));

        $teknisi_role = Role::where('name', 'Teknisi#1')->first();
        $this->assertTrue($teknisi_role->hasPermissionTo('repair.view'));

        $expected_users = ['sales_user', 'akunting_user', 'gudang_user', 'manufaktur_user', 'teknisi_user'];
        foreach ($expected_users as $username) {
            $user = User::where('username', $username)->where('business_id', 1)->first();
            $this->assertNotNull($user, "User {$username} should exist.");
            $this->assertTrue(Hash::check('12345', $user->password), "User {$username} should have password '12345'.");
        }

        $sales_user = User::where('username', 'sales_user')->first();
        $this->assertTrue($sales_user->hasRole('Sales#1'));
    }

    public function test_artisan_command_creates_roles_and_users()
    {
        $exitCode = Artisan::call('pos:create-roles-users');
        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas('users', [
            'username' => 'sales_user',
            'business_id' => 1,
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Sales#1',
            'business_id' => 1,
        ]);
    }
}
