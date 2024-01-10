<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use NIIT\ESign\Enum\AttachmentType;
use NIIT\ESign\Models\DocumentAttachment;

trait HasAttachment
{
    public function attachment(?AttachmentType $type = null): MorphOne
    {
        $relation = $this->morphOne(DocumentAttachment::class, 'model', 'model_type', 'model_id');

        return $type ? $relation->where('type', $type) : $relation;
    }

    public function attachments(?AttachmentType $type = null): MorphMany
    {
        $relation = $this->morphMany(DocumentAttachment::class, 'model', 'model_type', 'model_id');

        return $type ? $relation->where('type', $type) : $relation;
    }
}
