<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use NIIT\ESign\Enum\ElementType;

class DocumentSignerElement extends Model
{
    /** @var string */
    protected $table = 'e_document_signer_elements';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id', 'signer_id', 'document_id',
        'type', 'label',
        'on_page', 'left', 'top', 'scale_x', 'scale_y',
        'width', 'height', 'position',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'type' => ElementType::class,
    ];

    /**
     * @return BelongsTo<Document, DocumentSignerElement>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id',
        );
    }

    /**
     * @return BelongsTo<DocumentSigner, DocumentSignerElement>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: DocumentSigner::class,
            foreignKey: 'signer_id',
        );
    }

    /**
     * @return HasOne<DocumentSubmission>
     */
    public function submission()
    {
        return $this->hasOne(
            related: DocumentSubmission::class,
            foreignKey: 'signer_element_id'
        );
    }

    public function position(): Attribute
    {
        return new Attribute(
            set: function (?string $value, array $attributes) {
                if ($value) {
                    return $value;
                }

                if (! isset($attributes['document_id'], $attributes['signer_id'])) {
                    return 1;
                }

                $maxPriority = DocumentSignerElement::where([
                    'signer_id' => $attributes['signer_id'],
                    'document_id' => $attributes['document_id'],
                ])->max('position') ?? 0;

                return $maxPriority + 1;
            },
        );
    }
}
