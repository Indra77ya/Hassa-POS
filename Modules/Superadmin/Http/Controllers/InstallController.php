<?php

namespace Modules\Superadmin\Http\Controllers;

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
        $this->module_name = 'superadmin';
        $this->appVersion = config('superadmin.module_version');
        $this->module_display_name = 'Superadmin';
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if installed or not.
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
     * Installing Superadmin Module
     */
    public function install()
    {
        try {
            DB::beginTransaction();

            // request()->validate(
            //     ['license_code' => 'required',
            //         'login_username' => 'required', ],
            //     ['license_code.required' => 'License code is required',
            //         'login_username.required' => 'Username is required', ]
            // );

            // $license_code = request()->license_code;
            // $email = request()->email;
            // $login_username = request()->login_username;
            // $pid = config('superadmin.pid');

            // //Validate
            // $response = pos_boot(url('/'), __DIR__, $license_code, $email, $login_username, $type = 1, $pid);

            // if (! empty($response)) {
            //     return $response;
            // }

            $is_installed = System::getProperty($this->module_name.'_version');
            if (! empty($is_installed)) {
                abort(404);
            }

            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => 'Superadmin', '--force' => true]);
            System::addProperty($this->module_name.'_version', $this->appVersion);

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'Superadmin module installed succesfully',
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
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name.'_version');

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * update module
     *
     * @return Response
     */
    public function update()
    {
        //Check if superadmin_version is same as appVersion then 404
        //If appVersion > superadmin_version - run update script.
        //Else there is some problem.
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        return $this->updateExecute();
    }

    public function updateExecute()
    {
        //Check if superadmin_version is same as appVersion then 404
        //If appVersion > superadmin_version - run update script.
        //Else there is some problem.
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {

            // request()->validate(
            //     ['license_code' => 'required',
            //         'login_username' => 'required', ],
            //     ['license_code.required' => 'License code is required',
            //         'login_username.required' => 'Username is required', ]
            // );

            // $license_code = request()->license_code;
            // $email = request()->email;
            // $login_username = request()->login_username;
            // $pid = config('superadmin.pid');

            // //Validate
            // $response = pos_boot(url('/'), __DIR__, $license_code, $email, $login_username, $type = 1, $pid);

            // if (! empty($response)) {
            //     return $response;
            // }

            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $superadmin_version = System::getProperty($this->module_name.'_version');

            if (Comparator::greaterThan($this->appVersion, $superadmin_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => 'Superadmin', '--force' => true]);
                System::setProperty($this->module_name.'_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'Superadmin module updated Succesfully to version '.$this->appVersion.' !!',
            ];

            return redirect()->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])->with(['status' => $output]);

        } catch (\Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }
    }
}
