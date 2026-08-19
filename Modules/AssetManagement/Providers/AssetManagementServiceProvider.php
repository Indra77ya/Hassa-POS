<?php

namespace Modules\AssetManagement\Providers;

use Illuminate\Support\ServiceProvider;

class AssetManagementServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->commands([
            \Modules\AssetManagement\Console\RunMonthlyDepreciationCommand::class,
        ]);

        try {
            \Modules\AssetManagement\Http\AssetPermission::createPermissions();
        } catch (\Exception $e) {
            // Ignore if permissions table doesn't exist during initial setup
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/assetmanagement');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_filter([$viewPath]), [$sourcePath]), 'assetmanagement');
    }
}
