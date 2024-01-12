<?php

namespace NIIT\ESign;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;

class ESign
{
    public function registerMacros(array $macros = ['userStamps', 'notify'])
    {
        collect($macros)->each(function ($macro) {
            if (method_exists($this, ($method = 'register'.str($macro)->title()->value().'Macro'))) {
                $this->{$method}();
            }
        });
    }

    public function registerUserStampsMacro(): void
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

    public function registerNotifyMacros(): void
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
}
