<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\PageAction;

class Pages implements Generator, Orderable
{
    public int $priority = 5;
    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function generateRoutes(): iterable
    {
        $providers = [];
        $slingshot = $this->context->newSlingshot();

        foreach ($this->context->archetype->scanClasses(PageAction::class) as $class) {
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
