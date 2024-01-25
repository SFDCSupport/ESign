<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Enum\SigningStatus;

class DocumentSigner extends Model
{
    /** @var string */
    protected $table = 'e_document_signers';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id', 'document_id',
        'email', 'label',
        'signing_status', 'read_status', 'send_status',
        'position',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'read_status' => ReadStatus::class,
        'send_status' => SendStatus::class,
        'signing_status' => SigningStatus::class,
    ];

    /**
     * @return BelongsTo<Document, DocumentSigner>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id',
        );
    }

    /**
     * @return HasMany<DocumentSignerElement>
     */
    public function elements()
    {
        return $this->hasMany(
            related: DocumentSignerElement::class,
            foreignKey: 'signer_id'
        );
    }

    /**
     * @return HasMany<DocumentSubmission>
     */
    public function submissions()
    {
        return $this->hasMany(
            related: DocumentSubmission::class,
            foreignKey: 'signer_id'
        );
    }
}
