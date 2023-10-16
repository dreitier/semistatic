<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Content\Item;

use Dreitier\Semistatic\Content\Item;

class Hierarchy
{
    private mixed $parentResolver;
    private mixed $childrenResolver;

    public function __construct(
        ?callable $parentResolver = null,
        ?callable $childrenResolver = null,
    )
    {
        $this->parentResolver = $parentResolver;
        $this->childrenResolver = $childrenResolver;
    }

    private $parent = null;
    private $parentResolveTried = false;

    /**
     * Return the physical parent directory or null if we are already in the shelf's root directory
     * @return Item|null
     */
    public function parent(): ?Item
    {
        if ($this->parentResolveTried === false && $this->parentResolver !== null) {
            $this->parent = ($this->parentResolver)();
            $this->parentResolveTried = true;
        }

        return $this->parent;
    }

    private ?Items $children = null;

    /**
     * @return Items
     */
    public function children(): Items
    {
        if ($this->children == null && $this->childrenResolver !== null) {
            $this->children = ($this->childrenResolver)();
        }

        return $this->children;
    }
}
