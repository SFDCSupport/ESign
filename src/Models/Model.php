<?php

namespace NIIT\ESign\Models;

use App\Traits\RevisionableTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Base;
use Illuminate\Database\Eloquent\SoftDeletes;
use NIIT\ESign\Concerns\Auditable;

abstract class Model extends Base
{
    use Auditable, HasFactory, HasUuids, RevisionableTrait, SoftDeletes;
}
