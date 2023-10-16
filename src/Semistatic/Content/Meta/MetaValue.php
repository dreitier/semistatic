<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Meta;

class MetaValue
{
    protected mixed $converted = null;

    public function __construct(public readonly mixed $raw = null, public readonly mixed $default = null)
    {
        $this->converted = $raw;
    }

    public function isPresent(): bool
    {
        return $this->converted !== null;
    }

    public function __toString(): string
    {
        return $this->converted != null ? (string)$this->converted : '';
    }

    public static function empty(): MetaValue
    {
        return new static(null, null);
    }
}
