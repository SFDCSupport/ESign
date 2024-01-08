<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum DocumentStatus: string
{
    use EnumSupport;

    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVE = 'archive';
}
