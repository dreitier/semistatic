<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Filesystem;

class ItemDirectoryInfo
{
    public function __construct(
        public readonly string $absolutePath,
        public readonly string $slug,
        public readonly ?int   $order = null)
    {
    }

    public function toLocalSlug(): string
    {
        return basename($this->absolutePath);
    }

    public function __toString()
    {
        return "ItemDirectoryInfo={absolutePath='" . $this->absolutePath . '"}';
    }
}
