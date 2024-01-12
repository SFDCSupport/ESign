<?php

namespace NIIT\ESign;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as Base;

class ESignServiceProvider extends Base
{
    const NAME = 'esign';

    protected $commands = [
        Commands\InstallCommand::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', self::NAME);

        $this->app->register(Providers\EventServiceProvider::class);

        $this->app->singleton('fileSampark', Support\FileSampark::class);

        $this->app->singleton(self::NAME, ESign::class);
        $this->app->alias(self::NAME, ESignFacade::class);
    }

    public function boot(): void
    {
        (new ESign)->registerMacros();

        Route::name(self::NAME.'.')
            ->prefix(self::NAME)
            ->middleware([
                'web',
                Http\Middleware\ESignMiddleware::class,
            ])->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', self::NAME);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::NAME);

        Gate::define('sign-document', function ($user, $document) {
            return $user->is($document->signer);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path(self::NAME.'.php'),
            ], self::NAME.'-config');

            $this->publishes([
                __DIR__.'/../database/migrations/esign_migrations.php' => database_path('migrations/esign/esign_migrations.php'),
            ], self::NAME.'-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/'.self::NAME),
            ], self::NAME.'-views');

            $this->publishes([
                __DIR__.'/../resources/assets/' => public_path('vendor/'.self::NAME),
            ], self::NAME.'-assets');

            if ($this->app->environment() === 'local') {
                $this->commands[] = Commands\DevCommand::class;
            }

            $this->commands($this->commands);
        }
    }

    public function provides()
    {
        return [
            self::NAME,
            ESign::class,
            FileSampark::class,
        ];
    }
}
