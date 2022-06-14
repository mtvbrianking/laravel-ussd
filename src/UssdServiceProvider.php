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
                __DIR__.'/../config/ussd.php' => base_path('config/ussd.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../menus/menu.xsd' => menus_path('menu.xsd'),
            ], 'schema');

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
