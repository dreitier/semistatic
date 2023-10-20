<?php

use Dreitier\Semistatic\Filesystem\PathInfoParser;

test('type and extension recognized from filename', function () {
    $info = new SplFileInfo(__DIR__ . '/PathInfoParserTest/article.md');
    $sut = new PathInfoParser();
    $r = $sut->fromFilename($info);

    expect($r->extension)->toBe('md');
    expect($r->language)->toBe('en' /* default language */);
});

test('default language can be set', function () {
    $info = new SplFileInfo(__DIR__ . '/PathInfoParserTest/article.md');
    $sut = new PathInfoParser('de');
    $r = $sut->fromFilename($info);

    expect($r->language)->toBe('de' /* default language */);
});

test('language is recognized from filename', function () {
    $info = new SplFileInfo(__DIR__ . '/PathInfoParserTest/article.de.md');
    $sut = new PathInfoParser();
    $r = $sut->fromFilename($info);

    expect($r->extension)->toBe('md');
    expect($r->language)->toBe('de');
});

test('other formats like .bak files are ignored', function () {
    $info = new SplFileInfo(__DIR__ . '/PathInfoParserTest/article.de.md.bak');
    $sut = new PathInfoParser();
    $r = $sut->fromFilename($info);

    expect($r)->toBe(null);
});
