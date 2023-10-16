<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Filesystem;


class VariantFileInfo
{
    public function __construct(
        public readonly string $absolutePath,
        public string          $flavor,
        public string          $extension,
        public ?string         $language = null,
    )
    {
    }

    public function __toString()
    {
        return "VariantFileInfo={absolutePath='" . $this->absolutePath . '"}';
    }
}
