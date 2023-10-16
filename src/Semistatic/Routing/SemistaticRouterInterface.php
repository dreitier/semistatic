<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing;

use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Routing\Response\Responsable;

interface SemistaticRouterInterface
{
    public function route(RequestContext $requestContext): Responsable;
}
