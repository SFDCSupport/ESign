<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use NIIT\ESign\Concerns\HasAsset;
use NIIT\ESign\Enum\AssetType;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Events\DocumentStatusChanged;

class Document extends Model implements HasLocalePreference
{
    use HasAsset;

    /** @var string */
    protected $table = 'e_documents';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id',
        'title',
        'template_id',
        'status',
        'notification_sequence',
        'link_sent_to_all',
        'parent_id',
        'is_signed',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'status' => DocumentStatus::class,
        'notification_sequence' => NotificationSequence::class,
        'link_sent_to_all' => 'boolean',
        'is_signed' => 'boolean',
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        //        'document',
    ];

    /**
     * @return HasMany<Signer>
     */
    public function signers()
    {
        return $this->hasMany(
            related: Signer::class,
            foreignKey: 'document_id'
        );
    }

    /**
     * @return BelongsTo<Template, Document>
     */
    public function template()
    {
        return $this->belongsTo(
            related: Template::class,
            foreignKey: 'template_id',
        );
    }

    /**
     * @return BelongsTo<Document, Document>
     */
    public function parent()
    {
        return $this->belongsTo(
            related: __CLASS__,
            foreignKey: 'parent_id',
        );
    }

    /**
     * @return HasMany<Document, Document>
     */
    public function children()
    {
        return $this->hasMany(
            related: __CLASS__,
            foreignKey: 'parent_id',
        );
    }

    public function document(): MorphOne
    {
        return $this->asset(AssetType::DOCUMENT)
            ->where(
                fn ($q) => $q->whereNull('is_snapshot')
                    ->orWhere('is_snapshot', false)
            );
    }

    public function preferredLocale(): string
    {
        return 'en';
    }

    public function markAs(DocumentStatus $status, ?Signer $signer = null): void
    {
        $this->update([
            'status' => $status,
        ]);

        DocumentStatusChanged::dispatch(
            $this,
            $status,
            $signer,
        );
    }

    public function sendSigningLink(): void
    {
        $signers = $this->loadMissing(['signers' => fn ($q) => $q->where('send_status', SendStatus::NOT_SENT)->orderBy('position')])->signers;

        if (! $signers || count($signers) === 0) {
            return;
        }

        if ($this->notificationSequenceIsNot(NotificationSequence::SYNC)) {
            $signers->each(fn ($signer) => ESignFacade::sendSigningLink($signer, $this));
        }
    }

    /**
     * @return array<string, string>|string
     */
    public function getDocumentPath(bool $getBoth = false): array|string
    {
        $asset = $this->loadMissing('document')->document;

        $path = esignUploadPath('document', ['document' => $this->id]);

        return $getBoth
            ? [$asset->file_name, $path]
            : $path;
    }

    public function getDocumentUrl(): string
    {
        return FilepondAction::loadFile($this->getSignedDocumentPath(), 'view');
    }

    public function initialDocument(): Asset
    {
        if ($this->statusIsNot(DocumentStatus::COMPLETED)) {
            return $this->loadMissing('document')->document;
        }

        return $this->loadMissing([
            'signers' => fn ($q) => $q->with('preSubmitSnapshot')
                ->where('signing_status', SigningStatus::SIGNED)
                ->whereNotNull('signed_at')
                ->oldest('signed_at')
                ->first(),
        ])->signers
            ->first()
            ->preSubmitSnapshot;
    }
}
