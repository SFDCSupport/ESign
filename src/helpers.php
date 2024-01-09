<?php

if (! function_exists('splitLineBreak')) {
    function splitLineBreak(string $value): array|false
    {
        return preg_split('/\r\n|[\r\n]/', $value);
    }
}

if (! function_exists('splitLineBreakWithComma')) {
    function splitLineBreakWithComma(string $value): array|false
    {
        return preg_split('/\r\n|[\r\n,]+/', $value);
    }
}

if (! function_exists('esignUploadPath')) {
    function esignUploadPath(string $key, array $data = []): ?string
    {
        $path = \App\Actions\FilepondAction::getDiskPath(config("esign.upload.{$key}"));

        if (blank($path)) {
            return null;
        }

        if (! blank($data)) {
            $path = (new \App\Helper\Placeholders)->parse($path, $data);
        }

        return $path;
    }
}

if (! function_exists('convertPHPSizeToBytes')) {
    function convertPHPSizeToBytes(int|string $sSize): int
    {
        $sSuffix = strtoupper(substr((string) $sSize, -1));

        if (! in_array($sSuffix, ['P', 'T', 'G', 'M', 'K'])) {
            return (int) $sSize;
        }
        /** @var int $iValue */
        $iValue = substr((string) $sSize, 0, -1);

        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
            case 'T':
                $iValue *= 1024;
            case 'G':
                $iValue *= 1024;
            case 'M':
                $iValue *= 1024;
            case 'K':
                $iValue *= 1024;
                break;
        }

        return (int) $iValue;
    }
}
