<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router;

use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Greenleaf\RouterTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;

class Matching implements Router
{
    use RouterTrait;
    use RouteCollectorTrait;

    /**
     * Find route for request
     */
    public function routeIn(
        Request $request
    ): ?Route {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->scanRoutes($this->generator) as $route) {
            if ($route->matchesIn($method, $uri)) {
                return $route;
            }
        }

        return null;
    }


    /**
     * Find route for leaf URI
     *
     * @param array<string, string|Stringable|int|float|null> $params
     */
    public function routeOut(
        string|LeafUrl $uri,
        ?array $params = null
    ): ?Route {
        foreach ($this->scanRoutes($this->generator) as $route) {
            if ($route->matchesOut($uri, $params)) {
                return $route;
            }
        }

        return null;
    }
}
