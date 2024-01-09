<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum SignerStatus: string
{
    use EnumSupport;

    case OPENED = 'opened';
    case OPENED_BUT_NOT_SIGNED = 'opened_but_not_signed';
    case SIGNED = 'signed';
}
