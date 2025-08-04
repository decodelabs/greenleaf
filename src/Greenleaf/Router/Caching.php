<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router;

use DecodeLabs\Greenleaf\Router;

interface Caching extends Router
{
    public function clearCache(): void;
    public function rebuildCache(): void;
}
