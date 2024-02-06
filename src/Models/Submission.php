<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    /** @var string */
    protected $table = 'e_submissions';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id', 'document_id', 'signer_id',
        'signer_element_id', 'data',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [];

    /**
     * @var array<int,string>
     */
    protected static array $stampingColumns = [
        'restored_at',
    ];

    /**
     * @return BelongsTo<Document, Submission>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id'
        );
    }

    /**
     * @return BelongsTo<Signer, Submission>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: Signer::class,
            foreignKey: 'signer_id'
        );
    }

    /**
     * @return BelongsTo<SignerElement, Submission>
     */
    public function signerElement()
    {
        return $this->belongsTo(
            related: SignerElement::class,
            foreignKey: 'signer_element_id'
        );
    }
}
