<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum MailStatus: string
{
    use EnumSupport;

    case SENT = 'sent';
    case NOT_SENT = 'not-sent';
    case FAILED = 'failed';
    case CLICKED = 'clicked';
}
