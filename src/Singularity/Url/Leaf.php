<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Singularity\Url;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Singularity\Url;

class Leaf implements
    Url,
    Dumpable
{
    use NoSchemeTrait;
    use NoUserInfoTrait;
    use NoHostTrait;
    use NoPortTrait;
    use AuthorityTrait;
    use PathTrait;
    use QueryTrait;
    use FragmentTrait;

    protected string $area;

    /**
     * Parse string
     */
    public static function fromString(
        string $uri
    ): static {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw Exceptional::InvalidArgument(
                'Unable to parse uri: ' . $uri,
                null,
                $uri
            );
        }

        return new static(
            area: $parts['host'] ?? null,
            path: $parts['path'] ?? null,
            query: $parts['query'] ?? null,
            fragment: $parts['fragment'] ?? null
        );
    }

    /**
     * Init with parts
     */
    final public function __construct(
        ?string $area = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null
    ) {
        $this->area = static::normalizeArea($area);
        $this->path = static::normalizePath($path);
        $this->query = static::normalizeQuery($query);
        $this->fragment = static::normalizeFragment($fragment);
    }

    /**
     * Replace area
     */
    public function withArea(
        ?string $area
    ): static {
        $output = clone $this;
        $output->area = static::normalizeArea($area);
        return $output;
    }

    /**
     * Get area
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * Normalize area
     */
    public static function normalizeArea(
        string|null $area
    ): string {
        if($area === null) {
            $area = 'front';
        }

        return ltrim($area, '~');
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        $output = 'leaf://';

        if($this->area !== 'front') {
            $output .= '~'.$this->area;
        }

        if ($this->path !== null) {
            $output .= $this->path;
        }

        if ($this->query !== null) {
            $output .= '?' . $this->query;
        }

        if ($this->fragment !== null) {
            $output .= '#' . $this->fragment;
        }

        return $output;
    }

    /**
     * Dump for glitch
     */
    public function glitchDump(): iterable
    {
        yield 'definition' => $this->__toString();

        yield 'meta' => [
            'area' => $this->area,
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment
        ];
    }
}
