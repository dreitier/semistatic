<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Flavor\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownConverterFactory
{
    public function __construct(
        public mixed $environmentCustomizer = null,
        public readonly array $config = [],
    )
    {
    }

    public function createDefaultEnvironment(array $config = []): Environment
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        if (is_callable($this->environmentCustomizer)) {
            ($this->environmentCustomizer)($environment, $config);
        }

        return $environment;
    }

    public function create(?Environment $environment = null): MarkdownConverter
    {
        $environment = $environment ?? $this->createDefaultEnvironment($this->config);

        $converter = new MarkdownConverter($environment);
        return $converter;
    }
}