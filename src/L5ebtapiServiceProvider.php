<?php

namespace rodshaffer\l5ebtapi;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class L5ebtapiServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        App::bind('L5ebtapi', function()
        {
            return new \rodshaffer\l5ebtapi\L5ebtapi;
        });

        // use this if your package has views
        //$this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'l5ebtapi');

        // use this if your package has lang files
        //$this->loadTranslationsFrom(__DIR__.'/resources/lang', 'l5ebtapi');

        // use this if your package has routes
        $this->setupRoutes($this->app->router);

        // use this if your package needs a config file
        // $this->publishes([
        //         __DIR__.'/config/config.php' => config_path('skeleton.php'),
        // ]);

        // use the vendor configuration file as fallback
        // $this->mergeConfigFrom(
        //     __DIR__.'/config/config.php', 'skeleton'
        // );
    }
    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $router->group(['namespace' => 'rodshaffer\l5ebtapi\Http\Controllers'], function($router)
        {
            require __DIR__.'/Http/routes.php';
        });
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerSkeleton();

        // use this if your package has a config file
        // config([
        //         'config/skeleton.php',
        // ]);
    }
    private function registerSkeleton()
    {
        $this->app->bind('L5ebtapi',function($app){
            return new L5ebtapi($app);
        });
    }
}