<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Archetype;
use DecodeLabs\Exceptional;
use DecodeLabs\Pandora\Container as PandoraContainer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Dispatcher implements Handler
{
    protected ?ContainerInterface $container = null;

    public function __construct(
        ?ContainerInterface $container = null
    ) {
        $this->container = $container;
    }

    /**
     * Begin stage stack navigation
     */
    final public function handle(
        Request $request
    ): Response {
        return $this->findRoute($request)
            ->handle($request);
    }

    /**
     * Load generator
     */
    public function loadGenerator(): Generator
    {
        /** @var Generator $output */
        $output = new \Songsprout\Api\Greenleaf\Routes(); // @phpstan-ignore-line
        return $output;
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
    ): Route {
        $generator = $this->loadGenerator();
        $router = $this->loadRouter($generator);


        // Route request
        if (!$route = $router->routeIn($request)) {
            throw Exceptional::NotFound(
                'Route not found: ' . $request->getUri()->getPath()
            );
        }

        return $route;
    }
}
