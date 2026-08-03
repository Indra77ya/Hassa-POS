<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Modules\Essentials\Entities\Shift;
use Modules\Essentials\Entities\EssentialsAttendance;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ImportAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register Essentials module provider
        $this->app->register(\Modules\Essentials\Providers\EssentialsServiceProvider::class);
        request()->setLaravelSession($this->app['session']->driver());

        // Load the web routes directly for the test
        \Illuminate\Support\Facades\Route::middleware('web')
            ->namespace('Modules\Essentials\Http\Controllers')
            ->group(module_path('Essentials', '/Routes/web.php'));

        // Bypass Spatie permission DB queries by defining a global Gate::before rule
        Gate::before(function () {
            return true;
        });

        // Create tables needed
        Schema::dropIfExists('business');
        Schema::create('business', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('email')->unique();
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('essentials_shifts');
        Schema::create('essentials_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::dropIfExists('essentials_attendances');
        Schema::create('essentials_attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->integer('business_id')->index();
            $table->dateTime('clock_in_time')->nullable();
            $table->dateTime('clock_out_time')->nullable();
            $table->integer('essentials_shift_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('clock_in_note')->nullable();
            $table->text('clock_out_note')->nullable();
            $table->timestamps();
        });

        // Create Spatie tables to prevent SQL errors in SQLite
        Schema::dropIfExists('permissions');
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::dropIfExists('roles');
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::dropIfExists('model_has_roles');
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->integer('role_id');
            $table->string('model_type');
            $table->integer('model_id');
        });

        // Seed initial business
        DB::table('business')->insert([
            'id' => 1,
            'name' => 'Hassa POS Business'
        ]);
    }

    public function testImportAttendanceWithShiftColumn()
    {
        // 1. Create a mock user
        $user = User::create([
            'id' => 10,
            'business_id' => 1,
            'email' => 'employee@example.com',
            'first_name' => 'Employee',
            'username' => 'employee_user'
        ]);

        // 2. Create a shift
        $shift = Shift::create([
            'id' => 5,
            'business_id' => 1,
            'name' => 'Day Shift'
        ]);

        // 3. Create a real Excel file with 7 columns including the Shift name
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Email', 'Clock-in Time', 'Clock-out Time', 'Shift', 'Clock-in note', 'Clock-out note', 'IP address'],
            ['employee@example.com', '2026-08-03 08:46:40', '2026-08-03 17:00:00', 'Day Shift', 'Note In', 'Note Out', '192.168.1.1']
        ]);

        $writer = new Xlsx($spreadsheet);
        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_attendance') . '.xlsx';
        $writer->save($tempFilePath);

        // UploadedFile wrapper
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'attendance_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        // Create a real admin user
        $admin = User::create([
            'id' => 1,
            'business_id' => 1,
            'email' => 'admin@example.com',
            'first_name' => 'Admin',
            'username' => 'admin_user'
        ]);

        $this->actingAs($admin);

        // Disable AdminSidebarMenu middleware to avoid roles relation queries
        $this->withoutMiddleware([\App\Http\Middleware\AdminSidebarMenu::class]);

        // Mock ModuleUtil to bypass complex database checks like is_admin roles query
        $moduleUtil = \Mockery::mock(\App\Utils\ModuleUtil::class)->makePartial();
        $moduleUtil->shouldReceive('is_admin')->andReturn(true);
        $moduleUtil->shouldReceive('hasThePermissionInSubscription')->andReturn(true);
        $moduleUtil->shouldReceive('notAllowedInDemo')->andReturn(null);
        $moduleUtil->shouldReceive('getUserIpAddr')->andReturn('192.168.1.1');
        $this->app->instance(\App\Utils\ModuleUtil::class, $moduleUtil);

        // Put necessary session variables
        session([
            'user' => ['business_id' => 1],
            'business' => ['time_zone' => 'UTC']
        ]);

        // Request import
        $response = $this->post('/hrm/import-attendance', [
            'attendance' => $uploadedFile
        ]);

        // Clean up temp file
        @unlink($tempFilePath);

        // Assert response redirects back (or status 302)
        $response->assertStatus(302);

        // Check that the database contains the imported attendance record
        $this->assertDatabaseHas('essentials_attendances', [
            'user_id' => $user->id,
            'business_id' => 1,
            'clock_in_time' => '2026-08-03 08:46:40',
            'clock_out_time' => '2026-08-03 17:00:00',
            'essentials_shift_id' => $shift->id,
            'clock_in_note' => 'Note In',
            'clock_out_note' => 'Note Out',
            'ip_address' => '192.168.1.1'
        ]);
    }
}
