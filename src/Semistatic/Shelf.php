<?php
declare(strict_types=1);

namespace Dreitier\Semistatic;

use Dreitier\Semistatic\Content\Item\RequestToItemMapper;
use Dreitier\Semistatic\Routing\Context;
use Dreitier\Semistatic\Routing\RequestContext;
use Dreitier\Semistatic\Routing\Router;
use Dreitier\Semistatic\Routing\SemistaticRouter;
use Dreitier\Semistatic\Routing\SemistaticRouterInterface;

/**
 * Abstracts away one "package" or directory of content
 */
class Shelf
{
    private SemistaticRouterInterface $router;
    private RequestToItemMapper $requestToItemMapper;

    public function __construct(public readonly string $rootDirectory,
                                public readonly string $defaultLanguage = 'en',
                                ?SemistaticRouterInterface                $customRouter = null,
                                ?RequestToItemMapper   $itemMapper = null,

    )
    {
        $this->requestToItemMapper = $itemMapper ?? new RequestToItemMapper();
        $this->router = $customRouter ?? new SemistaticRouter($this, $this->requestToItemMapper);
    }

    public function render(RequestContext $requestContext): mixed
    {
        $ctx = new Context($this->router, $requestContext);
        return $this->router->route($requestContext)->respond($ctx);
    }
}
