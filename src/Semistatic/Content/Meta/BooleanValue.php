<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Meta;

class BooleanValue extends MetaValue
{
    public function __construct(...$args)
    {
        parent::__construct(... $args);

        if (is_string($this->raw)) {
            $this->converted = filter_var($this->raw, FILTER_VALIDATE_BOOLEAN);
        } elseif (is_bool($this->raw)) {
            $this->converted = $this->raw;
        } else {
            $this->converted = null;
        }
    }

    public function is(bool $value): bool
    {
        if ($this->converted !== null) {
            return $value === $this->converted;
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->converted === true ? '1' : (($this->converted === false) ? '0' : '');
    }
}
