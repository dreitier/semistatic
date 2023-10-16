<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Navigation;

use Dreitier\Semistatic\Content\Item;
use Illuminate\Support\Arr;

/**
 * @deprecated
 */
class Trail
{
    /**
     * @param Item[] $segments
     */
    public function __construct(public array $segments = [])
    {
    }

    public function push(Item $item)
    {
        $this->segments[] = $item;
        return $this;
    }

    public function last(): ?Item
    {
        if (sizeof($this->segments) > 0) {
            return $this->segments[sizeof($this->segments) - 1];
        }

        return null;
    }

    public function insertFirst(Item $item)
    {
        array_unshift($this->segments, $item);
        return $this;
    }

    public function toLocalSegments(callable $slugResolver = null): array
    {
        if (!$slugResolver) {
            $slugResolver = fn($item) => $item->info->toLocalSlug();
        }

        // use item's (primary) slug to find local path
        return Arr::map($this->segments, $slugResolver);
    }

    public function join(string $separator = '.', callable $slugResolver = null): string
    {
        // use item's (primary) slug to find local path
        return Arr::join(static::toLocalSegments($slugResolver), $separator);
    }

    public function directory(): string
    {
        return $this->join('/');
    }
}
