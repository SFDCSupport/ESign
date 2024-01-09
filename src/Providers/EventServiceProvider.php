<?php

namespace NIIT\ESign\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Base;
use NIIT\ESign\Events\SendDocumentLink;
use NIIT\ESign\Events\SigningProcessCompleted;
use NIIT\ESign\Events\SigningProcessStarted;
use NIIT\ESign\Listeners\SendSigningLink;
use NIIT\ESign\Listeners\SigningCompletedListener;
use NIIT\ESign\Listeners\SigningStartedListener;

class EventServiceProvider extends Base
{
    protected $listen = [
        SigningProcessStarted::class => [
            SigningStartedListener::class,
        ],
        SendDocumentLink::class => [
            SendSigningLink::class,
        ],
        SigningProcessCompleted::class => [
            SigningCompletedListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
