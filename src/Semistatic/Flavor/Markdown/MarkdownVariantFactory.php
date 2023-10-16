<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Flavor\Markdown;

use Dreitier\Semistatic\Content\Meta;
use Dreitier\Semistatic\Content\Variant;
use Dreitier\Semistatic\Content\Variant\Selector;
use Dreitier\Semistatic\Content\Variant\Factory;
use Dreitier\Semistatic\Filesystem\VariantFileInfo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarkdownVariantFactory implements Factory
{
    private array $regexToBladeTemplates = [];

    public function __construct(public readonly MarkdownToBladeRenderFactory $markdownToBladeRenderFactory)
    {
    }

    public function map(Selector $selector, string $bladeTemplate): MarkdownVariantFactory
    {
        $this->regexToBladeTemplates[] = ['selector' => $selector, 'bladeTemplate' => $bladeTemplate];
        return $this;
    }

    public function create(VariantFileInfo $variantInfo): ?Variant
    {
        if (!($bladeTemplateToUse = $this->mappedTo($variantInfo))) {
            return null;
        }

        $content = trim(file_get_contents($variantInfo->absolutePath));

        $position = 0;

        $meta = null;

        if (preg_match_all('/^---([\s\S]*?)---/ui', $content, $matches)) {
            $frontMatter = $matches[1][0];
            $position = strlen($matches[0][0]);

            try {
                $meta = Meta::fromYaml($frontMatter);
            } catch (\Exception $e) {
                // TODO pass parsing errors to frontend
                Log::error("Unable to parse " . $variantInfo . ": " . $e->getMessage());
            }
        }

        return new Variant(
            $variantInfo,
            $this->markdownToBladeRenderFactory->create(
                $bladeTemplateToUse,
                $variantInfo->absolutePath,
                $position,
            ),
            $meta ?? Meta::empty(),
        );
    }

    public function mappedTo(VariantFileInfo $variantInfo): ?string
    {
        if (!Str::endsWith($variantInfo->extension, 'md')) {
            return null;
        }

        foreach ($this->regexToBladeTemplates as $idx => $config) {
            if ($variantInfo->flavor == $config['selector']->flavor) {
                return $config['bladeTemplate'];
            }
        }

        return null;
    }
}
