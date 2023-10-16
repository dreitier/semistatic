<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing;

class RequestContext
{
    public function __construct(public readonly string $uriRelativeToShelfRoot,
                                public ?string         $selectedLanguage = null,
                                public mixed           $urlGenerator = null,
    )
    {
    }
}
