<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Archetype;
use DecodeLabs\Archetype\Resolver\Greenleaf as GreenleafResolver;
use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Generator\Scanner;
use DecodeLabs\Greenleaf\Compiler\Hit;
use DecodeLabs\Pandora\Container as PandoraContainer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Dispatcher implements Handler
{
    protected ?ContainerInterface $container = null;
    protected static bool $setup = false;

    public function __construct(
        ?ContainerInterface $container = null
    ) {
        $this->container = $container;

        $this->setupArchetype();
    }

    /**
     * Setup archetype
     */
    protected static function setupArchetype(): void
    {
        if (Dispatcher::$setup) {
            return;
        }

        Archetype::register(
            new GreenleafResolver(
                interface: Action::class,
                namespaces: Greenleaf::$namespaces,
                named: true
            ),
            unique: true
        );

        Archetype::register(
            new GreenleafResolver(
                interface: Generator::class,
                namespaces: Greenleaf::$namespaces,
            ),
            unique: true
        );

        Dispatcher::$setup = true;
    }

    /**
     * Begin stage stack navigation
     */
    final public function handle(
        Request $request
    ): Response {
        $hit = $this->findRoute($request);
        return $hit->getRoute()->handle($request, $hit->getParameters());
    }

    /**
     * Load generator
     */
    public function loadGenerator(): Generator
    {
        $generator = null;

        if($this->container instanceof PandoraContainer) {
            $generator = $this->container->tryGet(Generator::class);
        } elseif(
            $this->container &&
            $this->container->has(Generator::class)
        ) {
            if(!($generator = $this->container->get(Generator::class))
                instanceof Generator
            ) {
                $generator = null;
            }
        }

        if($generator === null) {
            $generator = new Scanner();
        }

        return $generator;
    }

    /**
     * Load router
     */
    public function loadRouter(
        ?Generator $generator = null
    ): Router {
        if ($generator === null) {
            $generator = $this->loadGenerator();
        }

        $router = null;

        // Load router
        if ($this->container instanceof PandoraContainer) {
            $router = $this->container->tryGetWith(Router::class, [
                'generator' => $generator
            ]);
        } elseif (
            $this->container &&
            $this->container->has(Router::class)
        ) {
            if (!($router = $this->container->get(Router::class))
                instanceof Router
            ) {
                $router = null;
            }
        }

        if (!$router) {
            $class = Archetype::resolve(Router::class, [null, 'Matching']);
            $router = new $class($generator);
        }

        return $router;
    }

    /**
     * Load route
     */
    public function findRoute(
        Request $request
    ): Hit {
        $generator = $this->loadGenerator();
        $router = $this->loadRouter($generator);


        // Route request
        if (!$hit = $router->routeIn($request)) {
            throw Exceptional::RouteNotFound(
                'Route not found: ' . $request->getUri()->getPath()
            );
        }

        return $hit;
    }
}
