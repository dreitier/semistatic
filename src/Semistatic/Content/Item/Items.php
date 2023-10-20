<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Item;

use ArrayIterator;
use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Content\Variant\Selector;
use Dreitier\Semistatic\Content\Variant\SelectorMode;
use IteratorAggregate;
use Traversable;

class Items implements IteratorAggregate
{
    private array $items = [];

    private function __construct(array|Items $items)
    {
        $this->items = is_array($items) ? $items : $items->items;
    }

    public function add(?Item $item)
    {
        if ($item !== null) {
            $this->items[] = $item;
        }

        return $this;
    }

    public function size(): int {
        return sizeof($this->items);
    }

    public static function create(): Items
    {
        return new static([]);
    }

    public function get(): array
    {
        return $this->items;
    }

    public static function toSortFunction(string|callable $sorting): callable
    {
        if (is_string($sorting)) {
            switch ($sorting) {
                case 'date':
                case 'order':
                default:
                    return function ($a, $b) {
                        return ($a->info->order ?? 0) >= ($b->info->order ?? 0);
                    };
            }
        }

        return $sorting;
    }

    public function sortBy(string|callable $sorting = 'default', string $direction = 'asc'): Items
    {
        $items = $this->items;
        usort($items, static::toSortFunction($sorting));

        if ($direction == 'desc') {
            $items = array_reverse($items);
        }

        return new Items($items);
    }

    public function filter(?string $flavor): Items
    {
        $selector = new Selector(flavor: $flavor);

        $items = array_filter($this->items, fn(Item $a) => $a->matches($selector));

        return new Items($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
