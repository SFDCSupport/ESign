<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum SigningStatus: string
{
    use EnumSupport;

    case NOT_SIGNED = 'not_signed';
    case SIGNED = 'signed';
}
