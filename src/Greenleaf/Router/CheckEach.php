<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router;

use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Greenleaf\RouterTrait;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;

class CheckEach implements Router, Caching
{
    use RouterTrait;
    use RouteCollectorTrait;

    protected Generator $generator;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
        $this->generator = $context->loader->loadGenerator();
    }

    /**
     * Find route for request
     */
    public function matchIn(
        Request $request
    ): ?Hit {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->scanRoutes($this->generator) as $route) {
            if ($hit = $route->matchIn($method, $uri)) {
                return $hit;
            }
        }

        return null;
    }


    /**
     * Find route for leaf URI
     *
     * @param array<string,string|Stringable|int|float|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit {
        foreach ($this->scanRoutes($this->generator) as $route) {
            if ($hit = $route->matchOut($uri, $parameters)) {
                return $hit;
            }
        }

        return null;
    }
}
