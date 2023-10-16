<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing\Response;

use Dreitier\Semistatic\Routing\Context;
use Dreitier\Semistatic\Routing\RequestContext;

class NotFoundResponse implements Responsable
{
    public function __construct(public readonly RequestContext $requestContext)
    {
    }

    public function respond(Context $context): mixed
    {
        return abort(404);
    }
}

