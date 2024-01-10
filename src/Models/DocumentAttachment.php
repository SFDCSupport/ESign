<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use NIIT\ESign\Enum\AttachmentType;

class DocumentAttachment extends Model
{
    /** @var string */
    protected $table = 'e_document_attachments';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'type',
        'file_name',
        'disk',
        'extension',
        'path',
        'is_current',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'is_current' => 'boolean',
        'type' => AttachmentType::class,
    ];

    public function model(): MorphTo
    {
        return $this->morphTo(
            type: 'model_type',
            id: 'model_id',
        );
    }
}
