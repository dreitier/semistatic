<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Item;

use Dreitier\Semistatic\Content\Variant\Factory as VariantFactory;
use Dreitier\Semistatic\Filesystem\Path;
use Dreitier\Semistatic\Filesystem\PathInfoParser;
use Dreitier\Semistatic\Routing\RequestContext;

class RequestToItemMapperFactory
{
    private PathInfoParser $pathInfoParser;

    public function __construct(
        ?PathInfoParser $pathInfoParser = null,
        array           $variantFactories = [],
    )
    {
        $this->pathInfoParser = $pathInfoParser ?? new PathInfoParser();
    }

    public function add(VariantFactory $variantFactory)
    {
        $this->variantFactories[] = $variantFactory;
    }

    public function create(RequestContext $requestContext): RequestToItemMapper
    {
        return new RequestToItemMapper($this->variantFactories, $this->pathInfoParser, $requestContext);
    }
}