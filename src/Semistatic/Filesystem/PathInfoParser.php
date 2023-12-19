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

    private string $regexDirectoryPrecompiled = '/^((?<order>\d+)\.)?(?<slug_or_variant_type>.*)/';
    private ?string $regexFilePrecompiled = null;

    private function fromFile(SplFileInfo $fileInfo): ?VariantFileInfo
    {
        if (!$this->regexFilePrecompiled) {
            $this->regexFilePrecompiled = '/^((?<order>\d+)\.)?(?<slug_or_variant_type>[\-|\w]*)(\.(?<language>(' . implode('|', $this->languages) . ')))?(\.(?<extension>(' . implode('|', $this->extensions) . ')))?$/';
        }

        if (!preg_match($this->regexFilePrecompiled, $fileInfo->getFilename(), $matches)) {
            return null;
        }

        return new VariantFileInfo(
            absolutePath: $fileInfo->getRealPath(),
            flavor: $matches['slug_or_variant_type'],
            extension: $matches['extension'] ?? '',
            language: !empty($matches['language']) ? $matches['language'] : $this->defaultLanguage,
        );
    }

    private function fromDirectory(SplFileInfo $fileInfo): ?ItemDirectoryInfo
    {
        if (!preg_match($this->regexDirectoryPrecompiled, $fileInfo->getFilename(), $matches)) {
            return null;
        }

        return new ItemDirectoryInfo(
            absolutePath: $fileInfo->getRealPath(),
            slug: $matches['slug_or_variant_type'],
            order: (int)$matches['order']
        );
    }

    public function fromFilename(SplFileInfo $fileInfo): ItemDirectoryInfo|VariantFileInfo|null
    {
        if ($fileInfo->isDir()) {
            return $this->fromDirectory($fileInfo);
        }

        return $this->fromFile($fileInfo);
    }
}