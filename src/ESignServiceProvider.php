<?php

namespace NIIT\ESign;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as Base;

class ESignServiceProvider extends Base
{
    const NAME = 'esign';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', self::NAME);

        $this->app->register(Providers\EventServiceProvider::class);

        $this->app->singleton(ESign::class);
        $this->app->alias(self::NAME, ESign::class);
    }

    public function boot(): void
    {
        if (! Blueprint::hasMacro('userStamps')) {
            (new ESign)->registerUserStampsMacro();
        }

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
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/esign_migrations.php' => database_path('migrations/esign/'.date('Y_m_d_His').'_esign_migrations.php'),
            ], 'migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/'.self::NAME),
            ], 'views');

            $this->publishes([
                __DIR__.'/../public/vendor/'.self::NAME => public_path('vendor/'.self::NAME),
            ], 'assets');

            $this->commands([
                Commands\InstallCommand::class,
            ]);
        }
    }

    public function provides()
    {
        return [
            self::NAME,
            ESign::class,
        ];
    }
}
