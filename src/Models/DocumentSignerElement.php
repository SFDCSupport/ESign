<?php

namespace NIIT\ESign\Models;

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
        'on_page', 'offset_x', 'offset_y',
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
}
