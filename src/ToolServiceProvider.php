<?php

namespace Opscale\NovaWebhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaWebhooks\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutes();

        if ($this->app->runningInConsole()) {
            $this->loadMigrations();
        }

        Nova::serving(function (ServingNova $event) {
            $this->loadResources();
        });
    }

    public function register()
    {
        //
    }

    protected function loadResources()
    {
        Nova::resources([
            \Opscale\NovaWebhooks\Nova\Webhook::class,
        ]);
    }

    protected function loadRoutes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/opscale-co/nova-webhooks')
            ->group(__DIR__ . '/../routes/api.php');
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ]);
    }
}
