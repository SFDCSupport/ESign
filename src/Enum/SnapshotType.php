<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum SnapshotType: string
{
    use EnumSupport;

    case PRE_SUBMIT = 'pre_submit';
    case POST_SUBMIT = 'post_submit';
}
