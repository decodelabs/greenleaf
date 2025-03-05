<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Closure;
use DecodeLabs\Archetype\Handler as ArchetypeHandler;
use DecodeLabs\Archetype\Resolver\Greenleaf as GreenleafResolver;
use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Compiler\Hit;
use DecodeLabs\Greenleaf\Context\Loader;
use DecodeLabs\Greenleaf\Route\Action as ActionRoute;
use DecodeLabs\Greenleaf\Route\Redirect as RedirectRoute;
use DecodeLabs\Harvest\Middleware\Greenleaf as GreenleafMiddleware;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Singularity\Url;
use DecodeLabs\Singularity\Url\Http as HttpUrl;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;

class Context
{
    protected const Archetypes = [
        Generator::class => [],
        Action::class => ['named' => false],
    ];

    #[Plugin]
    public ArchetypeHandler $archetype;

    #[Plugin]
    public Router $router;

    public ?Container $container = null;


    /**
     * Init with namespace map
     */
    public function __construct(
        ?Container $container = null,
        ?ArchetypeHandler $archetype = null
    ) {
        $this->archetype = $archetype ?? new ArchetypeHandler();
        $this->container = $container;

        foreach (self::Archetypes as $interface => $options) {
            $options['interface'] = $interface;

            $this->archetype->register(
                new GreenleafResolver(...$options),
                unique: true
            );
        }

        $loader = $this->initLoader();
        $generator = $loader->loadGenerator();
        $this->router = $loader->loadRouter($generator);
    }

    protected function initLoader(): Loader
    {
        $loader = null;

        // Load loader
        if ($this->container instanceof PandoraContainer) {
            $loader = $this->container->tryGetWith(Loader::class, [
                'context' => $this,
                'archetype' => $this->archetype,
                'container' => $this->container
            ]);
        } elseif (
            $this->container &&
            $this->container->has(Loader::class)
        ) {
            if (!($loader = $this->container->get(Loader::class))
                instanceof Loader) {
                $loader = null;
            }
        }

        if (!$loader) {
            $class = $this->archetype->resolve(Loader::class);
            $loader = new $class($this);
        }

        return $loader;
    }


    /**
     * Create dispatcher
     */
    public function createDispatcher(): Dispatcher
    {
        return new GreenleafMiddleware($this);
    }



    /**
     * Load route for Request
     */
    public function matchIn(
        Request $request,
        bool $checkDir = false
    ): Hit {
        // Route request
        if (!$hit = $this->router->matchIn($request)) {
            if (
                $checkDir &&
                $hit = $this->testDirMatch($request)
            ) {
                return $hit;
            }

            throw Exceptional::RouteNotFound(
                message: 'Route not found: ' . $request->getUri()->getPath()
            );
        }

        return $hit;
    }

    /**
     * Test if tailing / affects match
     */
    protected function testDirMatch(
        Request $request
    ): ?Hit {
        $url = $request->getUri();
        $path = $url->getPath();

        if (str_ends_with($path, '/')) {
            $newPath = substr($path, 0, -1);
        } else {
            $newPath = $path . '/';
        }

        $url = $url->withPath($newPath);
        $request = $request->withUri($url);

        if (!$hit = $this->router->matchIn($request)) {
            return null;
        }

        return new Hit(
            new RedirectRoute($path, $newPath),
            [],
        );
    }

    /**
     * Load route for URI
     *
     * @param array<string, string|Stringable|int|float|null>|null $params
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $params = null
    ): Hit {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        if (!$hit = $this->router->matchOut($uri, $params)) {
            throw Exceptional::RouteNotMatched(
                message: 'Unable to match uri to route'
            );
        }

        return $hit;
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

        if (!$hit = $this->router->matchOut($uri, $params)) {
            throw Exceptional::RouteNotMatched(
                message: 'Unable to match uri to route'
            );
        }

        $route = $hit->getRoute();
        /** @var array<string, string|Stringable|float|int|null> */
        $params = $hit->parameters;
        $segments = $route->pattern->parseSegments();

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
        ?string $target = null,
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
Veneer\Manager::getGlobalManager()->register(
    Context::class,
    Greenleaf::class
);
