<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum AttachmentType: string
{
    use EnumSupport;

    case DOCUMENT = 'document';
    case SIGNER_ELEMENT = 'signer_element';
    case OTHER = 'other';
}