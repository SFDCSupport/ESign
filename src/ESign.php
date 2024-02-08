<?php

namespace NIIT\ESign;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Mail\Signer\SendSigningLink;
use NIIT\ESign\Models\Document;
use NIIT\ESign\Models\Signer;

class ESign
{
    protected bool $addMacros = false;

    protected bool $addMenu = false;

    public function __construct(protected ?Application $app = null)
    {
        $this->app = $app ?? app();
    }

    /**
     * @return mixed|\Illuminate\Config\Repository
     */
    public function config(array|null|string $key = null, mixed $default = null)
    {
        return config('esign'.($key ? '.'.$key : ''), $default);
    }

    public function signingHeaders(): array
    {
        return $this->config('signing_headers', []);
    }

    public function addMacros(): self
    {
        $this->addMacros = true;

        return $this;
    }

    public function addMenu(): self
    {
        $this->addMenu = true;

        return $this;
    }

    public function proceed(): void
    {
        if ($this->addMacros) {
            collect(['eSignUserStamps', 'notify'])->each(function ($macro) {
                $method = 'register'.($macro === 'eSignUserStamps' ? 'ESignUserStamps' : 'Notify').'Macro';

                if (method_exists($this, $method)) {
                    $this->{$method}();
                }
            });
        }

        if ($this->addMenu) {
            $this->app->booted(function () {
                if (hasTable('e_documents')) {
                    ViewFacade::composer('*.includes.header', static fn (View $view) => $view->with('links', [
                        'esign' => [
                            'check' => [
                                'role' => 'admin',
                                'permission' => 'esign',
                            ],
                            'route' => route('esign.documents.index'),
                            'label' => __('esign::label.app_name'),
                        ],
                    ]));
                }
            });
        }
    }

    public function sendSigningLink(Signer $signer, Document $document): void
    {
        try {
            $mailResponse = Mail::to($signer->email)
                ->send(
                    new SendSigningLink($document, $signer)
                );

            $signer->update([
                'send_status' => $mailResponse ? SendStatus::SENT : SendStatus::NOT_SENT,
            ]);
        } catch (\Swift_TransportException|\Exception $e) {
            $signer->update([
                'send_status' => SendStatus::NOT_SENT,
            ]);
        }
    }

    private function registerESignUserStampsMacro(): void
    {
        if (Blueprint::hasMacro('eSignUserStamps')) {
            return;
        }

        Blueprint::macro('eSignUserStamps', function ($fields = [
            'restored_at', 'created_by', 'updated_by', 'deleted_by', 'restored_by',
        ]) {
            foreach (Arr::wrap($fields) as $col) {
                $method = $col === 'restored_at' ? 'timestamp' : 'unsignedBigInteger';

                /** @var Blueprint $this */
                $this->{$method}($col)->nullable();
            }
        });
    }

    private function registerNotifyMacro(): void
    {
        if (JsonResponse::hasMacro('notify')) {
            return;
        }

        JsonResponse::macro('notify', function ($message, $class = null) {
            $this->setData(
                collect($this->getData())->merge([
                    'notify' => collect([
                        'message' => $message,
                    ])->when($class, function ($c) use ($class) {
                        return $c->merge(['class' => $class]);
                    })->all(),
                ])->all()
            );

            return $this;
        });

        foreach (['success', 'error', 'warning', 'info'] as $type) {
            JsonResponse::macro($type, function ($message) use ($type) {
                return $this->notify($message, $type);
            });
        }
    }

    private function pushMenu(): void
    {
        /*
        @foreach(($links ?? []) as $key => $link)
            @if(isset($link['check']))
                @if(auth()->check() && (
                    (isset($link['check']['role']) && auth()->user()->hasRole($link['check']['role'])) ||
                    (isset($link['check']['permission']) && auth()->user()->can($link['check']['permission']))
                ))
                    <li><a href="{{ $link['route'] }}">{{ $link['label'] }}</a></li>
                @endif
            @else
                <li><a href="{{ $link['route'] }}">{{ $link['label'] }}</a></li>
            @endif
        @endforeach
         */
    }
}
