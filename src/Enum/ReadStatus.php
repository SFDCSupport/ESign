<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum ReadStatus: string
{
    use EnumSupport;

    case NOT_OPENED = 'not_opened';
    case OPENED = 'opened';
}
