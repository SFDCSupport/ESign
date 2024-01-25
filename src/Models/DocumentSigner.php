<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
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
        'email', 'label', 'url',
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

    public function url(): Attribute
    {
        return new Attribute(
            set: fn (?string $value) => $value ?? Str::orderedUuid(),
        );
    }

    public function position(): Attribute
    {
        return new Attribute(
            set: function (?string $value, array $attributes) {
                if ($value) {
                    return $value;
                }

                if (! isset($attributes['document_id'])) {
                    return 1;
                }

                $maxPriority = DocumentSigner::where('document_id', $attributes['document_id'])
                    ->max('position') ?? 0;

                return $maxPriority + 1;
            },
        );
    }
}
