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

    public function document(): BelongsTo
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'e_document_id',
        );
    }

    public function elements(): HasMany
    {
        return $this->hasMany(
            related: SignerElement::class,
            foreignKey: 'e_signer_id'
        );
    }
}
