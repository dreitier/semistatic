<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Meta;

use Carbon\Carbon;
use DateTime;

class DateValue extends MetaValue
{
    public function __construct(...$args)
    {
        parent::__construct(... $args);

        $this->converted = static::dateOrNull($this->raw ?? $this->default);
    }

    public static function dateOrNull(null|string|DateTime|DateValue $date): ?DateTime
    {
        if ($date instanceof DateValue) {
            $date = $date->raw;
        }

        if (empty($date)) {
            return null;
        }

        if ($date instanceof DateTime) {
            return $date;
        }

        return Carbon::parseFromLocale($date);
    }

    public function __toString(): string
    {
        return $this->converted?->format('Y-m-d') ?? '';
    }

    public static function empty(): DateValue
    {
        return new static(null, null);
    }
}
