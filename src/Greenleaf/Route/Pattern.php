<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Greenleaf\Route;
use Stringable;

class Pattern implements Stringable, Dumpable
{
    protected string $pattern;


    /**
     * Init with pattern
     */
    public function __construct(
        string $pattern
    ) {
        $this->pattern = $this->normalize($pattern);
    }

    /**
     * Normalize pattern
     */
    protected function normalize(
        string $pattern
    ): string {
        $pattern = '/' . ltrim($pattern, '/');

        return $pattern;
    }




    /**
     * Get segments
     *
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


    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->pattern;
    }


    /**
     * Dump for glitch
     */
    public function glitchDump(): iterable
    {
        yield 'text' => $this->pattern;

        yield 'meta' => [
            'segments' => $this->parseSegments()
        ];
    }
}
