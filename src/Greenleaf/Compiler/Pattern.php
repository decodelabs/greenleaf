<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler;

use DecodeLabs\Glitch\Dumpable;
use Stringable;

class Pattern implements Stringable, Dumpable
{
    protected string $pattern;

    /**
     * @var array<Segment>
     */
    protected array $segments;

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
     * Parse pattern
     */
    protected function parse(): void
    {
        $this->segments = [];

        if ($this->pattern === '/') {
            return;
        }

        $segments = explode('/', ltrim($this->pattern, '/'));

        foreach ($segments as $i => $segment) {
            $this->segments[$i] = Segment::fromString($i, $segment);
        }
    }


    /**
     * Get segments
     *
     * @return array<Segment>
     */
    public function getSegments(): array
    {
        if (!isset($this->segments)) {
            $this->parse();
        }

        return $this->segments;
    }

    /**
     * Get parameters
     *
     * @return array<string>
     */
    public function getParameters(): array
    {
        $output = [];

        foreach ($this->getSegments() as $segment) {
            $output = array_merge($output, $segment->getParameterNames());
        }

        return $output;
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

        if (!isset($this->segments)) {
            $this->parse();
        }

        yield 'meta' => [
            'segments' => $this->segments
        ];
    }
}
