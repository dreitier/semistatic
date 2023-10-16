<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Flavor\Markdown;

use Dreitier\Semistatic\Content\ContentProvider;

class MarkdownToBladeRenderFactory
{
    public function __construct(public ?MarkdownConverterFactory $markdownConverterFactory,
                                public ?\Closure                 $viewPropertyConfigurer = null,
                                public ?\Closure                 $viewResponder = null,
    )
    {
        if ($this->markdownConverterFactory == null) {
            $this->markdownConverterFactory = new MarkdownConverterFactory();
        }
    }


    public function create(string $bladeTemplateToUse,
                           string $sourceFile,
                           int    $sourceFileBeginPos = 0,
    ): ContentProvider
    {
        return new MarkdownToBladeRenderer(
            markdownConverter: $this->markdownConverterFactory->create(),
            bladeTemplate: $bladeTemplateToUse,
            sourceFile: $sourceFile,
            sourceFileBeginPos: $sourceFileBeginPos,
            viewPropertyConfigurer: $this->viewPropertyConfigurer,
            viewResponder: $this->viewResponder
        );
    }
}