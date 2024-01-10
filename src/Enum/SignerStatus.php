<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum SignerStatus: string
{
    use EnumSupport;

    case MAIL_RECEIVED = 'mail_received';
    case MAIL_NOT_RECEIVED = 'mail_not_received';
    case MAIL_READ = 'mail_read';
    case DOC_OPENED = 'doc_opened';
    case DOC_OPENED_BUT_NOT_SIGNED = 'doc_opened_but_not_signed';
    case DOC_SIGNED = 'doc_signed';
}
