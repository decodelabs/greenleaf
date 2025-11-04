<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router;

use DecodeLabs\Greenleaf\Generator as RouteGenerator;
use DecodeLabs\Greenleaf\Route;
use Generator;

trait RouteCollectorTrait
{
    /**
     * @return array<string, Route>
     */
    protected function collectRoutes(
        RouteGenerator $generator
    ): array {
        return iterator_to_array($this->scanRoutes($generator));
    }

    /**
     * @return Generator<string, Route>
     */
    protected function scanRoutes(
        RouteGenerator $generator
    ): Generator {
        foreach ($generator->generateRoutes() as $route) {
            if ($route instanceof RouteGenerator) {
                yield from $this->scanRoutes($route);
            } else {
                yield (string)$route->pattern => $route;
            }
        }
    }
}
