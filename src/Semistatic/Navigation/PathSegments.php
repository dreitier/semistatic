<?php

namespace Dreitier\Semistatic\Navigation;

use Dreitier\Semistatic\Content\Item;
use Illuminate\Support\Arr;

class PathSegments extends Segments
{
    public function __construct(public readonly string $root, int|array $sizeOrArray)
    {
        parent::__construct($sizeOrArray);
    }

    private ?\Closure $segmentConverter = null;

    public function withSegmentConverter(?\Closure $converter): PathSegments
    {
        $this->segmentConverter = $converter;
        return $this;
    }

    public function join(array $elements, string $separator = '/'): string
    {
        $useSegmentConverter = $this->segmentConverter ?? fn($item) => ($item instanceof Item) ? $item->info->toLocalSlug() : ($item ?? '');
        $elements = Arr::map($elements, $useSegmentConverter);
        return Arr::join($elements, $separator);
    }

    protected function joinWithRoot(array $elements, string $separator = '/'): string
    {
        return $this->join(array_merge([$this->root], $elements), $separator);
    }

    protected function create(int|array $sizeOrArray): PathSegments
    {
        return (new static($this->root, $sizeOrArray))
            ->withSegmentConverter($this->segmentConverter);
    }

    public function absolutePath(string $separator = '/'): string
    {
        return $this->joinWithRoot($this->beginToCurrent(), $separator);
    }

    public function joinAllWithRoot(string $separator = '/'): string
    {
        return $this->joinWithRoot($this->segments, $separator);
    }
}