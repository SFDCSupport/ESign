<?php

namespace NIIT\ESign\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'esign:install {--tenant}';

    /**
     * @var string
     */
    protected $description = '...';

    public function handle(): int
    {
        $this->callSilent('vendor:publish', ['--tag' => 'esign-assets', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'esign-migrations', '--force' => true]);

        return 1;
    }
}
