<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

interface Generator
{
    public function __construct(
        Context $context
    );

    /**
     * Generate routes
     *
     * @return iterable<Route|Generator>
     */
    public function generateRoutes(): iterable;
}
