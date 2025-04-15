<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Greenleaf\Generator;

interface Orderable extends Generator
{
    public int $priority { get; set; }
}
