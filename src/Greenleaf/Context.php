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
use DecodeLabs\Greenleaf\Context\Loader;
use DecodeLabs\Greenleaf\Route\Action as ActionRoute;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Greenleaf\Route\Page as PageRoute;
use DecodeLabs\Greenleaf\Route\Redirect as RedirectRoute;
use DecodeLabs\Greenleaf\Router\Caching as CachingRouter;
use DecodeLabs\Harvest\Middleware\Greenleaf as GreenleafMiddleware;
use DecodeLabs\Monarch;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Singularity\Url;
use DecodeLabs\Singularity\Url\Http as HttpUrl;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use DecodeLabs\Slingshot;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;
use Throwable;

class Context
{
    protected const Archetypes = [
        Generator::class => [],
        Action::class => ['named' => false],
    ];

    #[Plugin]
    public ArchetypeHandler $archetype;

    #[Plugin]
    public Loader $loader;

    #[Plugin]
    public Router $router;

    public ?Container $container = null;

    protected string $defaultPageType = 'html';

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

        $this->loader = $this->initLoader();
        $this->router = $this->loader->loadRouter();

        if(!Monarch::$paths->hasAlias('@pages')) {
            Monarch::$paths->alias('@pages', '@run/src/@components/pages');
        }
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


    public function createDispatcher(): Dispatcher
    {
        return new GreenleafMiddleware($this);
    }

    public function newSlingshot(): Slingshot
    {
        $slingshot = new Slingshot($this->container);
        $slingshot->addType($this);

        // @phpstan-ignore-next-line
        if(isset($this->router)) {
            $slingshot->addType($this->router);
        }

        return $slingshot;
    }


    public function setDefaultPageType(
        string $type
    ): void {
        $this->defaultPageType = $type;
    }

    public function getDefaultPageType(): string
    {
        return $this->defaultPageType;
    }


    /**
     * Load route for Request
     */
    public function matchIn(
        Request $request,
        bool $checkDir = false
    ): Hit {
        $clear = false;

        while(true) {
            try {
                if ($hit = $this->router->matchIn($request)) {
                    return $hit;
                }

                if (
                    $checkDir &&
                    $hit = $this->testDirMatch($request)
                ) {
                    return $hit;
                }
            } catch (Throwable $e) {
                if($clear) {
                    throw $e;
                }
            }

            if(
                !$clear &&
                Monarch::isDevelopment() &&
                $this->router instanceof CachingRouter
            ) {
                $clear = true;
                $this->router->clearCache();
                continue;
            }

            break;
        }

        throw Exceptional::RouteNotFound(
            message: 'Route not found: ' . $request->getUri()->getPath()
        );
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
     * @param array<string,string|Stringable|int|float|null>|null $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): Hit {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        if (!$hit = $this->router->matchOut($uri, $parameters)) {
            throw Exceptional::RouteNotMatched(
                message: 'Unable to match uri to route'
            );
        }

        return $hit;
    }



    /**
     * Create URL from uri
     *
     * @param string|Stringable|int|float|null ...$parameters
     */
    public function url(
        string|LeafUrl $uri,
        string|Stringable|int|float|null ...$parameters
    ): Url {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        /** @var array<string,string|Stringable|int|float|null> $parameters */
        if (!$hit = $this->router->matchOut($uri, $parameters)) {
            throw Exceptional::RouteNotMatched(
                message: 'Unable to match uri to route'
            );
        }

        $route = $hit->getRoute();
        /** @var array<string,string|Stringable|float|int|null> */
        $parameters = $hit->parameters;
        $segments = $route->pattern->parseSegments();

        foreach ($segments as $i => $segment) {
            $segments[$i] = $segment->resolve($parameters);
        }

        /** @var array<string> $segments */
        $path = implode('/', $segments);

        if(!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

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
    public function action(
        string $pattern,
        ?string $target = null
    ): ActionRoute {
        return new ActionRoute($pattern, $target);
    }

    /**
     * Create page route
     */
    public function page(
        string $pattern,
        ?string $target = null
    ): PageRoute {
        return new PageRoute($pattern, $target);
    }

    /**
     * Create redirect route
     */
    public function redirect(
        string $pattern,
        string $target
    ): RedirectRoute {
        return new RedirectRoute($pattern, $target);
    }
}


// Veneer
Veneer\Manager::getGlobalManager()->register(
    Context::class,
    Greenleaf::class
);
