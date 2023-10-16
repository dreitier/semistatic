<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Routing\Response;

use Dreitier\Semistatic\Routing\Context;

interface Responsable
{
    public function respond(Context $context): mixed;
}