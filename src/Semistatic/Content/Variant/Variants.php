<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Variant;

use Dreitier\Semistatic\Content\Variant;
use Dreitier\Semistatic\Routing\RequestContext;
use Exception;
use Generator;
use JetBrains\PhpStorm\Internal\TentativeType;
use Traversable;

class Variants implements \IteratorAggregate
{
    public function __construct(public readonly RequestContext $requestContext)
    {
    }

    private $variants = [];

    public function add(Variant $variant): Variants
    {
        $this->variants[] = $variant;
        return $this;
    }

    public function isEmpty(): bool
    {
        return sizeof($this->variants) == 0;
    }

    public function each(): Generator
    {
        foreach ($this->variants as $variant) {
            yield $variant;
        }
    }

    public function firstOrNull(): ?Variant
    {
        return $this->isEmpty() ? null : $this->variants[0];
    }

    public function size(): int
    {
        return sizeof($this->variants);
    }

    public function sortPreferred(): Variants
    {
        $variantsGrouped = [[], []];
        $variants = $this->variants;

        if (!empty($this->requestContext->selectedLanguage)) {
            $variants = [];

            /** @var Variant $variant */
            foreach ($this->variants as $data) {
                $useIdx = 1;
                $variant = $data;

                if ($variant->info->language == $this->requestContext->selectedLanguage) {
                    $useIdx = 0;
                }

                $variantsGrouped[$useIdx][] = $variant;
            }

            $variants = array_merge($variantsGrouped[0], $variantsGrouped[1]);
        }

        return $this->newFrom($variants);
    }

    private function newFrom(array $variants): Variants
    {
        $r = new static($this->requestContext);
        $r->variants = $variants;

        return $r;
    }

    public function selectFirstOrNull(?Selector $selector): ?Variant
    {
        if (!$selector) {
            $selector = new Selector();
        }

        $selector->onlyFirst = true;

        $r = $this->select($selector);

        return $r->firstOrNull();
    }

    /**
     * @param Selector|null $selector
     */
    public function select(?Selector $selector = null): Variants
    {
        if (!$selector) {
            $selector = new Selector();
        }

        $matches = [];

        foreach ($this->variants as $data) {
            /** @var Variant $variant */
            $variant = $data;

            /*
            if ($selector->primary === true) {
                $match = true;
            }
            */

            $match = $selector->matches($variant);

            if ($match) {
                $matches[] = $variant;

                if ($selector->onlyFirst) {
                    break;
                }
            }
        }

        return $this->newFrom($matches);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->variants);
    }
}