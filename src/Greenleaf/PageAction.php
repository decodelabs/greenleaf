<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Generator\Orderable;

interface PageAction extends Action, Generator, Orderable
{
}
