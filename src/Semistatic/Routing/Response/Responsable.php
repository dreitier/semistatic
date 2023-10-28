<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Routing\Response;

interface Responsable
{
    public function respond(): mixed;
}