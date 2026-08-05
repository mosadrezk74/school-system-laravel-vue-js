<?php

namespace Modules\Core\Abstracts;

abstract class BaseEnum
{
    protected static array $labels = [];

    public static function all(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }

    public static function values(): array
    {
        return array_values(static::all());
    }

    public static function keys(): array
    {
        return array_keys(static::all());
    }

    public static function label(string $value): string
    {
        return static::$labels[$value] ?? $value;
    }

    public static function labels(): array
    {
        $labels = [];
        foreach (static::all() as $key => $value) {
            $labels[$value] = static::label($value);
        }
        return $labels;
    }

    public static function options(): array
    {
        $options = [];
        foreach (static::all() as $key => $value) {
            $options[] = [
                'value' => $value,
                'label' => static::label($value),
                'key' => $key,
            ];
        }
        return $options;
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, static::values());
    }

    public static function isValidKey(string $key): bool
    {
        return array_key_exists($key, static::all());
    }

    public static function random(): string
    {
        $values = static::values();
        return $values[array_rand($values)];
    }
}
