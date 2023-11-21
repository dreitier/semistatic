<?php

namespace Dreitier\Semistatic\Routing\Response;

use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Navigation\Segments;

class ItemLookupResponse implements Responsable
{
    public function __construct(
        public readonly ?Item    $item,
        public readonly Segments $uriSegmentsLeft,
        public readonly Segments $trail,
    )
    {
    }

    public function respond(): mixed
    {
        return $this->item;
    }
}