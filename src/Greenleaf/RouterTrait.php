<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Generator\Caching;

/**
 * @phpstan-require-implements Router
 */
trait RouterTrait
{
    public function __construct(
        protected Generator $generator
    ) {
    }

    public function clearCache(): void
    {
        if ($this->generator instanceof Caching) {
            $this->generator->clearCache();
        }
    }

    public function rebuildCache(): void
    {
        if ($this->generator instanceof Caching) {
            $this->generator->rebuildCache();
        }
    }
}
