<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router\PatternSwitch;

use DecodeLabs\Greenleaf\Route\Bidirectional;

class OutGroup
{
    /**
     * @var array<string,Bidirectional>
     */
    protected(set) array $routes = [];

    public function mapRoute(
        Bidirectional $route
    ): void {
        $pattern = (string)$route->pattern;
        $this->routes[$pattern] = $route;
    }
}
