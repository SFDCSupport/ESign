<?php

namespace NIIT\ESign\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as BaseServiceProvider;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;
use NIIT\ESign\Policies\DocumentPolicy;
use NIIT\ESign\Policies\SignerPolicy;

class AuthServiceProvider extends BaseServiceProvider
{
    /** array<class-string, class-string> */
    protected $policies = [
        Document::class => DocumentPolicy::class,
        Signer::class => SignerPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
