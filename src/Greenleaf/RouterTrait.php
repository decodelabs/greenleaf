<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

/**
 * @phpstan-require-implements Router
 */
trait RouterTrait
{
    protected Generator $generator;

    public function __construct(
        Generator $generator
    ) {
        $this->generator = $generator;
    }
}
