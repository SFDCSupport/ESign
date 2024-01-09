<?php

namespace NIIT\ESign\Models;

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Mail\Attachment;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;

class Document extends Model implements Attachable, HasLocalePreference
{
    protected $table = 'e_documents';

    /**
     * @var array<int,string>
     */
    protected $fillable = [];

    /**
     * @var array<string,string>
     */
    protected $casts = [
        'status' => DocumentStatus::class,
        'notification_sequence' => NotificationSequence::class,
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
