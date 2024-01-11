<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum SignerStatus: string
{
    use EnumSupport;

    case MAIL_READ = 'mail_read';
    case DOC_OPENED = 'doc_opened';
    case DOC_SIGNED = 'doc_signed';
}
