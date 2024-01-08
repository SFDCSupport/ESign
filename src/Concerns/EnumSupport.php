<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Support\Str;

trait EnumSupport
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $cases = static::cases();
        $options = [];
        foreach ($cases as $case) {
            $label = $case->name;
            if (Str::contains($label, '_')) {
                $label = Str::replace('_', ' ', $label);
            }
            $options[] = [
                'value' => $case->value,
                'label' => Str::title($label),
            ];
        }

        return $options;
    }
}
