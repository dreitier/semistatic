<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Meta;

class ArrayValue extends MetaValue
{
    public function __toString(): string
    {
        $use = [];

        if (is_array($this->converted)) {
            $use = $this->converted;
        } elseif (is_string($this->converted)) {
            $use = [$this->converted];
        }

        return implode(", ", $use);;
    }
}

