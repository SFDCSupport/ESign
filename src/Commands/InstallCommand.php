<?php

namespace NIIT\ESign\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;

class InstallCommand extends Command
{
    use ConfirmableTrait;

    /** @var string */
    protected $signature = 'esign:install {--tenant=}
                                     {--migrate}';

    /** @var string */
    protected $description = '...';

    protected int $invalidCount = 0;

    protected int $maxInvalidCount = 3;

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->call('vendor:publish', ['--tag' => 'esign-assets', '--force' => true]);
        $this->call('vendor:publish', ['--tag' => 'esign-migrations', '--force' => true]);

        if ($this->option('migrate')) {
            $tenant = $this->askForTenant();

            if (! $tenant) {
                $this->info('Fail: migrations!');

                return 1;
            }

            if ($tenant->execute(function (Tenant $tenant) {
                DB::table('migrations')->where('migration', 'esign_migrations')->delete();
                $this->info('ESign migration related entries removed from migrations table!');

                return true;
            })) {
                $this->call('tenants:artisan', [
                    'artisanCommand' => 'migrate --database=tenant --path=database/migrations/esign',
                    '--tenant' => $tenant->id,
                ]);

                $this->info('Done: migrations!');
            } else {
                $this->error('Unable to clean old migration entries! Do it manually!');
            }
        }

        $this->info('All done!');

        return 1;
    }

    protected function askForTenant(): bool|Tenant
    {
        if (($tenant = $this->option('tenant')) && ($model = $this->isValidTenant($tenant))) {
            return $model;
        }

        if (($tenant = $this->ask('Run migrations for which tenant?')) && ($model = $this->isValidTenant($tenant))) {
            return $model;
        }

        $this->invalidCount++;

        if ($this->invalidCount >= $this->maxInvalidCount) {
            return false;
        }

        $this->error('Please provide an valid tenant?');

        return $this->askForTenant();
    }

    protected function isValidTenant($tenant): bool|Tenant
    {
        $tenant = (int) $tenant;

        if ($tenant === 0) {
            return false;
        }

        DB::connection('landlord');

        if (! blank($model = Tenant::where('id', $tenant)->first())) {
            return $model;
        }

        $this->error('Fail: '.$tenant.': tenant not exists!');

        return false;
    }

    protected function getDefaultConfirmCallback(): \Closure
    {
        return function () {
            return Str::startsWith($this->getLaravel()->environment(), 'prod');
        };
    }
}
