<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Closure;
use DecodeLabs\Archetype;
use DecodeLabs\Archetype\NamespaceMap;
use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Generator\Scanner;
use DecodeLabs\Greenleaf\Route\Action as ActionRoute;
use DecodeLabs\Greenleaf\Route\Redirect as RedirectRoute;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Singularity\Url;
use DecodeLabs\Singularity\Url\Http as HttpUrl;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\LazyLoad;
use DecodeLabs\Veneer\Plugin;
use Psr\Container\ContainerInterface;
use Stringable;

#[LazyLoad]
class Context
{
    #[Plugin]
    public NamespaceMap $namespaces;

    protected ?ContainerInterface $container = null;

    protected Router $router;

    /**
     * Init with namespace map
     */
    public function __construct(
        ?NamespaceMap $namespaces = null,
        ?ContainerInterface $container = null
    ) {
        $this->namespaces = $namespaces ?? new NamespaceMap();
        $this->container = $container;
    }


    /**
     * Load router
     */
    public function getRouter(): Router
    {
        if (isset($this->router)) {
            return $this->router;
        }

        $generator = $this->loadGenerator();

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

        return $this->router = $router;
    }

    /**
    * Load generator
    */
    protected function loadGenerator(): Generator
    {
        $generator = null;

        if ($this->container instanceof PandoraContainer) {
            $generator = $this->container->tryGet(Generator::class);
        } elseif (
            $this->container &&
            $this->container->has(Generator::class)
        ) {
            if (!($generator = $this->container->get(Generator::class))
                instanceof Generator
            ) {
                $generator = null;
            }
        }

        if ($generator === null) {
            $generator = new Scanner();
        }

        return $generator;
    }




    /**
     * Create URL from uri
     *
     * @param array<string, string|Stringable|int|float|null> $params
     */
    public function createUrl(
        string|LeafUrl $uri,
        ?array $params = null
    ): Url {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        if (!$hit = $this->getRouter()->matchOut($uri, $params)) {
            throw Exceptional::RouteNotMatched(
                'Unable to match uri to route'
            );
        }

        $route = $hit->getRoute();
        /** @var array<string, string|Stringable|float|int|null> */
        $params = $hit->getParameters();
        $segments = $route->getPattern()->parseSegments();

        foreach ($segments as $i => $segment) {
            $segments[$i] = $segment->resolve($params);
        }

        /** @var array<string> $segments */
        $path = implode('/', $segments);

        return new HttpUrl(
            scheme: null,
            path: $path,
            query: $hit->getQueryString(),
            fragment: $uri->getFragment(),
        );
    }




    /**
     * Create action route
     */
    public function route(
        string $pattern,
        string $target,
        ?Closure $setup = null
    ): ActionRoute {
        $output = new ActionRoute($pattern, $target);

        if ($setup) {
            $setup($output);
        }

        return $output;
    }

    /**
     * Create redirect route
     */
    public function redirect(
        string $pattern,
        string $target,
        ?Closure $setup = null
    ): RedirectRoute {
        $output = new RedirectRoute($pattern, $target);

        if ($setup) {
            $setup($output);
        }

        return $output;
    }
}


// Veneer
Veneer::register(
    Context::class,
    Greenleaf::class // @phpstan-ignore-line
);
