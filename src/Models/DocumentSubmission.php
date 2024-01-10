<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSubmission extends Model
{
    /** @var string */
    protected $table = 'e_document_submissions';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [];

    /**
     * @return BelongsTo<Document, DocumentSubmission>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id'
        );
    }

    /**
     * @return BelongsTo<DocumentSigner, DocumentSubmission>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: DocumentSigner::class,
            foreignKey: 'signer_id'
        );
    }

    /**
     * @return BelongsTo<DocumentSignerElement, DocumentSubmission>
     */
    public function signerElement()
    {
        return $this->belongsTo(
            related: DocumentSignerElement::class,
            foreignKey: 'signer_element_id'
        );
    }
}
