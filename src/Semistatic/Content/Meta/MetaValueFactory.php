<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Meta;
class MetaValueFactory
{
    const ARRAY = 'array';
    const DATE = 'date';
    const BOOL = 'bool';
    const BOOLEAN = 'boolean';
    const STRING = 'string';

    public static function create(mixed $raw, mixed $default = null, ?string $type = 'string')
    {
        switch ($type) {
            case static::ARRAY:
                return (new ArrayValue($raw, $default));
            case static::DATE:
                return (new DateValue($raw, $default));
            case static::BOOL:
            case static::BOOLEAN:
                return (new BooleanValue($raw, $default));
            default:
                return (new MetaValue($raw, $default));
        }
    }
}
