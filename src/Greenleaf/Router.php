<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Stringable;

interface Router
{
    /**
     * Find route for request
     */
    public function matchIn(
        PsrRequest $request
    ): ?Hit;


    /**
     * Find route for leaf URI
     *
     * @param array<string, string|Stringable|int|float|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit;
}
