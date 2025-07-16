<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Context\Loader;

use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Context\Loader;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Generator\Collector;
use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Pandora\Container as PandoraContainer;

class Generic implements Loader
{
    protected Context $context;

    /**
     * Init with container
     */
    public function __construct(
        Context $context,
    ) {
        $this->context = $context;
    }

    /**
     * Load generator instance
     */
    public function loadGenerator(): Generator
    {
        return $this->context->newSlingshot()
            ->newInstance(Collector::class);
    }

    /**
     * Load router instance
     */
    public function loadRouter(): Router
    {
        $router = null;

        // Load router
        if ($this->context->container instanceof PandoraContainer) {
            $router = $this->context->container->tryGetWith(Router::class);
        } elseif (
            $this->context->container &&
            $this->context->container->has(Router::class)
        ) {
            if (!($router = $this->context->container->get(Router::class))
                instanceof Router
            ) {
                $router = null;
            }
        }

        if (!$router) {
            $class = $this->context->archetype->resolve(Router::class, [null, 'PatternSwitch', 'CheckEach']);

            $router = $this->context->newSlingshot()
                ->newInstance($class);
        }

        return $router;
    }
}
