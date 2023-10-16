<?php

declare(strict_types=1);

namespace Dreitier\Semistatic\Flavor\Markdown;

use Dreitier\Semistatic\Content\ContentProvider;
use Dreitier\Semistatic\Content\Item;
use League\CommonMark\MarkdownConverter;

class MarkdownToBladeRenderer implements ContentProvider
{
    public function __construct(
        public readonly MarkdownConverter $markdownConverter,
        public readonly string            $bladeTemplate,
        public readonly string            $sourceFile,
        public readonly int               $sourceFileBeginPos = 0,
        private ?\Closure                 $viewPropertyConfigurer = null,
        private ?\Closure                 $viewResponder = null,
    )
    {
        if ($this->viewResponder == null) {
            $this->viewResponder = function (string $bladeTemplate, &$args, $item, $_this) {
                return view($this->bladeTemplate, $args);
            };
        }
    }

    public function render(Item $item): mixed
    {
        $preProcessed = $this->getRaw();

        if ($item->meta('blade', false, 'bool')->is(true)) {
            $preProcessed = \Illuminate\Support\Facades\Blade::render($preProcessed, ['item' => $item]);
        }

        $postProcessed = $this->getMarkdownRendered($preProcessed);

        $args = [
            'item' => $item,
            'content' => $postProcessed,
        ];

        if ($this->viewPropertyConfigurer) {
            ($this->viewPropertyConfigurer)($args, $item, $this);
        }

        return ($this->viewResponder)($this->bladeTemplate, $args, $item, $this);
    }

    private ?string $rendered = null;

    private ?string $raw = null;

    private function getMarkdownRendered(string $preprocessed): string
    {
        if ($this->rendered === null) {
            $this->rendered = $this->markdownConverter->convert($preprocessed)->getContent();
        }

        return $this->rendered;
    }

    public function getRaw(): string
    {
        if ($this->raw === null) {
            $this->raw = substr(file_get_contents($this->sourceFile), $this->sourceFileBeginPos);
        }

        return $this->raw;
    }
}
