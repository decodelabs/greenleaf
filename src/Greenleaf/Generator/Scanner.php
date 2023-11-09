<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\GeneratorTrait;

class Scanner implements Generator
{
    use GeneratorTrait;

    public function generateRoutes(): iterable
    {
        $class = $this->context->archetype->resolve(Generator::class, 'routes');
        $routes = new $class($this->context);

        yield from $routes->generateRoutes();
    }
}
