<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing;

use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Content\Item\RequestToItemMapper;
use Dreitier\Semistatic\Navigation\PathSegments;
use Dreitier\Semistatic\Navigation\Segments;
use Dreitier\Semistatic\Navigation\Trail;
use Dreitier\Semistatic\Routing\Response\ItemLookupResponse;
use Dreitier\Semistatic\Routing\Response\NotFoundResponse;
use Dreitier\Semistatic\Filesystem\Path;
use Dreitier\Semistatic\Routing\Response\RenderItemResponse;
use Dreitier\Semistatic\Routing\Response\Responsable;
use Dreitier\Semistatic\Routing\Response\SendFileResponse;
use Dreitier\Semistatic\SemistaticException;

class SemistaticRouter implements SemistaticRouterInterface
{
    public function __construct(
        public readonly string                          $rootDirectory,
        public readonly Item\RequestToItemMapperFactory $itemMapperFactory)
    {
    }

    /**
     * Find the given item
     *
     * @param RequestContext $requestContext
     * @return ItemLookupResponse
     * @throws SemistaticException
     */
    public function lookup(RequestContext $requestContext): ItemLookupResponse
    {
        // based upon the current URI and mountpoint, split each slug
        $uriSegmentsLeft = array_filter(explode('/', $requestContext->uriRelativeToShelfRoot));
        array_unshift($uriSegmentsLeft, '');

        $uriSegmentsLeft = Segments::of($uriSegmentsLeft)->moveToBegin();

        $requestToIteMapper = $this->itemMapperFactory->create($requestContext);

        $trail = (new PathSegments($this->absoluteShelfPath(), $uriSegmentsLeft->capacity()))
            ->moveToBegin();
        $item = $this->resolveItemHierarchy($requestToIteMapper, $trail, $uriSegmentsLeft);

        return new ItemLookupResponse($item, $uriSegmentsLeft, $trail);
    }

    public function render(RequestContext $requestContext): mixed
    {
        return $this->route($requestContext)->respond();
    }

    public function route(RequestContext $requestContext): Responsable
    {
        try {
            $itemLookupResponse = $this->lookup($requestContext);
            $item = $itemLookupResponse->item;
            $trail = $itemLookupResponse->trail;

            // node/directory item
            if ($item) {
                return new RenderItemResponse($item);
            }

            // some attachment inside a node/directory
            if (!$trail->isLast()) {
                if ($sendFile = SendFileResponse::mayProcess($trail->current(), $itemLookupResponse->uriSegmentsLeft->joinCurrentToEnd('/'))) {
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
        return base_path($this->rootDirectory);
    }

    /**
     * Find recursively each item from left (root) to right (leaf)
     *
     * @param RequestToItemMapper $requestToItemMapper
     * @param PathSegments $itemsInPath
     * @param Segments $slugs
     * @return Item|null
     * @throws SemistaticException
     */
    private function resolveItemHierarchy(RequestToItemMapper $requestToItemMapper, PathSegments $itemsInPath, Segments $slugs): ?Item
    {
        $absolutePath = $itemsInPath->absolutePath();

        if (!file_exists($absolutePath)) {
            throw new SemistaticException("Path " . $itemsInPath->join($itemsInPath->beginToCurrent()) . " does not exist");
        }

        $item = $requestToItemMapper->map(
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

                return $this->resolveItemHierarchy($requestToItemMapper,
                    $itemsInPath,
                    $slugs
                );
            }
        }

        return null;
    }
}