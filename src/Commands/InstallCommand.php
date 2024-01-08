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
        return 0;
    }
}
