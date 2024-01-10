<?php

namespace NIIT\ESign;

use Illuminate\Database\Schema\Blueprint;

class ESign
{
    public function t()
    {
        dd('dfdfg');
    }

    public function registerUserStampsMacro(): void
    {
        Blueprint::macro('userStamps', function ($fields = [
            'created_by', 'updated_by', 'deleted_by',
        ]) {
            foreach ($fields as $col) {
                /** @var Blueprint $this */
                $this->unsignedBigInteger($col)->nullable();
            }
        });
    }
}
