<?php

namespace NIIT\ESign;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class ESign
{
    protected bool $addMacros = false;

    protected bool $addMenu = false;

    public function __construct(protected ?Application $app = null)
    {
        $this->app = $app ?? app();
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
            collect(['userStamps', 'notify'])->each(function ($macro) {
                if (method_exists($this, ($method = 'register'.str($macro)->title()->value().'Macro'))) {
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

    private function registerUserStampsMacro(): void
    {
        if (! Blueprint::hasMacro('userStamps')) {
            return;
        }

        Blueprint::macro('userStamps', function ($fields = [
            'created_by', 'updated_by', 'deleted_by',
        ]) {
            foreach ($fields as $col) {
                /** @var Blueprint $this */
                $this->unsignedBigInteger($col)->nullable();
            }
        });
    }

    private function registerNotifyMacros(): void
    {
        if (! JsonResponse::hasMacro('notify')) {
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
