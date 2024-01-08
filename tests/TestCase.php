<?php

namespace NIIT\ESign\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use NIIT\ESign\ESign;
use NIIT\ESign\ESignServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'NIIT\\ESign\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ESignServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.connections.esign', [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../database/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.default', 'esign');

        Schema::dropAllTables();

        $app->singleton(ESign::class);
        $app->alias('esign', ESign::class);
        (new ESign)->registerUserStampsMacro();

        $migration = include __DIR__.'/../database/migrations/esign_migrations.php';
        $migration->up();
    }
}
