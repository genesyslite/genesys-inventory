<?php

namespace GenesysLite\GenesysInventory\Providers;

use Illuminate\Support\ServiceProvider;

class GenesysInventoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/genesysInventory.php',
            'genesysFact'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) { // function not available and 'publish' not relevant in Lumen
            $this->publishes([
                __DIR__.'/../../config/genesysInventory.php' => config_path('genesysInventory.php'),
            ], 'config');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
