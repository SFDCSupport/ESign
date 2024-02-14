<?php

namespace NIIT\ESign\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use NIIT\ESign\Concerns\HasAsset;
use NIIT\ESign\Enum\AssetType;
use NIIT\ESign\Enum\ElementType;

class SignerElement extends Model
{
    use HasAsset;

    /** @var string */
    protected $table = 'e_signer_elements';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id', 'signer_id', 'document_id',
        'type', 'text',
        'page_index', 'page_width', 'page_height',
        'left', 'top', 'width', 'height',
        'position', 'is_required',
        'data', 'submitted_at',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'type' => ElementType::class,
        'page_index' => 'integer',
        'page_width' => 'double',
        'page_height' => 'double',
        'height' => 'double',
        'width' => 'double',
        'top' => 'double',
        'left' => 'double',
        'is_required' => 'boolean',
        'submitted_at' => 'timestamp',
    ];

    /**
     * @return BelongsTo<Document, SignerElement>
     */
    public function document()
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id',
        );
    }

    /**
     * @return BelongsTo<Signer, SignerElement>
     */
    public function signer()
    {
        return $this->belongsTo(
            related: Signer::class,
            foreignKey: 'signer_id',
        );
    }

    public function attachedData(): MorphOne
    {
        return $this->asset(AssetType::SIGNER_ELEMENT);
    }

    public function position(): Attribute
    {
        return new Attribute(
            set: function (?string $value, array $attributes) {
                if ($value) {
                    return $value;
                }

                if (! isset($attributes['document_id'], $attributes['signer_id'])) {
                    return 1;
                }

                $maxPriority = SignerElement::where([
                    'signer_id' => $attributes['signer_id'],
                    'document_id' => $attributes['document_id'],
                ])->max('position') ?? 0;

                return $maxPriority + 1;
            },
        );
    }

    public function getUploadPath(): string
    {
        $loadedModel = $this->loadMissing('signer.document');

        return esignUploadPath('signer_element', [
            'document' => $loadedModel->document->id,
            'signer' => $this->signer->id,
            'element' => $this->id,
        ]);
    }
}
