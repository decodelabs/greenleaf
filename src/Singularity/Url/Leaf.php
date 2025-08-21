<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Singularity\Url;

use DecodeLabs\Dictum;
use DecodeLabs\Exceptional;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
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

    public static function fromString(
        string $uri
    ): static {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw Exceptional::InvalidArgument(
                message: 'Unable to parse uri: ' . $uri,
                data: $uri
            );
        }

        $path = $parts['path'] ?? null;
        $host = $parts['host'] ?? null;

        if (
            $host !== null &&
            $host !== ''
        ) {
            $path = $host . $path;
            $host = null;
        }

        return new static(
            path: $path,
            query: $parts['query'] ?? null,
            fragment: $parts['fragment'] ?? null
        );
    }

    final public function __construct(
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null
    ) {
        if (!str_starts_with((string)$path, '/')) {
            $path = '/' . $path;
        }

        $this->path = static::normalizePath($path);
        $this->query = static::normalizeQuery($query);
        $this->fragment = static::normalizeFragment($fragment);
    }



    public function __toString(): string
    {
        $output = 'leaf:';

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

    public function toClassName(): string
    {
        $output = '';

        if (
            $this->path !== null &&
            $this->path !== '/'
        ) {
            $parts = explode('/', trim($this->path, '/'));
            $parts = array_map(Dictum::id(...), $parts);
            $output .= '\\' . implode('\\', $parts);
        } else {
            $output .= '\\Index';
        }

        return $output;
    }

    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->definition = $this->__toString();

        $entity->meta = [
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment
        ];

        return $entity;
    }
}
