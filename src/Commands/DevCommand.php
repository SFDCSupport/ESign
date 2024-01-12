<?php

namespace NIIT\ESign\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DevCommand extends Command
{
    /** @var string */
    protected $signature = 'esign:dev-install {--tenant}';

    /** @var string */
    protected $description = '...';

    /** @var bool */
    protected $hidden = true;

    public function handle(): int
    {
        DB::table('migrations')->where('migration', 'esign_migrations')->delete();

        $this->callSilently('tenants:artisan "migrate --database=tenant --path=vendor\niit\esign\database\migrations" --tenant=7');
    }
}
