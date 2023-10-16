<?php

namespace Dreitier\Semistatic\Navigation;

use Illuminate\Support\Arr;

class Segments
{
    protected array $segments = [];
    protected int $capacity = 0;
    protected int $pointer = 0;

    public function __construct(int|array $sizeOrArray)
    {
        if (is_int($sizeOrArray)) {
            $sizeOrArray = array_fill(0, $sizeOrArray, null);
        }

        if (sizeof($sizeOrArray) == 0) {
            throw new \Exception("One Segment must be specified");
        }

        $this->segments = $sizeOrArray;
        $this->capacity = sizeof($sizeOrArray);
    }

    public static function of(int|array $sizeOrArray): Segments
    {
        return new static($sizeOrArray);
    }

    protected function create(int|array $sizeOrArray)
    {
        return new static($sizeOrArray);
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function set(int $index, mixed $value): Segments
    {
        $maxIndex = $this->capacity - 1;

        if ($index > $maxIndex) {
            throw new \Exception("Out of bounds: (idx:$index,max:$maxIndex)");
        }

        $this->segments[$index] = $value;
        return $this;
    }

    public function push(mixed $value): Segments
    {
        $pointer = $this->pointer + 1;
        $this->set($pointer, $value);
        $this->pointer = $pointer;

        return $this;
    }

    public function moveToBegin(): Segments
    {
        $this->pointer = 0;
        return $this;
    }

    public function next(): mixed
    {
        if ($this->isLast()) {
            return null;
        }

        $this->pointer++;
        return $this->current();
    }

    public function isLast(): bool
    {
        return ($this->pointer + 1 >= $this->capacity);
    }

    public function isBegin(): bool
    {
        return $this->pointer == 0;
    }

    public function current(): mixed
    {
        return $this->segments[$this->pointer];
    }

    public function last(): mixed
    {
        return $this->segments[sizeof($this->segments) - 1];
    }

    public function expand(mixed $value): Segments
    {
        return $this->create(array_merge($this->beginToCurrent(), [$value]));
    }

    public function moveToEnd(): Segments
    {
        $this->pointer = sizeof($this->segments) - 1;
        return $this;
    }

    public function shrink(): Segments|false
    {
        if ($this->pointer == 0) {
            return false;
        }

        return $this->create($this->beginToCurrent(false))->moveToEnd();
    }

    protected function join(array $elements, string $separator = '/'): string
    {
        return Arr::join($elements, $separator);
    }

    public function joinCurrentToEnd(string $separator = '/'): string
    {
        return $this->join($this->currentToEnd(), $separator);
    }

    public function beginToCurrent(bool $includeCurrent = true): array
    {
        return array_slice($this->segments, 0, $this->pointer + ($includeCurrent ? 1 : 0));
    }

    public function currentToEnd(): array
    {
        return array_slice($this->segments, $this->pointer);
    }

}