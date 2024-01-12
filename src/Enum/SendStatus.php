<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum SendStatus: string
{
    use EnumSupport;

    case NOT_SENT = 'not_sent';
    case SENT = 'sent';
}
