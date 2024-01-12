<?php

namespace NIIT\ESign\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Base;
use NIIT\ESign\Events\DocumentStatusChanged;
use NIIT\ESign\Events\ReadStatusChanged;
use NIIT\ESign\Events\SendStatusChanged;
use NIIT\ESign\Events\SigningProcessCompleted;
use NIIT\ESign\Events\SigningProcessStarted;
use NIIT\ESign\Events\SigningStatusChanged;
use NIIT\ESign\Listeners\DocumentStatusListener;
use NIIT\ESign\Listeners\ReadStatusListener;
use NIIT\ESign\Listeners\SendStatusListener;
use NIIT\ESign\Listeners\SigningCompletedListener;
use NIIT\ESign\Listeners\SigningStartedListener;
use NIIT\ESign\Listeners\SigningStatusListener;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\DocumentSigner;
use NIIT\ESign\Observers\DocumentObserver;
use NIIT\ESign\Observers\SignerObserver;

class EventServiceProvider extends Base
{
    protected $listen = [
        SigningProcessStarted::class => [
            SigningStartedListener::class,
        ],
        DocumentStatusChanged::class => [
            DocumentStatusListener::class,
        ],
        SendStatusChanged::class => [
            SendStatusListener::class,
        ],
        ReadStatusChanged::class => [
            ReadStatusListener::class,
        ],
        SigningStatusChanged::class => [
            SigningStatusListener::class,
        ],
        SigningProcessCompleted::class => [
            SigningCompletedListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        Document::observe(DocumentObserver::class);
        DocumentSigner::observe(SignerObserver::class);
    }
}
