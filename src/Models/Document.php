<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use NIIT\ESign\Concerns\HasAttachment;
use NIIT\ESign\Enum\AttachmentType;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\ESignFacade;
use NIIT\ESign\Events\DocumentStatusChanged;

class Document extends Model implements HasLocalePreference
{
    use HasAttachment;

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
        return $this->attachment(AttachmentType::DOCUMENT)
            ->where('is_current', true)
            ->latest();
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
    public function getSignedDocumentPath(bool $getBoth = false): array|string
    {
        $fileName = $this->loadMissing('document')->document->file_name;

        $signedFileName = (
            pathinfo($fileName, PATHINFO_FILENAME).
            '_signed.'.
            pathinfo($fileName, PATHINFO_EXTENSION)
        );

        $path = esignUploadPath('document', ['id' => $this->id]).'/'.$signedFileName;

        return $getBoth
            ? [$signedFileName, $path]
            : $path;
    }

    public function getSignedDocumentUrl(): string
    {
        return FilepondAction::loadFile($this->getSignedDocumentPath(), 'view');
    }
}
