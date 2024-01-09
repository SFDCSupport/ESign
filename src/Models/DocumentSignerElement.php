<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NIIT\ESign\Enum\ElementType;

class DocumentSignerElement extends Model
{
    protected $table = 'e_document_signer_elements';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'type' => ElementType::class,
    ];

    /**
     * @return BelongsTo<DocumentSigner, SignerElement>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: DocumentSigner::class,
            foreignKey: 'signer_id',
        );
    }
}
