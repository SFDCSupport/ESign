<?php

namespace NIIT\ESign\Models;

use App\Traits\Userstamps;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Mail\Attachment;
use NIIT\ESign\Concerns\HasAttachment;
use NIIT\ESign\Enum\AttachmentType;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;

class Document extends Model implements Attachable, HasLocalePreference
{
    use HasAttachment, Userstamps;

    /** @var string */
    protected $table = 'e_documents';

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'file_name',
        'disk',
        'extension',
        'path',
        'template_id',
        'status',
        'notification_sequence',
    ];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'status' => DocumentStatus::class,
        'notification_sequence' => NotificationSequence::class,
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        //        'document',
    ];

    /**
     * @return HasMany<DocumentSigner>
     */
    public function signers()
    {
        return $this->hasMany(
            related: DocumentSigner::class,
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

    public function document(): MorphOne
    {
        return $this->attachment(AttachmentType::DOCUMENT)
            ->where('is_current', true)
            ->latest();
    }

    public function toMailAttachment(): Attachment
    {
        return Attachment::fromStorageDisk(
            /** @phpstan-ignore-next-line */
            $this->disk, $this->path
        )
            ->as(
                /** @phpstan-ignore-next-line */
                $this->name
            )
            ->withMime('application/pdf');
    }

    public function preferredLocale(): string
    {
        return 'en';
    }
}
