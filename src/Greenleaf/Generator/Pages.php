<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Archetype;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Slingshot;

class Pages implements Generator, Orderable
{
    public int $priority = 5;

    public function __construct(
        protected Archetype $archetype
    ) {
    }

    public function generateRoutes(): iterable
    {
        $providers = [];
        $slingshot = new Slingshot();

        foreach ($this->archetype->scanClasses(PageAction::class) as $class) {
            $providers[] = $slingshot->newInstance($class);
        }

        uasort($providers, function (PageAction $a, PageAction $b) {
            return $b->priority <=> $a->priority;
        });

        foreach ($providers as $provider) {
            yield from $provider->generateRoutes();
        }
    }
}
