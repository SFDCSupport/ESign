<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum DocumentStatus: string
{
    use EnumSupport;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'in_active';
    case ARCHIVED = 'archived';
}
