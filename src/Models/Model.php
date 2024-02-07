<?php

namespace NIIT\ESign\Models;

use App\Traits\RevisionableTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Base;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use NIIT\ESign\Concerns\Auditable;
use NIIT\ESign\Concerns\HasUserStamps;

abstract class Model extends Base
{
    use Auditable, HasFactory, HasUserStamps, HasUuids, RevisionableTrait, SoftDeletes;

    public function __call($method, $parameters)
    {
        $column = null;
        $strMethod = Str::of($method);

        if ($endsWithIs = $strMethod->endsWith('Is')) {
            $column = $strMethod->snake()->beforeLast('_is');
        } elseif ($strMethod->endsWith('IsNot')) {
            $column = $strMethod->snake()->beforeLast('_is_not');
        }

        if (isset($column, $this->{$column})) {
            return $endsWithIs
                ? ($this->{$column} === $parameters[0])
                : ($this->{$column} !== $parameters[0]);
        }

        return parent::__call($method, $parameters);
    }
}
