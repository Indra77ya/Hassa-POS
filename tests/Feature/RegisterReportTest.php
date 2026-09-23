<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Business;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisterReportTest extends TestCase
{
    protected $createdEnv = false;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

        if (! file_exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), 'APP_ENV=testing');
            $this->createdEnv = true;
        }

        Artisan::call('view:clear');

        Gate::before(function () {
            return true;
        });

        Schema::dropIfExists('system');
        Schema::create('system', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->string('value')->nullable();
        });
        DB::table('system')->insert(['key' => 'db_version', 'value' => '1.0']);

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->nullable();
            $table->string('user_type')->default('user');
            $table->tinyInteger('allow_login')->default(1);
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('language')->default('en');
            $table->tinyInteger('is_cmmsn_agnt')->default(0);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('currencies');
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country')->nullable();
            $table->string('currency')->nullable();
            $table->string('code')->nullable();
            $table->string('symbol')->nullable();
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            $table->timestamps();
        });

        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('currency_id')->nullable();
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->integer('fy_start_month')->default(1);
            $table->string('accounting_method')->default('fifo');
            $table->string('default_sales_discount')->default('0.00');
            $table->string('sell_price_tax')->default('includes');
            $table->text('custom_labels')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->integer('business_id')->nullable();
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('model_has_roles');
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id');
            $table->string('model_type');
            $table->integer('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::dropIfExists('permissions');
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::dropIfExists('role_has_permissions');
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::dropIfExists('model_has_permissions');
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->string('model_type');
            $table->integer('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::dropIfExists('notifications');
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->bigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        if ($this->createdEnv && file_exists(base_path('.env'))) {
            unlink(base_path('.env'));
        }

        parent::tearDown();
    }

    public function test_register_report_renders_successfully_without_custom_labels()
    {
        $currencyId = DB::table('currencies')->insertGetId([
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
        ]);

        $business = Business::create([
            'name' => 'Test Business',
            'currency_id' => $currencyId,
            'time_zone' => 'Asia/Jakarta',
            'custom_labels' => null,
        ]);

        $user = User::create([
            'business_id' => $business->id,
            'user_type' => 'user',
            'allow_login' => 1,
            'first_name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@test.com',
        ]);

        $permission = Permission::create(['name' => 'register_report.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Admin#' . $business->id, 'guard_name' => 'web', 'business_id' => $business->id]);

        $user->assignRole($role);
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->get('/reports/register-report');

        $response->assertStatus(200);
        $response->assertSee(__('lang_v1.custom_payment_1'));
        $response->assertSee(__('lang_v1.custom_payment_7'));
    }

    public function test_register_report_renders_custom_labels_when_configured()
    {
        $currencyId = DB::table('currencies')->insertGetId([
            'country' => 'Indonesia',
            'currency' => 'Rupiah',
            'code' => 'IDR',
            'symbol' => 'Rp',
            'thousand_separator' => '.',
            'decimal_separator' => ',',
        ]);

        $business = Business::create([
            'name' => 'Test Business Custom Labels',
            'currency_id' => $currencyId,
            'time_zone' => 'Asia/Jakarta',
            'custom_labels' => json_encode([
                'payments' => [
                    'custom_pay_1' => 'QRIS Toko',
                    'custom_pay_2' => 'Transfer BCA',
                ],
            ]),
        ]);

        $user = User::create([
            'business_id' => $business->id,
            'user_type' => 'user',
            'allow_login' => 1,
            'first_name' => 'Admin',
            'username' => 'admin2',
            'email' => 'admin2@test.com',
        ]);

        $permission = Permission::where('name', 'register_report.view')->first() ?: Permission::create(['name' => 'register_report.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Admin#' . $business->id, 'guard_name' => 'web', 'business_id' => $business->id]);

        $user->assignRole($role);
        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->get('/reports/register-report');

        $response->assertStatus(200);
        $response->assertSee('QRIS Toko');
        $response->assertSee('Transfer BCA');
        $response->assertSee(__('lang_v1.custom_payment_3'));
    }
}
