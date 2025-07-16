<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Coercion;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Iota;

class Collector implements Generator, Caching, Orderable
{
    public int $priority = 1;

    /**
     * @var array<class-string<Generator>>
     */
    protected array $generatorNames = [
        Directory::class,
        Pages::class,
    ];

    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function addGenerator(
        string $name
    ): void {
        $class = $this->context->archetype->resolve(Generator::class, $name);

        if (!in_array($class, $this->generatorNames)) {
            return;
        }

        $this->generatorNames[] = $class;
    }

    public function generateRoutes(): iterable
    {
        $routes = $this->loadRouteData();

        foreach ($routes as $i => $route) {
            /** @var class-string<Route> */
            $class = $route['class'];
            yield $class::fromArray($route);
        }
    }

    /**
     * @return array<array<string,mixed>>
     */
    protected function loadRouteData(): array
    {
        $repo = Iota::loadStatic('greenleaf');

        if ($repo->has('routes')) {
            $output = $repo->return('routes');
        } else {
            $output = $this->generateRouteData();
            $repo->storeStaticArray('routes', $output);
        }

        /** @var array<array<string,mixed>> */
        return $output;
    }

    /**
     * @return array<array<string,mixed>>
     */
    protected function generateRouteData(): array
    {
        /** @var array<array<string,mixed>> */
        $routes = [];

        foreach ($this->scanGenerators() as $generator) {
            foreach ($this->scanGeneratorRoutes($generator) as $route) {
                $routes[] = $route->jsonSerialize();
            }
        }

        // @phpstan-ignore-next-line
        usort($routes, function (array $a, array $b) {
            $partsA = explode('/', ltrim(Coercion::asString($a['pattern']), '/'));
            $partsB = explode('/', ltrim(Coercion::asString($b['pattern']), '/'));
            $countA = count($partsA);
            $countB = count($partsB);

            if ($countA !== $countB) {
                return $countB <=> $countA;
            }

            foreach ($partsA as $i => $partA) {
                $partB = (string)($partsB[$i] ?? '');

                if ($partA === $partB) {
                    continue;
                }

                if (preg_match('/^\{[a-zA-Z0-9_]+\}$/', $partA)) {
                    return 1;
                }

                if (preg_match('/^\{[a-zA-Z0-9_]+\}$/', $partB)) {
                    return -1;
                }

                return $partB <=> $partA;
            }

            return 0;
        });

        /** @var array<array<string,mixed>> */
        return $routes;
    }

    /**
     * @return iterable<Route>
     */
    protected function scanGeneratorRoutes(
        Generator $generator
    ): iterable {
        foreach ($generator->generateRoutes() as $route) {
            if ($route instanceof Generator) {
                yield from $this->scanGeneratorRoutes($route);
                continue;
            }

            $route->parseParameters();
            yield $route;
        }
    }

    /**
     * @return iterable<Generator>
     */
    protected function scanGenerators(): iterable
    {
        $classes = $generators = [];
        $slingshot = $this->context->newSlingshot();

        foreach ($this->generatorNames as $name) {
            $class = $this->context->archetype->resolve(Generator::class, $name);

            if (in_array($class, $classes)) {
                continue;
            }

            $generators[] = $slingshot->newInstance($class);
        }

        uasort($generators, function (Generator $a, Generator $b) {
            return
                ($b instanceof Orderable ? $b->priority : 0) <=>
                ($a instanceof Orderable ? $a->priority : 0);
        });

        return $generators;
    }


    public function clearCache(): void
    {
        $repo = Iota::loadStatic('greenleaf');
        $repo->purge();
    }
}
