<?php

namespace NIIT\ESign\Enum;

use NIIT\ESign\Concerns\EnumSupport;

enum ElementType: string
{
    use EnumSupport;

    case SIGNATURE_PAD = 'signature_pad';
    case TEXT = 'text';
    case DATE = 'date';
    case EMAIL = 'email';
    case TEXTAREA = 'textarea';

    public static function withIcons(bool $withText = false): array
    {
        $data = [];

        foreach (self::values() as $e) {
            $icon = self::getIcon($e);

            $data[$e] = $withText ? [
                __('esign::label.'.$e),
                $icon,
            ] : $icon;
        }

        return $data;
    }

    private static function getIcon(string $for): string
    {
        return match ($for) {
            'signature_pad' => 'fa-solid fa-signature',
            'text' => 'fa-solid fa-font',
            'date' => 'fa-regular fa-calendar',
            'email' => 'fa-solid fa-mail',
            'textarea' => '',
        };
    }
}
