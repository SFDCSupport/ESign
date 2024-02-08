<?php

namespace NIIT\ESign\Models;

use App\Actions\FilepondAction;
use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Mail\Attachment as MailAttachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use NIIT\ESign\Enum\ReadStatus;
use NIIT\ESign\Enum\SendStatus;
use NIIT\ESign\Enum\SigningStatus;
use NIIT\ESign\Mail\Signer\SigningCompletedMail;

class Signer extends Model implements Attachable
{
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

    /**
     * @return HasMany<Submission>
     */
    public function submissions()
    {
        return $this->hasMany(
            related: Submission::class,
            foreignKey: 'signer_id'
        );
    }

    public function url(): Attribute
    {
        return new Attribute(
            set: fn (?string $value) => $value ?? Str::orderedUuid(),
        );
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
        $mailResponse = Mail::to($this->email)->send(new SigningCompletedMail(
            $this->loadMissing('document')->document,
            $this
        ));

        return (bool) $mailResponse;
    }

    public function toMailAttachment(): MailAttachment
    {
        return MailAttachment::fromStorageDisk(
            FilepondAction::getDisk(true),
            signerUploadPath($this).'/'.$this->document_id.'.pdf'
        );
    }
}
