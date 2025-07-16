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
    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function clearCache(): void
    {
        $generator = $this->context->loader->loadGenerator();

        if ($generator instanceof Caching) {
            $generator->clearCache();
        }
    }
}
