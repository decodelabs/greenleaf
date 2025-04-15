<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

interface Generator
{
    /**
     * Generate routes
     *
     * @return iterable<Route|Generator>
     */
    public function generateRoutes(): iterable;
}
