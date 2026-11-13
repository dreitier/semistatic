<?php

use Dreitier\Semistatic\Routing\Response\SendFileResponse;

beforeEach(function () {
    $this->base = sys_get_temp_dir() . '/semistatic-sfr-' . uniqid();
    mkdir($this->base . '/sub', 0777, true);
    file_put_contents($this->base . '/image.svg', '<svg/>');
    file_put_contents($this->base . '/sub/nested.txt', 'nested');
    // a file one level above the item directory, i.e. outside the content tree
    $this->secret = dirname($this->base) . '/secret-' . basename($this->base) . '.txt';
    file_put_contents($this->secret, 'secret');
});

afterEach(function () {
    @unlink($this->base . '/image.svg');
    @unlink($this->base . '/sub/nested.txt');
    @rmdir($this->base . '/sub');
    @rmdir($this->base);
    @unlink($this->secret);
});

it('resolves files that stay inside the item directory', function () {
    expect(SendFileResponse::safeResolve($this->base, 'image.svg'))
        ->toBe(realpath($this->base . '/image.svg'));
    expect(SendFileResponse::safeResolve($this->base, 'sub/nested.txt'))
        ->toBe(realpath($this->base . '/sub/nested.txt'));
});

it('rejects traversal, missing files and null bytes', function () {
    $secret = basename($this->secret);

    expect(SendFileResponse::safeResolve($this->base, '../' . $secret))->toBeNull();
    expect(SendFileResponse::safeResolve($this->base, 'sub/../../' . $secret))->toBeNull();
    expect(SendFileResponse::safeResolve($this->base, '../../../../../../etc/hostname'))->toBeNull();
    expect(SendFileResponse::safeResolve($this->base, "image.svg\0.txt"))->toBeNull();
    expect(SendFileResponse::safeResolve($this->base, 'does-not-exist.svg'))->toBeNull();
    expect(SendFileResponse::safeResolve($this->base . '/missing-base', 'x'))->toBeNull();
});
