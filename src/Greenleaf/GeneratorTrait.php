<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

trait GeneratorTrait
{
    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }
}
