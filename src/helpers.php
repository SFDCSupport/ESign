<?php

function convertPHPSizeToBytes($sSize)
{
    $sSuffix = strtoupper(substr($sSize, -1));
    if (! in_array($sSuffix, ['P', 'T', 'G', 'M', 'K'])) {
        return (int) $sSize;
    }
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P': $iValue *= 1024;
        case 'T': $iValue *= 1024;
        case 'G': $iValue *= 1024;
        case 'M': $iValue *= 1024;
        case 'K': $iValue *= 1024;
            break;
    }

    return (int) $iValue;
}
