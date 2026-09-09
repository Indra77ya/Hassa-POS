<?php

namespace Tests\Feature;

use App\User;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;
use Modules\Superadmin\Entities\Package;
use Modules\Superadmin\Entities\Subscription;
use Tests\TestCase;

class ModuleSubscriptionPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Module::enable('Superadmin');

        Schema::dropIfExists('system');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('users');
        Schema::dropIfExists('business');

        Schema::create('business', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->integer('business_id')->nullable();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('language', 7)->default('en');
            $table->timestamps();
        });

        Schema::create('packages', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('location_count')->default(0);
            $table->integer('user_count')->default(0);
            $table->integer('product_count')->default(0);
            $table->integer('invoice_count')->default(0);
            $table->string('interval')->default('days');
            $table->integer('interval_count')->default(30);
            $table->decimal('price', 22, 4)->default(0);
            $table->boolean('is_active')->default(1);
            $table->text('package_details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('subscriptions', function ($table) {
            $table->id();
            $table->integer('business_id');
            $table->integer('package_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->text('package_details')->nullable();
            $table->decimal('package_price', 22, 4)->default(0);
            $table->string('paid_via')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->string('status')->default('approved');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('system', function ($table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert Superadmin module version into system table so isSuperadminInstalled returns true
        DB::table('system')->insert([
            'key' => 'superadmin_version',
            'value' => '1.0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_module_permission_subscription_returns_false_when_unchecked()
    {
        $business_id = DB::table('business')->insertGetId([
            'name' => 'Test Business',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $packageDetails = [
            'assetmanagement_module' => 0,
            'laundry_module' => 0,
            'accounting_module' => 1,
        ];

        $package = Package::create([
            'name' => 'Basic Package',
            'description' => 'Test package',
            'location_count' => 5,
            'user_count' => 5,
            'product_count' => 50,
            'invoice_count' => 100,
            'interval' => 'days',
            'interval_count' => 30,
            'price' => 100,
            'is_active' => 1,
            'package_details' => json_encode($packageDetails),
        ]);

        Subscription::create([
            'business_id' => $business_id,
            'package_id' => $package->id,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDays(30)->toDateTimeString(),
            'package_details' => $packageDetails,
            'package_price' => $package->price,
            'paid_via' => 'offline',
            'payment_transaction_id' => 'TXN123',
            'status' => 'approved',
        ]);

        $user = new User();
        $user->id = 1;
        $user->business_id = $business_id;

        $this->actingAs($user);

        $moduleUtil = new ModuleUtil();

        $is_asset_enabled = $moduleUtil->hasThePermissionInSubscription($business_id, 'assetmanagement_module');
        $is_laundry_enabled = $moduleUtil->hasThePermissionInSubscription($business_id, 'laundry_module');
        $is_accounting_enabled = $moduleUtil->hasThePermissionInSubscription($business_id, 'accounting_module');

        $this->assertFalse((bool) $is_asset_enabled, 'Asset management module should be disabled when unchecked in package');
        $this->assertFalse((bool) $is_laundry_enabled, 'Laundry module should be disabled when unchecked in package');
        $this->assertTrue((bool) $is_accounting_enabled, 'Accounting module should be enabled when checked in package');
    }

    public function test_module_permission_subscription_returns_false_for_superadmin_when_package_has_unchecked_modules()
    {
        $business_id = DB::table('business')->insertGetId([
            'name' => 'Test Business Superadmin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $packageDetails = [
            'assetmanagement_module' => 0,
            'laundry_module' => 0,
        ];

        $package = Package::create([
            'name' => 'Custom Package',
            'description' => 'Test package for superadmin',
            'location_count' => 5,
            'user_count' => 5,
            'product_count' => 50,
            'invoice_count' => 100,
            'interval' => 'days',
            'interval_count' => 30,
            'price' => 100,
            'is_active' => 1,
            'package_details' => json_encode($packageDetails),
        ]);

        Subscription::create([
            'business_id' => $business_id,
            'package_id' => $package->id,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDays(30)->toDateTimeString(),
            'package_details' => $packageDetails,
            'package_price' => $package->price,
            'paid_via' => 'offline',
            'payment_transaction_id' => 'TXN456',
            'status' => 'approved',
        ]);

        $superadmin = \Mockery::mock(User::class)->makePartial();
        $superadmin->id = 2;
        $superadmin->business_id = $business_id;
        $superadmin->shouldReceive('can')->with('superadmin')->andReturn(true);

        $this->actingAs($superadmin);

        $moduleUtil = new ModuleUtil();

        $is_asset_enabled = $moduleUtil->hasThePermissionInSubscription($business_id, 'assetmanagement_module');
        $is_laundry_enabled = $moduleUtil->hasThePermissionInSubscription($business_id, 'laundry_module');

        $this->assertFalse((bool) $is_asset_enabled, 'Asset management module should be disabled for superadmin if unchecked in business package');
        $this->assertFalse((bool) $is_laundry_enabled, 'Laundry module should be disabled for superadmin if unchecked in business package');
    }
}
