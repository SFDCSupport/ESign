<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use NIIT\ESign\Enum\AssetType;
use NIIT\ESign\Models\Asset;

trait HasAsset
{
    public function asset(?AssetType $type = null): MorphOne
    {
        $relation = $this->morphOne(Asset::class, 'model', 'model_type', 'model_id');

        return $type ? $relation->where('type', $type) : $relation;
    }

    public function assets(?AssetType $type = null): MorphMany
    {
        $relation = $this->morphMany(Asset::class, 'model', 'model_type', 'model_id');

        return $type ? $relation->where('type', $type) : $relation;
    }
}
