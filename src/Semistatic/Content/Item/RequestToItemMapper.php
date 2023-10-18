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
    private array $variantFactories = [];
    private PathInfoParser $pathInfoParser;

    public function add(VariantFactory $variantFactory, ?PathInfoParser $pathInfoParser = null)
    {
        $this->variantFactories[] = $variantFactory;
        $this->pathInfoParser = $pathInfoParser ?? new PathInfoParser();
    }

    public function map(RequestContext $requestContext, PathSegments $itemSegments): ?Item
    {
        $absolutePath = $itemSegments->absolutePath();

        if (!file_exists($absolutePath)) {
            return null;
        }

        if (!$itemInfo = $this->pathInfoParser->fromFilename(new SplFileInfo($absolutePath))) {
            return null;
        }

        $childrenResolver = null;

        if (is_dir($absolutePath)) {
            // TODO Refactor to new method
            $childrenResolver = function () use ($requestContext, $absolutePath, $itemSegments) {
                $r = Items::create();

                foreach (new DirectoryIterator($absolutePath) as $fileInfo) {
                    if ($fileInfo->isDot()) {
                        continue;
                    }

                    $variantOrItemInfo = $this->pathInfoParser->fromFilename($fileInfo);

                    if ($variantOrItemInfo instanceof ItemDirectoryInfo) {
                        $nextPathSegment = $itemSegments->expand($fileInfo->getFilename())->moveToEnd();
                        $r->add($this->map($requestContext, $nextPathSegment));
                    }
                }

                return $r;
            };
        }

        $variants = new Variants($requestContext);

        // TODO Refactor to new method
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

        if ($variants->isEmpty()) {
            // no available content
            return null;
        }

        // late binding for parent
        $parentResolver = null;

        if (!$itemSegments->isBegin()) {
            /** @var PathSegments $parentSegment */
            $parentSegment = $itemSegments->shrink();
            $parentResolver = fn() => $this->map($requestContext, $parentSegment);
        }

        return new Item($itemInfo,
            new Hierarchy(
                parentResolver: $parentResolver,
                childrenResolver: $childrenResolver
            ),
            $variants,
            $requestContext
        );
    }
}