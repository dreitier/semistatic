<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Filesystem;

class Path
{
    public function __construct(public readonly string $root, public readonly array $nodes = [])
    {
    }

    public static function of(string $root, array $nodes = [])
    {
        return new static(static::normalize($root), $nodes);
    }

    private ?string $relativePath = null;

    public function relativePath(): string
    {
        if ($this->relativePath == null) {
            $this->relativePath = implode("/", $this->nodes);
        }

        return $this->relativePath;
    }

    private ?string $absolutePath = null;

    public function absolutePath(): string
    {
        if ($this->absolutePath == null) {
            $this->absolutePath = static::normalize($this->root . '/' . $this->relativePath());
        }

        return $this->absolutePath;
    }

    public function down(string $node): Path
    {
        return new Path($this->root, array_merge($this->nodes, [$node]));
    }

    public function up(): Path
    {
        $nodes = $this->nodes;
        array_pop($nodes);
        return new Path($this->root, $nodes);
    }

    public function isRoot(): bool
    {
        return sizeof($this->nodes) == 0;
    }

    public static function normalize(string $path): string
    {
        if ($r = preg_replace('#/+#', '/', $path)) {
            return $r;
        }

        return $path;
    }

}

