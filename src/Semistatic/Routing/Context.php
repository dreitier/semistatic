<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing;

class Context
{
    public function __construct(public readonly SemistaticRouterInterface $router,
                                public readonly RequestContext            $requestContext)
    {
    }
}

