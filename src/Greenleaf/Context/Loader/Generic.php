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
use DecodeLabs\Greenleaf\Generator\Scanner;
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
        return new Scanner($this->context);
    }

    /**
     * Load router instance
     */
    public function loadRouter(
        Generator $generator
    ): Router {
        $router = null;

        // Load router
        if ($this->context->container instanceof PandoraContainer) {
            $router = $this->context->container->tryGetWith(Router::class, [
                'generator' => $generator
            ]);
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
            $class = $this->context->archetype->resolve(Router::class, [null, 'Matching']);
            $router = new $class($generator);
        }

        return $router;
    }
}
