<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Router;

use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Greenleaf\Router;
use DecodeLabs\Greenleaf\Router\PatternSwitch\InStep;
use DecodeLabs\Greenleaf\Router\PatternSwitch\OutMap;
use DecodeLabs\Greenleaf\RouterTrait;
use DecodeLabs\Iota;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Stringable;

class PatternSwitch implements Caching, Router
{
    use RouterTrait;
    use RouteCollectorTrait;

    protected ?Generator $generator;

    /**
     * Find route for request
     */
    public function matchIn(
        PsrRequest $request
    ): ?Hit {
        return $this->loadSwitches()->matchIn($request);
    }


    /**
     * Find route for leaf URI
     *
     * @param array<string,string|Stringable|int|float|bool|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit {
        return $this->loadSwitches()->matchOut($uri, $parameters);
    }

    protected function getGenerator(): Generator
    {
        if (!isset($this->generator)) {
            $this->generator = $this->context->loader->loadGenerator();
        }

        return $this->generator;
    }

    protected function loadSwitches(): Router
    {
        $repo = Iota::loadStatic('greenleaf');

        if (!$repo->has('patternSwitch')) {
            $repo->store('patternSwitch', $this->generateSwitchCode());
        }

        return $repo->returnAsType('patternSwitch', Router::class);
    }

    public function clearCache(): void
    {
        $repo = Iota::loadStatic('greenleaf');
        $repo->purge();
        $generator = $this->getGenerator();

        if ($generator instanceof Caching) {
            $generator->clearCache();
        }
    }

    public function rebuildCache(): void
    {
        $this->clearCache();
        $this->loadSwitches();
    }

    protected function generateSwitchCode(): string
    {
        $routes = $this->collectRoutes($this->getGenerator());
        $uses = [];

        $matchInString = str_replace("\n", "\n            ", $this->generateMatchIn($routes, $uses));
        $matchOutString = str_replace("\n", "\n        ", $this->generateMatchOut($routes, $uses));

        $uses['Hit'] = 'DecodeLabs\Greenleaf\Route\Hit';
        $uses['Router'] = 'DecodeLabs\Greenleaf\Router';
        $uses['Monarch'] = 'DecodeLabs\Monarch';
        $uses['LeafUrl'] = 'DecodeLabs\Singularity\Url\Leaf';
        $uses['PsrRequest'] = 'Psr\Http\Message\ServerRequestInterface';
        $uses['Throwable'] = 'Throwable';

        $uses = array_unique($uses);
        asort($uses);
        foreach ($uses as $key => $class) {
            $uses[$key] = "use $class as $key;";
        }

        $usesString = implode("\n", $uses);

        $code = <<<CODE
        <?php

        declare(strict_types=1);

        namespace DecodeLabs\Greenleaf\Router;

        {$usesString}

        return new class implements Router
        {
            public function matchIn(
                PsrRequest \$request
            ): ?Hit {
                try {
                    {$matchInString};
                } catch (Throwable \$e) {
                    if (Monarch::isDevelopment()) {
                        dd(\$e);
                    }

                    throw \$e;
                }
            }

            public function matchOut(
                string|LeafUrl \$uri,
                ?array \$parameters = null
            ): ?Hit {
                {$matchOutString};
            }
        };
        CODE;

        return $code;
    }

    /**
     * @param array<string,Route> $routes
     * @param array<string> $uses
     */
    protected function generateMatchIn(
        array $routes,
        array &$uses
    ): string {
        $root = new InStep();

        foreach ($routes as $route) {
            $class = get_class($route);
            $key = array_search($class, $uses, true);

            if (!$key) {
                $key = 'Route' . (count($uses) + 1);
                $uses[$key] = $class;
            }

            $segments = $route->pattern->parseSegments($route);

            $root->mapSegments(
                $segments,
                $route
            );
        }

        $output =
            <<<PHP
            \$path = ltrim(\$request->getUri()->getPath(), '/');

            if (\$path === '') {
                \$parts = [];
            } else {
                \$parts = explode('/', \$path);
            }

            \$params = [];
            \$method = \$request->getMethod();
            {$root->generateSwitches()}
            PHP;

        foreach ($uses as $key => $class) {
            $output = str_replace("\\$class::", "$key::", $output);
        }

        return $output;
    }

    /**
     * @param array<string,Route> $routes
     * @param array<string> $uses
     */
    protected function generateMatchOut(
        array $routes,
        array $uses
    ): string {
        $map = new OutMap();

        foreach ($routes as $route) {
            $map->mapRoute($route);
        }

        $output =
            <<<PHP
            \$parameters ??= [];

            if (is_string(\$uri)) {
                \$uri = LeafUrl::fromString(\$uri);
            }

            {$map->generateSwitches()}
            PHP;

        foreach ($uses as $key => $class) {
            $output = str_replace("\\$class::", "$key::", $output);
        }

        return $output;
    }
}
