<?php

namespace NIIT\ESign\Models;

use Illuminate\Contracts\Mail\Attachable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Mail\Attachment;

class ESignDocument extends Model implements Attachable, HasLocalePreference
{
    protected $table = 'e_documents';

    public function toMailAttachment(): Attachment
    {
        return Attachment::fromStorageDisk($this->disk, $this->path)
            ->as($this->name)
            ->withMime('application/pdf');
    }

    public function preferredLocale(): string
    {
        return 'en';
    }
}
