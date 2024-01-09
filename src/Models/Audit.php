<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    protected $table = 'e_audits';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [];

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
     * @return BelongsTo<Signer, Audit>
     */
    public function Signer()
    {
        return $this->belongsTo(
            related: Signer::class,
            foreignKey: 'signer_id',
        );
    }
}
