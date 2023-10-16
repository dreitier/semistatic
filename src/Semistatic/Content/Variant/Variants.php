<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Variant;
use Dreitier\Semistatic\Content\Variant;
use Generator;

class Variants
{
    private $variants = [];

    public function add(Variant $variant, bool $isPrimary = true): Variants
    {
        $this->variants[] = [$variant, $isPrimary];
        return $this;
    }

    public function isEmpty(): bool
    {
        return sizeof($this->variants) == 0;
    }

    public function each(): Generator
    {
        foreach ($this->variants as $variantData) {
            yield $variantData[0];
        }
    }

    public function select(?Selector $selector = null): array|null|Variant
    {
        if (!$selector) {
            return $this->active();
        }

        $r = [];

        foreach ($this->variants as $data) {
            /** @var Variant $variant */
            $variant = $data[0];
            $isPrimary = $data[1];

            $match = false;

            if ($isPrimary && $selector->primary === true) {
                $match = true;
            }

            $match = $selector->matches($variant, $match);

            if ($match) {
                $r[] = $variant;

                if ($selector->onlyFirst) {
                    break;
                }
            }
        }

        return $selector->onlyFirst === true ? $r[0] ?? null : $r;
    }

    public function active(): ?Variant
    {
        $lastMatch = null;

        foreach ($this->variants as $data) {
            $lastMatch = $data[0];

            if ($data[1] === true) {
                return $lastMatch;
            }
        }

        return $lastMatch;
    }
}


