<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Archetype\Resolver\Greenleaf as GreenleafResolver;
use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Dispatcher;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Generator\Collector;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Greenleaf\Route\Redirect as RedirectRoute;
use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Greenleaf\Router\Caching as CachingRouter;
use DecodeLabs\Greenleaf\Router\PatternSwitch;
use DecodeLabs\Harvest\Middleware\Greenleaf as GreenleafMiddleware;
use DecodeLabs\Iota\Repository as IotaRepository;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use DecodeLabs\Singularity\Url;
use DecodeLabs\Singularity\Url\Http as HttpUrl;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Stringable;

class Greenleaf implements Service
{
    use ServiceTrait;

    protected const Archetypes = [
        Generator::class => [],
        Action::class => ['named' => false],
    ];

    public protected(set) IotaRepository $iotaRepo;

    public static function provideService(
        ContainerAdapter $container
    ): static {
        if (!$container->has(Router::class)) {
            $container->setType(Router::class, PatternSwitch::class);
        }

        if (!$container->has(Generator::class)) {
            $container->setType(Generator::class, Collector::class);
        }

        return $container->getOrCreate(static::class);
    }

    public function __construct(
        protected(set) Router $router,
        protected(set) Generator $generator,
        protected(set) Archetype $archetype,
        Iota $iota
    ) {
        $this->iotaRepo = Coercion::newLazyProxy(
            IotaRepository::class,
            fn () => $iota->loadStatic('greenleaf')
        );

        foreach (self::Archetypes as $interface => $options) {
            $options['interface'] = $interface;

            $this->archetype->register(
                new GreenleafResolver(...$options),
                unique: true
            );
        }

        $this->router = $router;
        $paths = Monarch::getPaths();

        if (!$paths->hasAlias('@pages')) {
            $paths->alias('@pages', '@run/src/@components/pages');
        }
    }


    public function createDispatcher(): Dispatcher
    {
        return new GreenleafMiddleware($this);
    }


    public function clearDevCache(): void
    {
        if (!Monarch::isDevelopment()) {
            return;
        }

        if ($this->router instanceof CachingRouter) {
            $this->router->clearCache();
        }
    }

    public function rebuildDevCache(): void
    {
        if (!Monarch::isDevelopment()) {
            return;
        }

        if ($this->router instanceof CachingRouter) {
            $this->router->rebuildCache();
        }
    }


    public function matchIn(
        PsrRequest $request,
        bool $checkDir = false
    ): ?Hit {
        if ($hit = $this->router->matchIn($request)) {
            return $hit;
        }

        if (
            $checkDir &&
            $hit = $this->testDirMatch($request)
        ) {
            return $hit;
        }

        return null;
    }

    protected function testDirMatch(
        PsrRequest $request
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
            throw Exceptional::{'./Greenleaf/RouteNotMatched'}(
                message: 'Unable to match uri to route'
            );
        }

        return $hit;
    }



    /**
     * @param string|Stringable|int|float|null ...$parameters
     */
    public function url(
        string|LeafUrl $uri,
        string|Stringable|int|float|bool|null ...$parameters
    ): Url {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        /** @var array<string,string|Stringable|int|float|bool|null> $parameters */
        if (!$hit = $this->router->matchOut($uri, $parameters)) {
            throw Exceptional::{'./Greenleaf/RouteNotMatched'}(
                message: 'Unable to match uri to route'
            );
        }

        $route = $hit->getRoute();

        if (!empty($hit->parameters)) {
            /** @var array<string,string|Stringable|float|int|bool|null> */
            $parameters = $hit->parameters;
            $segments = $route->pattern->parseSegments();

            foreach ($segments as $i => $segment) {
                $segments[$i] = $segment->resolve($parameters);
            }

            /** @var array<string> $segments */
            $path = implode('/', $segments);
        } else {
            $path = (string)$route->pattern;
        }

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        return new HttpUrl(
            scheme: null,
            path: $path,
            query: $hit->getQueryString(),
            fragment: $uri->getFragment(),
        );
    }
}
