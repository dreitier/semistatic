<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing\Response;

use Dreitier\Semistatic\Content\Item;
use Illuminate\Support\Facades\File;

class SendFileResponse implements Responsable
{
    public function __construct(public readonly Item $item, public readonly string $fileInItem)
    {
    }

    public static function mayProcess(Item $item, string $fileInItem): ?SendFileResponse
    {
        if (self::safeResolve($item->info->absolutePath, $fileInItem) === null) {
            return null;
        }

        return new static($item, $fileInItem);
    }

    public function respond(): mixed
    {
        $absolutePath = self::safeResolve($this->item->info->absolutePath, $this->fileInItem);

        // defence in depth: mayProcess already rejected traversal, never serve outside the item
        if ($absolutePath === null) {
            abort(404);
        }

        // send correct MIME type, e.g. for .svg files
        $mimeContentType = mime_content_type($absolutePath);

        return response(File::get($absolutePath))->header('Content-Type', $mimeContentType);
    }

    /**
     * Resolve $fileInItem against the item's directory and return the canonical absolute path,
     * but only when it is an existing file that stays inside that directory. Returns null for
     * missing files and for traversal attempts — "..", null bytes, or symlinks that escape the
     * item — so a crafted URL such as "<item>/../../.env" cannot read files outside the content
     * tree. The request layer turns null into a 404.
     */
    public static function safeResolve(string $baseDir, string $fileInItem): ?string
    {
        if (str_contains($fileInItem, "\0")) {
            return null;
        }

        $base = realpath($baseDir);

        if ($base === false) {
            return null;
        }

        $target = realpath($base . '/' . $fileInItem);

        if ($target === false || ! is_file($target)) {
            return null;
        }

        // the resolved file must be the base directory's own file or sit strictly inside it
        if ($target !== $base && ! str_starts_with($target, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $target;
    }
}
