<?php

namespace Tests\Feature;

use App\Business;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Create necessary tables in SQLite memory DB
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_templates');
        Schema::dropIfExists('users');
        Schema::dropIfExists('business');
        Schema::dropIfExists('system');

        Schema::create('system', function (Blueprint $table) {
            $table->string('key');
            $table->string('value')->nullable();
        });

        DB::table('system')->insert([
            ['key' => 'db_version', 'value' => '1.0'],
            ['key' => 'app_version', 'value' => '1.0'],
        ]);

        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('time_zone')->default('Asia/Jakarta');
            $table->text('pos_settings')->nullable();
            $table->text('common_settings')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('business_id')->unsigned();
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
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(0);
            $table->boolean('is_service_staff')->default(0);
            $table->timestamps();
        });

        Schema::create('role_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('permissions')->nullable();
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

        // Seed core granular permissions
        $perms = [
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'laundry.view_dashboard', 'laundry.view', 'laundry.create', 'laundry.update', 'laundry.delete',
            'laundry.add_payment', 'laundry.print', 'laundry.send_whatsapp', 'repair.view', 'repair.send_whatsapp',
            'user.view', 'user.create', 'user.update', 'user.delete'
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Create test business and admin user
        Business::create(['id' => 1, 'name' => 'Test Business', 'time_zone' => 'Asia/Jakarta']);
        $user = User::create([
            'id' => 1,
            'first_name' => 'Admin',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456'),
            'business_id' => 1,
        ]);

        $admin_role = Role::create([
            'name' => 'Admin#1',
            'business_id' => 1,
            'guard_name' => 'web',
        ]);
        $admin_role->syncPermissions($perms);
        $user->assignRole('Admin#1');

        // Grant permission checks for user
        Gate::before(function () {
            return true;
        });
    }

    /** @test */
    public function it_creates_a_role_with_granular_permissions_and_saves_template()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->withSession([
                'user' => ['business_id' => 1, 'id' => 1],
                'business' => ['time_zone' => 'Asia/Jakarta'],
            ])
            ->post('/roles', [
                'name' => 'Laundry Manager Test',
                'description' => 'Role for testing laundry admin capabilities',
                'permissions' => [
                    'laundry.view_dashboard',
                    'laundry.view',
                    'laundry.create',
                    'laundry.update',
                    'laundry.add_payment',
                    'laundry.print',
                    'laundry.send_whatsapp',
                ],
                'save_as_template' => 1,
                'template_name' => 'Laundry Custom Template',
            ]);

        $response->assertRedirect('roles');

        $role = Role::where('name', 'Laundry Manager Test#1')->first();
        $this->assertNotNull($role);
        $this->assertEquals('Role for testing laundry admin capabilities', $role->description);
        $this->assertTrue($role->hasPermissionTo('laundry.add_payment'));
        $this->assertTrue($role->hasPermissionTo('laundry.send_whatsapp'));

        $this->assertDatabaseHas('role_templates', [
            'business_id' => 1,
            'name' => 'Laundry Custom Template',
        ]);
    }

    /** @test */
    public function it_renders_create_role_page_with_system_presets_and_permission_groups()
    {
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->withSession([
                'user' => ['business_id' => 1, 'id' => 1],
                'business' => ['time_zone' => 'Asia/Jakarta'],
            ])
            ->get('/roles/create');

        $response->assertStatus(200);
        $response->assertSee('laundry_admin');
        $response->assertSee('repair_tech');
        $response->assertSee('mfg_supervisor');
        $response->assertSee('laundry.add_payment');
        $response->assertSee('laundry.send_whatsapp');
        $response->assertSee('repair.send_whatsapp');
    }
}
