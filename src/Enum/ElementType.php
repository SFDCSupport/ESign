<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum ElementType: string
{
    use EnumSupport;

    case SIGNATURE_PAD = 'signature_pad';
    case TEXT = 'text';
    case DATE = 'date';
    case TIME = 'time';
    case TIMESTAMP = 'timestamp';
}
