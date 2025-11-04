<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use Stringable;

class Pattern implements Stringable, Dumpable
{
    protected string $pattern;


    public function __construct(
        string $pattern
    ) {
        $this->pattern = $this->normalize($pattern);
    }

    protected function normalize(
        string $pattern
    ): string {
        $pattern = '/' . ltrim($pattern, '/');

        return $pattern;
    }




    /**
     * @return array<Segment>
     */
    public function parseSegments(
        ?Route $route = null
    ): array {
        if ($this->pattern === '/') {
            return [];
        }

        $segments = explode('/', ltrim($this->pattern, '/'));

        foreach ($segments as $i => $segment) {
            $segments[$i] = $segment = Segment::fromString($i, $segment, $route);

            if (
                $segment->isMultiSegment() &&
                isset($segments[$i + 1])
            ) {
                throw Exceptional::UnexpectedValue(
                    message: 'Multi-segment parameters must be the last segment in a pattern'
                );
            }
        }

        /**
         * @var array<Segment>
         */
        return $segments;
    }


    public function __toString(): string
    {
        return $this->pattern;
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->text = $this->pattern;
        $entity->meta['segments'] = $this->parseSegments();
        return $entity;
    }
}
