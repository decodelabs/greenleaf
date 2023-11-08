<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;

interface Router
{
    public function __construct(
        Generator $generator
    );

    /**
     * Find route for request
     */
    public function routeIn(
        Request $request
    ): ?Route;


    /**
     * Find route for leaf URI
     *
     * @param array<string, string|Stringable|int|float|null> $params
     */
    public function routeOut(
        string|LeafUrl $uri,
        ?array $params = null
    ): ?Route;
}
