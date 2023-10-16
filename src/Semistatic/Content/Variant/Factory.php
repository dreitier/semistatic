<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Variant;

use Dreitier\Semistatic\Content\Variant;
use Dreitier\Semistatic\Filesystem\VariantFileInfo;

interface Factory
{
    /**
     * Create a new Variant from a local file information
     *
     * @param VariantFileInfo $variantInfo
     * @return Variant|null
     */
    public function create(VariantFileInfo $variantInfo): ?Variant;
}
