<?php

namespace NIIT\ESign;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use NIIT\ESign\Livewire\DocumentComponent;
use NIIT\ESign\Models\Asset;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class ESignServiceProvider extends ServiceProvider
{
    public const NAME = 'esign';

    protected array $commands = [
        Commands\InstallCommand::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', self::NAME);

        $this->app->register(Providers\EventServiceProvider::class);
        $this->app->register(Providers\AuthServiceProvider::class);

        $this->app->singleton('fileSampark', Support\FileSampark::class);

        $this->app->singleton(self::NAME, ESign::class);
        $this->app->alias(self::NAME, ESignFacade::class);
    }

    public function boot(): void
    {
        Livewire::component('esign-documents-component', DocumentComponent::class);

        (new ESign($this->app))->addMacros()->proceed();

        Route::bind('signing_url', function (string $value) {
            $signer = Signer::where('url', $value)->firstOrFail();

            Request::macro('signer', fn () => $signer);

            return $signer;
        });

        Route::name(self::NAME.'.')
            ->prefix(self::NAME)
            ->middleware([
                'web',
                Http\Middleware\ESignMiddleware::class,
            ])->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });

        $this->bindRouting();
        $this->loadAssets();

        Gate::define('sign-document', static function ($user, $document) {
            return $user->is($document->signer);
        });

        if ($this->app->runningInConsole()) {
            $this->registerConsoleProcess();
        }
    }

    public function provides()
    {
        return [
            self::NAME,
            ESign::class,
            Support\FileSampark::class,
        ];
    }

    private function loadAssets(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', self::NAME);
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::NAME);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    private function registerConsoleProcess()
    {
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

    private function bindRouting(): void
    {
        $uuidExpressions = config('esign.expressions.uuid');

        foreach ([
            'document' => Document::class,
            'signer' => Signer::class,
            'asset' => Asset::class,
        ] as $binding => $class) {
            Route::bind($binding, function (string $value) use ($class, $uuidExpressions) {
                if (preg_match($uuidExpressions, $value) !== 1) {
                    throw (new ModelNotFoundException)->setModel($class, $value);
                }

                return (new $class)->findOrFail($value);
            });
        }
    }
}
