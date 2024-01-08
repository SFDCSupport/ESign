<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum MailEvent: string
{
    use EnumSupport;

    case SENT = 'sent';
    case NOT_SENT = 'not_sent';
    case CLICKED = 'clicked';
}
