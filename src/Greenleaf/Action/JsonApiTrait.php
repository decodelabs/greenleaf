<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Action;

trait JsonApiTrait
{
    use ByMethodTrait;

    protected function getDefaultContentType(): string
    {
        return 'application/json';
    }
}
