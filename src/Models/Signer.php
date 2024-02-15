<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Attachment as MailAttachment;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NIIT\ESign\Concerns\HasAsset;
use NIIT\ESign\Enum\AssetType;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Enum\SnapshotType;
use NIIT\ESign\Mail\Signer\SendCopyMail;

class Signer extends Model implements Attachable
{
    use HasAsset, Notifiable;

    /** @var string */
    protected $table = 'e_signers';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'id', 'document_id',
        'email', 'text', 'url',
        'signing_status', 'read_status', 'send_status',
        'position', 'is_next_receiver',
        'submitted_at',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'read_status' => ReadStatus::class,
        'send_status' => SendStatus::class,
        'signing_status' => SigningStatus::class,
        'position' => 'integer',
        'is_next_receiver' => 'boolean',
        'submitted_at' => 'timestamp',
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

    public function url(): Attribute
    {
        return new Attribute(
            set: fn (?string $value) => $value ?? Str::orderedUuid(),
        );
    }

    public function snapshots(): MorphMany
    {
        return $this->assets(AssetType::SIGNER_SNAPSHOT)
            ->where('is_snapshot', true)
            ->whereIn('snapshot_type', SnapshotType::values());
    }

    public function postSubmitSnapshot(): MorphOne
    {
        return $this->asset(AssetType::SIGNER_SNAPSHOT)
            ->where('is_snapshot', true)
            ->where('snapshot_type', SnapshotType::POST_SUBMIT);
    }

    public function preSubmitSnapshot(): MorphOne
    {
        return $this->asset(AssetType::SIGNER_SNAPSHOT)
            ->where('is_snapshot', true)
            ->where('snapshot_type', SnapshotType::PRE_SUBMIT);
    }

    public function position(): Attribute
    {
        return new Attribute(
            set: function (?string $value, array $attributes) {
                if ($value) {
                    return $value;
                }

                if (! isset($attributes['document_id'])) {
                    return 1;
                }

                $maxPriority = Signer::where('document_id', $attributes['document_id'])
                    ->max('position') ?? 0;

                return $maxPriority + 1;
            },
        );
    }

    public function signingUrl(): string
    {
        return route('esign.signing.index', $this->url);
    }

    public function sendCopy(): bool
    {
        $mailResponse = Mail::to($this->email)->send(new SendCopyMail(
            $this->loadMissing('document')->document,
            $this
        ));

        return (bool) $mailResponse;
    }

    public function toMailAttachment(): MailAttachment
    {
        return MailAttachment::fromStorageDisk(
            $this->loadMissing('document')->document->document->disk ?? FilepondAction::getDisk(true),
            $this->getSignedDocumentPath()
        );
    }

    public function getSignedDocumentPath(): string
    {
        return $this->getUploadPath(true);
    }

    public function getSignedDocumentUrl(): string
    {
        return FilepondAction::loadFile($this->getSignedDocumentPath(), 'view');
    }

    public function getUploadPath(bool $getPath = false): string
    {
        /** @var Document $document */
        $document = $this->loadMissing('document.document')->document;

        $path = ($isCompleted = $document->statusIs(DocumentStatus::COMPLETED))
            ? esignUploadPath('document', [
                'document' => $document->id,
            ])
            : esignUploadPath('signer_snapshot', [
                'document' => $document->id,
                'signer' => $this->id,
            ]);

        return $getPath
            ? ($path.'/'.($isCompleted ? '' : (SnapshotType::POST_SUBMIT->value.'-')).$document->document->file_name)
            : $path;
    }

    public function createSnapshot(Asset $asset, UploadedFile $file): Asset
    {
        $postSubmitSnapshot = null;

        $snapshotPath = esignUploadPath('signer_snapshot', [
            'document' => $this->document->id,
            'signer' => $this->id,
        ]);

        foreach (SnapshotType::values() as $snapshotType) {
            $filename = $snapshotType.'-'.$asset->file_name;
            $path = $snapshotPath.'/'.$filename;
            $isPostSubmit = ($snapshotType === 'post_submit');

            if (! $isPostSubmit) {
                Storage::disk($asset->disk)->copy(
                    $asset->path.'/'.$asset->file_name,
                    $path
                );
            } else {
                $path = $file->storeAs(
                    $snapshotPath,
                    $filename,
                    $asset->disk
                );
            }

            /** @var Asset $newAsset */
            $newAsset = $this->snapshots()->updateOrCreate([
                'type' => AssetType::SIGNER_SNAPSHOT,
                'snapshot_type' => $snapshotType,
                'is_snapshot' => true,
            ], [
                'path' => $snapshotPath,
                'file_name' => $filename,
                'bucket' => $asset->bucket,
                'disk' => $asset->disk,
                'extension' => $asset->extension,
            ]);

            if ($isPostSubmit) {
                $postSubmitSnapshot = $newAsset;
            }
        }

        return $postSubmitSnapshot;
    }
}
