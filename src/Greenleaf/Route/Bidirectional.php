<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;

interface Bidirectional extends Route
{
    public LeafUrl $target { get; }
}
