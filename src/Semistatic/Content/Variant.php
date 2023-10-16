<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content;

use Dreitier\Semistatic\Filesystem\VariantFileInfo;

class Variant
{
    public function __construct(
        public readonly VariantFileInfo $info,
        public readonly ContentProvider $content,
        public readonly Meta            $meta,
    )
    {
    }

    public static function of(VariantFileInfo $info,
                              ContentProvider $content,
                              Meta            $meta)
    {
        return new static($info, $content, $meta);
    }
}
