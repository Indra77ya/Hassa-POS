<?php

namespace Modules\Laundry\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = 'laundry';
        $this->appVersion = config('laundry.module_version', '1.0');
        $this->module_display_name = 'Laundry';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin') && ! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        // Check if installed or not.
        $is_installed = System::getProperty($this->module_name.'_version');
        if (! empty($is_installed)) {
            abort(404);
        }

        return $this->install();
    }

    /**
     * Initialize all install functions
     */
    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
    }

    /**
     * Installing Laundry Module
     */
    public function install()
    {
        try {
            DB::beginTransaction();

            $is_installed = System::getProperty($this->module_name.'_version');
            if (! empty($is_installed)) {
                abort(404);
            }

            Artisan::call('module:migrate', ['module' => 'Laundry', '--force' => true]);
            System::addProperty($this->module_name.'_version', $this->appVersion);

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Laundry module installed successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()
                ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
                ->with('status', $output);
    }

    /**
     * Uninstall
     *
     * @return Response
     */
    public function uninstall()
    {
        if (! auth()->user()->can('superadmin') && ! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name.'_version');

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Update module
     *
     * @return Response
     */
    public function update()
    {
        if (! auth()->user()->can('superadmin') && ! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $laundry_version = System::getProperty($this->module_name.'_version');

            if (Comparator::greaterThan($this->appVersion, $laundry_version)) {
                $this->installSettings();
                Artisan::call('module:migrate', ['module' => 'Laundry', '--force' => true]);
                System::setProperty($this->module_name.'_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Laundry module updated successfully to version '.$this->appVersion.' !',
            ];

            return redirect()->back()->with(['status' => $output]);
        } catch (\Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }
    }
}
