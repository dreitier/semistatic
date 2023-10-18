<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Variant;

use Dreitier\Semistatic\Content\Variant;

class Selector
{
    public function __construct(
        public ?string      $flavor = null,
        public ?string      $language = null,
        public ?string      $extension = null,
        public ?bool        $primary = null,
        public ?bool        $onlyFirst = null,
        public SelectorMode $mode = SelectorMode::AND,
    )
    {
    }

    private function combine(bool $overallResult, bool $singleResult): bool
    {
        if ($this->mode == SelectorMode::AND) {
            return (bool)($overallResult &= $singleResult);
        }

        return (bool)($overallResult |= $singleResult);
    }

    public function matches(Variant $variant, bool $previousMatches = false): bool
    {
        $previousMatches = false;

        if ($this->language !== null) {
            $previousMatches = $this->combine($previousMatches, (bool)preg_match('/' . $this->language . '/', $variant->info->language));
        }

        if ($this->extension !== null) {
            $previousMatches = $this->combine($previousMatches, (bool)preg_match('/' . $this->extension . '/', $variant->info->extension));
        }

        if ($this->flavor !== null) {
            $previousMatches = $this->combine($previousMatches, (bool)preg_match('/' . $this->flavor . '/', $variant->info->flavor));
        }

        return $previousMatches;
    }
}