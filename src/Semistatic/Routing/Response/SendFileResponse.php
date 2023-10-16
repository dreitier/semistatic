<?php
declare(strict_types=1);

namespace Dreitier\Semistatic\Routing\Response;

use Dreitier\Semistatic\Content\Item;
use Dreitier\Semistatic\Routing\Context;
use Dreitier\Semistatic\Routing\Response\Responsable;
use Illuminate\Support\Facades\File;

class SendFileResponse implements Responsable
{
    public function __construct(public readonly Item $item, public readonly string $fileInItem)
    {

    }

    public static function mayProcess(Item $item, string $fileInItem): ?SendFileResponse
    {
        $path = $item->info->absolutePath . '/' . $fileInItem;

        if (file_exists($path)) {
            return new static($item, $fileInItem);
        }

        return null;
    }

    public function respond(Context $context): mixed
    {
        $absolutePath = $this->item->info->absolutePath . '/' . $this->fileInItem;

        // send correct MIME type, e.g. for .svg files
        $mimeContentType = mime_content_type($absolutePath);

        return response(File::get($absolutePath))->header('Content-Type', $mimeContentType);
    }
}
