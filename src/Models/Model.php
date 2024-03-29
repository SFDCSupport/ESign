<?php

namespace NIIT\ESign\Models;

use App\Traits\RevisionableTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Base;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
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
            $array = Arr::wrap($parameters);

            return $endsWithIs
                ? in_array($this->{$column}, $array, true)
                : ! in_array($this->{$column}, $array, true);
        }

        return parent::__call($method, $parameters);
    }
}
