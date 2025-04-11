<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Compiler\Hit;
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
    public function matchIn(
        Request $request
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
