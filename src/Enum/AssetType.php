<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum AssetType: string
{
    use EnumSupport;

    case DOCUMENT = 'document';
    case SIGNER_ELEMENT = 'signer_element';
    case SIGNER_SNAPSHOT = 'signer_snapshot';
}
