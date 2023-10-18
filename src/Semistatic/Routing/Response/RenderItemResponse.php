<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing\Response;

use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Routing\Context;

class RenderItemResponse implements Responsable
{
    public function __construct(public readonly Item $item)
    {
    }

    public function respond(Context $context): mixed
    {
        return $this
            ->item
            ->variants
            ->select()
            ->sortPreferred()
            ->firstOrNull()
            ->content->render($this->item);
    }
}
