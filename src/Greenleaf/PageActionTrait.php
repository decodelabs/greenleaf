<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File;
use DecodeLabs\Monarch;

trait PageActionTrait
{
    /**
     * @return iterable<string,File>
     */
    protected function scanPageFiles(
        string $extension
    ): iterable {
        $path = Monarch::getPaths()->resolve('@pages/');

        foreach (Atlas::scanFilesRecursive($path, fn (string $file) => str_ends_with($file, '.' . $extension)) as $file) {
            $name = basename($file->path, '.' . $extension);
            yield $name => $file;
        }
    }

    protected function nameToPattern(
        string $name
    ): string {
        return match ($name) {
            'index' => '/',
            default => $name
        };
    }
}
