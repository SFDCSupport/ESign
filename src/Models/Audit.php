<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    /** @var string */
    protected $table = 'e_audits';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id', 'document_id', 'signer_id',
        'event', 'metadata',
        'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * @return BelongsTo<Document, Audit>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id',
        );
    }

    /**
     * @return BelongsTo<DocumentSigner, Audit>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: DocumentSigner::class,
            foreignKey: 'signer_id',
        );
    }
}
