<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NIIT\ESign\Enum\ElementType;

class SignerElement extends Model
{
    protected $table = 'e_signer_elements';

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
     * @return BelongsTo<Signer, SignerElement>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: Signer::class,
            foreignKey: 'e_signer_id',
        );
    }
}
