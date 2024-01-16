<?php

namespace NIIT\ESign\Concerns;

use Illuminate\Support\Str;
use ReflectionClass;

trait EnumSupport
{
    public static function value(string $enumCase): string
    {
        $constants = (new ReflectionClass(static::class))->getConstants();

        return $constants[$enumCase] ?? $enumCase;
    }

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
