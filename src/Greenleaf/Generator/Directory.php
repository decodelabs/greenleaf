<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Generator;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\Route\Action as ActionRoute;
use DecodeLabs\Greenleaf\Route\Parameter;
use ReflectionClass;

class Directory implements Generator, Orderable
{
    public int $priority = 10;
    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function generateRoutes(): iterable
    {
        yield from $this->loadFromUserlandGenerators();

        yield from $this->loadActions();
    }

    /**
     * @return iterable<Route|Generator>
     */
    private function loadFromUserlandGenerators(): iterable
    {
        $namespaces = $this->context->archetype->getNamespaceMap()->map(Generator::class);
        $slingshot = $this->context->newSlingshot();
        $generators = [];

        foreach($this->context->archetype->scanClasses(Generator::class) as $path => $class) {
            $generator = $slingshot->newInstance($class);
            $priority = $generator instanceof Orderable ? $generator->priority : 0;

            if($priority === 0) {
                if($local = $namespaces->localize($class)) {
                    $priority = count(explode('\\', $local)) * 10;
                } else {
                    $priority = 10;
                }
            }

            $generators[$class] = [$generator, $priority];
        }

        uasort($generators, function (array $a, array $b) {
            return $b[1] <=> $a[1];
        });


        foreach($generators as $generator) {
            yield from $generator[0]->generateRoutes();
        }
    }

    /**
     * @return iterable<Route|Generator>
     */
    private function loadActions(): iterable
    {
        $namespaces = $this->context->archetype->getNamespaceMap()->map(Action::class);

        foreach($this->context->archetype->scanClasses(Action::class) as $path => $class) {
            $ref = new ReflectionClass($class);
            $attributes = $ref->getAttributes();

            /** @var array<ActionRoute> */
            $routes = [];
            /** @var array<Parameter> */
            $parameters = [];

            foreach($attributes as $attribute) {
                if(is_a($attribute->name, Parameter::class, true)) {
                    $parameters[] = $attribute->newInstance();
                    continue;
                }

                if(is_a($attribute->name, Route::class, true)) {
                    $routes[] = $attribute->newInstance();
                    continue;
                }
            }

            if(empty($routes)) {
                $name = strtolower((string)preg_replace_callback(
                    '/([a-z])([A-Z])/',
                    function (array $matches) {
                        return $matches[1] . '-' . $matches[2];
                    },
                    $namespaces->localize($class) ?? $ref->getShortName()
                ));

                $routes[] = new ActionRoute(
                    pattern: $name,
                    method: 'get'
                );
            }

            /** @var ActionRoute $route */
            foreach($routes as $route) {
                /** @var Parameter $parameter */
                foreach($parameters as $parameter) {
                    $route->addParameter($parameter);
                }

                yield $route;
            }
        }
    }
}
