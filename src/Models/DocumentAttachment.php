<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'id',
        'model_type',
        'model_id',
        'type',
        'disk',
        'bucket',
        'file_name',
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

    public function url(): Attribute
    {
        return new Attribute(
            get: fn (?string $value, array $attributes) => FilepondAction::loadFile($attributes['path'], 'view'),
        );
    }
}
