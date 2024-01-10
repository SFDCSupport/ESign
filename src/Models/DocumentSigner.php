<?php

namespace NIIT\ESign\Models;

use App\Traits\Userstamps;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NIIT\ESign\Enum\SignerStatus;

class DocumentSigner extends Model
{
    use Userstamps;

    /** @var string */
    protected $table = 'e_document_signers';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'status' => SignerStatus::class,
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
