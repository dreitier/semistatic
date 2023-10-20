<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Filesystem;

use SplFileInfo;

class PathInfoParser
{
    public $extensions = [
        'md',
        'html',
        'mdx'
    ];

    public $languages = [
        'de',
        'en'
    ];

    public function __construct(public readonly string $defaultLanguage = 'en')
    {
    }

    private ?string $regexPrecompiled = null;

    public function fromFilename(SplFileInfo $fileInfo): ItemDirectoryInfo|VariantFileInfo|null
    {
        if (!$this->regexPrecompiled) {
            $this->regexPrecompiled = '/^((?<order>\d+)\.)?(?<slug_or_variant_type>[\-|\w]*)(\.(?<language>(' . implode('|', $this->languages) . ')))?(\.(?<extension>(' . implode('|', $this->extensions) . ')))?$/';
        }

        if (preg_match($this->regexPrecompiled, $fileInfo->getFilename(), $matches)) {
            if ($fileInfo->isDir()) {
                return new ItemDirectoryInfo(
                    absolutePath: $fileInfo->getRealPath(),
                    slug: $matches['slug_or_variant_type'],
                    order: (int)$matches['order']
                );
            }

            return new VariantFileInfo(
                absolutePath: $fileInfo->getRealPath(),
                flavor: $matches['slug_or_variant_type'],
                extension: $matches['extension'] ?? '',
                language: !empty($matches['language']) ? $matches['language'] : $this->defaultLanguage,
            );
        }

        return null;
    }
}


