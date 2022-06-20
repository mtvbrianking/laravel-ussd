<?php

namespace Bmatovu\Ussd;

use Illuminate\Support\ServiceProvider;

class UssdServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ussd.php' => config_path('ussd.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../menus/menu.xsd' => menu_path('menu.xsd'),
            ], 'schema');

            $this->publishes([
                __DIR__.'/../bin/simulator.json' => base_path('simulator.json'),
            ], 'simulator');

            $this->commands([
                Commands\Validate::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ussd.php', 'ussd');
    }
}
