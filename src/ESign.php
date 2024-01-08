<?php

namespace NIIT\ESign;

use Illuminate\Database\Schema\Blueprint;

class ESign
{
    public function registerUserStampsMacro()
    {
        Blueprint::macro('userStamps', function ($fields = [
            'created_by', 'updated_by', 'deleted_by',
        ]) {
            foreach ($fields as $col) {
                $this->unsignedBigInteger($col)->nullable();
            }
        });
    }
}
