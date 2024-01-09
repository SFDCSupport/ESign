<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NIIT\ESign\Enum\MailStatus;
use NIIT\ESign\Enum\SignerStatus;

class Signer extends Model
{
    protected $table = 'e_signers';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'mail_status' => MailStatus::class,
        'status' => SignerStatus::class,
    ];

    /**
     * @return BelongsTo<Document, Signer>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id',
        );
    }

    /**
     * @return HasMany<SignerElement>
     */
    public function elements()
    {
        return $this->hasMany(
            related: SignerElement::class,
            foreignKey: 'signer_id'
        );
    }
}
