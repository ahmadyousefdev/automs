<?php

namespace Ahmadyousefdev\Automs;

use Illuminate\Support\ServiceProvider;
use Ahmadyousefdev\Automs\Commands\CreateCommand;
use Ahmadyousefdev\Automs\Commands\GenerateControllerCommand;
use Ahmadyousefdev\Automs\Commands\GenerateMigrationCommand;
use Ahmadyousefdev\Automs\Commands\GenerateModelCommand;
use Ahmadyousefdev\Automs\Commands\GenerateRoutesCommand;
use Ahmadyousefdev\Automs\Commands\GenerateViewsCommand;

class AutomsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ahmadyousefdev');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'ahmadyousefdev');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/automs.php', 'automs');

        // Register the service the package provides.
        $this->app->singleton('automs', function ($app) {
            return new Automs;
        });

        

        // $this->commands([
        //     'command.automs.create',
        // ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['automs'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/automs.php' => config_path('automs.php'),
        ], 'automs.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/ahmadyousefdev'),
        ], 'automs.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/ahmadyousefdev'),
        ], 'automs.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ahmadyousefdev'),
        ], 'automs.views');*/

        $this->app->bind('command.automs.create', function ($app) {
            return new CreateCommand();
        });
        $this->app->bind('command.generate.migration', function ($app) {
            return new GenerateMigrationCommand();
        });
        $this->app->bind('command.generate.model', function ($app) {
            return new GenerateModelCommand();
        });
        $this->app->bind('command.generate.routes', function ($app) {
            return new GenerateRoutesCommand();
        });
        $this->app->bind('command.generate.controller', function ($app) {
            return new GenerateControllerCommand();
        });
        $this->app->bind('command.generate.views', function ($app) {
            return new GenerateViewsCommand();
        });
        // Registering package commands.
        $this->commands([
            'command.automs.create',
            'command.generate.migration',
            'command.generate.model',
            'command.generate.routes',
            'command.generate.controller',
            'command.generate.views'
        ]);
    }
}
