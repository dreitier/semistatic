<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content;

use ArrayIterator;
use Dreitier\Semistatic\Content\Item\Items;
use Dreitier\Semistatic\Content\Item\Hierarchy;
use Dreitier\Semistatic\Content\Meta\DateValue;
use Dreitier\Semistatic\Content\Meta\MetaValue;
use Dreitier\Semistatic\Content\Meta\MetaValueFactory;
use Dreitier\Semistatic\Content\Variant\Selector;
use Dreitier\Semistatic\Content\Variant\Variants;
use Dreitier\Semistatic\Filesystem\ItemDirectoryInfo;
use Dreitier\Semistatic\Filesystem\VariantFileInfo;
use Dreitier\Semistatic\Navigation\Trail;
use Dreitier\Semistatic\Routing\RequestContext;

class Item
{
    public function __construct(
        public readonly ItemDirectoryInfo $info,
        public readonly Hierarchy         $physicalLocation,
        public readonly Variants          $variants,
        public readonly ?RequestContext   $requestContext = null,
    )
    {
    }

    private ?Items $children = null;

    public function title(mixed $default = null, ?Selector $variantSelector = null): MetaValue
    {
        return $this->meta('title', $this->info->slug, null, $variantSelector);
    }

    public function date(?Selector $variantSelector = null): DateValue|MetaValue
    {
        return $this->meta('date', null, MetaValueFactory::DATE, $variantSelector);
    }

    public function slug(?Selector $variantSelector = null): string
    {
        /** @var Variant $variant */
        if ($variant = $this->variants->selectFirstOrNull($variantSelector)) {
            if (sizeof($variant->meta->slugs()) > 0) {
                return $variant->meta->slugs[0];
            }
        }

        return $this->info->slug;
    }

    public function matches(Selector $selector): bool
    {
        return !$this->variants->select($selector)->isEmpty();
    }

    public function firstVariantOrNull(?Selector $selector): ?Variant {
        return $this->variants->sortPreferred()->selectFirstOrNull($selector);
    }

    public function meta(string $pathInArray, mixed $default = null, ?string $type = 'string', ?Selector $variantSelector = null): MetaValue
    {
        return $this->firstVariantOrNull($variantSelector)?->meta->valueOf($pathInArray, $default, $type);
    }

    public function content(?Selector $variantSelector = null): string
    {
        return $this->firstVariantOrNull($variantSelector)?->content->render($this) ?? '';
    }

    public function variantInfo(?Selector $variantSelector = null): ?VariantFileInfo
    {
        return $this->firstVariantOrNull($variantSelector)?->info;
    }

    public function url(bool $absolute = false, ?Selector $variantSelector = null): string
    {
        if ($this->requestContext) {
            return ($this->requestContext->urlGenerator)($this->trail(), $this->requestContext);
        }

        return $this->slug($variantSelector);
    }

    public function children(string|callable $sortBy = 'default', string $order = 'asc'): Items|ArrayIterator
    {
        if ($this->children == null) {
            $this->children = $this->physicalLocation->children();
        }

        return $this->children->sortBy($sortBy, $order);
    }

    public function parent(): ?Item
    {
        return $this->physicalLocation->parent();
    }

    public function isRoot(): bool
    {
        return $this->hasParent() === FALSE;
    }

    public function hasParent(): bool
    {
        return $this->physicalLocation->parent() != null;
    }

    public function hasChildren(): bool
    {
        return $this->physicalLocation->children()->size() !== 0;
    }

    private $slugs = null;

    public function slugs(): array
    {
        if ($this->slugs == null) {
            $slugs = [$this->info->slug];

            foreach ($this->variants->each() as $variant) {
                $slugs[] = $variant->meta->slugs();
            }

            $this->slugs = $slugs;
        }

        return $this->slugs;
    }

    public function hasSlug($slug): bool
    {
        return in_array($slug, $this->slugs());
    }

    public function trail(): Trail
    {
        $r = new Trail();
        $item = $this;
        $rootVisited = false;

        while (!$item->isRoot()) {
            $r->insertFirst($item);
            $item = $item->parent();
        }

        return $r;
    }
}
