<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum NotificationSequence: string
{
    use EnumSupport;

    case SYNC = 'sync';
    case ASYNC = 'async';
}
