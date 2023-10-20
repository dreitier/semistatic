<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing;

use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Content\Item\RequestToItemMapper;
use Dreitier\Semistatic\Navigation\PathSegments;
use Dreitier\Semistatic\Navigation\Segments;
use Dreitier\Semistatic\Navigation\Trail;
use Dreitier\Semistatic\Routing\Response\NotFoundResponse;
use Dreitier\Semistatic\Filesystem\Path;
use Dreitier\Semistatic\Routing\Response\RenderItemResponse;
use Dreitier\Semistatic\Routing\Response\Responsable;
use Dreitier\Semistatic\Routing\Response\SendFileResponse;
use Dreitier\Semistatic\SemistaticException;
use Dreitier\Semistatic\Shelf;

class SemistaticRouter implements SemistaticRouterInterface
{
    public function __construct(public readonly Shelf $shelf, public readonly RequestToItemMapper $itemMapper)
    {
    }

    public function route(RequestContext $requestContext): Responsable
    {
        // based upon the current URI and mountpoint, split each slug
        $slugsLeft = array_filter(explode('/', $requestContext->uriRelativeToShelfRoot));
        array_unshift($slugsLeft, '');

        $slugsLeft = Segments::of($slugsLeft)->moveToBegin();

        try {
            $trail = (new PathSegments($this->absoluteShelfPath(), $slugsLeft->capacity()))
                ->moveToBegin();
            $item = $this->resolveItemHierarchy($requestContext, $trail, $slugsLeft);

            // node/directory item
            if ($item) {
                return new RenderItemResponse($item);
            }

            // some attachment inside a node/directory
            if (!$trail->isLast()) {
                if ($sendFile = SendFileResponse::mayProcess($trail->current(), $slugsLeft->joinCurrentToEnd('/'))) {
                    return $sendFile;
                }
            }
        } catch (\Exception $e) {
            throw new SemistaticException("Unable to process semistatic request", 0, $e);
        }

        return new NotFoundResponse($requestContext);
    }

    public function absoluteShelfPath(): string
    {
        return base_path($this->shelf->rootDirectory);
    }

    /**
     * Find recursively each item from left (root) to right (leaf)
     *
     * @param RequestContext $requestContext
     * @param PathSegments $itemsInPath
     * @param Segments $slugs
     * @return Item|null
     * @throws SemistaticException
     */
    private function resolveItemHierarchy(RequestContext $requestContext, PathSegments $itemsInPath, Segments $slugs): ?Item
    {
        $absolutePath = $itemsInPath->absolutePath();

        if (!file_exists($absolutePath)) {
            throw new SemistaticException("Path " . $itemsInPath->join($itemsInPath->beginToCurrent()) ."does not exist");
        }

        $item = $this->itemMapper->map(
            $requestContext,
            $itemsInPath,
        );

        // nothing more to process
        if ($slugs->isLast()) {
            return $item;
        }

        // move to next slug
        $nextSlug = $slugs->next();

        foreach ($item->children() as $child) {
            if ($child->hasSlug($nextSlug)) {
                $itemsInPath->push($child);

                return $this->resolveItemHierarchy($requestContext,
                    $itemsInPath,
                    $slugs
                );
            }
        }

        return null;
    }
}