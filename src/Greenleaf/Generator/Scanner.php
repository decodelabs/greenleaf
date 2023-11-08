<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Archetype;
use DecodeLabs\Greenleaf\Generator;

class Scanner implements Generator
{
    public function generateRoutes(): iterable
    {
        $class = Archetype::resolve(Generator::class, 'routes');
        $routes = new $class();

        yield from $routes->generateRoutes();
    }
}
