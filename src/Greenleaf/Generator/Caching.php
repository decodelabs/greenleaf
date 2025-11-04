<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Greenleaf\Generator;

interface Caching extends Generator
{
    public function clearCache(): void;
    public function rebuildCache(): void;
}
