<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Singularity\Url;

use DecodeLabs\Dictum;
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

        $path = $parts['path'] ?? null;
        $host = $parts['host'] ?? null;

        if (
            $host !== null &&
            $host !== '' &&
            !str_starts_with($host, '~')
        ) {
            $path = $host . $path;
            $host = null;
        }

        return new static(
            area: $host,
            path: $path,
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
        if ($area === null) {
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

        if ($this->area !== 'front') {
            $output .= '~' . $this->area;
        }

        if ($this->path !== null) {
            $path = $this->path;

            if ($this->area === 'front') {
                $path = ltrim($path, '/');
            }

            $output .= $path;
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
     * Convert to class name string
     */
    public function toClassName(): string
    {
        $output = Dictum::id($this->area ?? 'Front');

        if (
            $this->path !== null &&
            $this->path !== '/'
        ) {
            $parts = explode('/', trim($this->path, '/'));
            $parts = array_map(Dictum::id(...), $parts);
            $output .= '\\' . implode('\\', $parts);
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
