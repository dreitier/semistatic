<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content;

interface ContentProvider
{
    public function render(Item $item): mixed;

    public function getRaw(): ?string;
}

