<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Context;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Router;

interface Loader
{
    public function loadGenerator(): Generator;

    public function loadRouter(): Router;
}
