<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Item;

use DirectoryIterator;
use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Content\Variant\Factory as VariantFactory;
use Dreitier\Semistatic\Content\Variant\Variants;
use Dreitier\Semistatic\Filesystem\ItemDirectoryInfo;
use Dreitier\Semistatic\Filesystem\PathInfoParser;
use Dreitier\Semistatic\Filesystem\VariantFileInfo;
use Dreitier\Semistatic\Navigation\PathSegments;
use Dreitier\Semistatic\Routing\RequestContext;
use SplFileInfo;

class RequestToItemMapper
{
    public function __construct(
        public array          $variantFactories,
        public PathInfoParser $pathInfoParser,
        public RequestContext $requestContext,
    )
    {
    }

    public function createInfoFromPath(string $absolutePath): ItemDirectoryInfo|VariantFileInfo|null
    {
        if (!file_exists($absolutePath)) {
            return null;
        }

        return $this->pathInfoParser->fromFilename(new SplFileInfo($absolutePath));
    }

    public function createVariantsFromPath(string $absolutePath): Variants
    {
        $variants = new Variants($this->requestContext);

        foreach (new DirectoryIterator($absolutePath) as $fileInfo) {
            $variant = null;

            $variantOrItemInfo = $this->pathInfoParser->fromFilename($fileInfo);

            if (!($variantOrItemInfo instanceof VariantFileInfo)) {
                continue;
            }

            foreach ($this->variantFactories as $factory) {
                if (null !== ($variant = $factory->create($variantOrItemInfo))) {
                    break;
                }
            }

            if (!$variant) {
                continue;
            }

            $variants->add($variant);
        }

        return $variants;
    }

    public function map(PathSegments $itemSegments): ?Item
    {
        $absolutePath = $itemSegments->absolutePath();

        if (!$itemInfo = $this->createInfoFromPath($absolutePath)) {
            return null;
        }

        $childrenResolver = null;

        if (is_dir($absolutePath)) {
            // TODO Refactor to new method
            $childrenResolver = function () use ($absolutePath, $itemSegments) {
                $r = Items::create();

                foreach (new DirectoryIterator($absolutePath) as $fileInfo) {
                    if ($fileInfo->isDot()) {
                        continue;
                    }

                    $variantOrItemInfo = $this->pathInfoParser->fromFilename($fileInfo);

                    if ($variantOrItemInfo instanceof ItemDirectoryInfo) {
                        $nextPathSegment = $itemSegments->expand($fileInfo->getFilename())->moveToEnd();
                        $r->add($this->map($nextPathSegment));
                    }
                }

                return $r;
            };
        }

        $variants = $this->createVariantsFromPath($absolutePath);

        if ($variants->isEmpty()) {
            // no available content
            return null;
        }

        // late binding for parent
        $parentResolver = null;

        if (!$itemSegments->isBegin()) {
            /** @var PathSegments $parentSegment */
            $parentSegment = $itemSegments->shrink();
            $parentResolver = fn() => $this->map($parentSegment);
        }

        return new Item($itemInfo,
            new Hierarchy(
                parentResolver: $parentResolver,
                childrenResolver: $childrenResolver
            ),
            $variants,
            $this->requestContext
        );
    }
}