<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router;

use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Greenleaf\RouterTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Stringable;

class CheckEach implements Router, Caching
{
    use RouterTrait;
    use RouteCollectorTrait;

    public function matchIn(
        PsrRequest $request
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
     * @param array<string,string|Stringable|int|float|bool|null> $parameters
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
