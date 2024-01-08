<?php

namespace NIIT\ESign\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Base;
use NIIT\ESign\Events\DocumentSigned;
use NIIT\ESign\Listeners\SigningCompletedListener;

class EventServiceProvider extends Base
{
    protected $listen = [
        DocumentSigned::class => [
            SigningCompletedListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
