<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Generator;

use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Generator;

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
}
