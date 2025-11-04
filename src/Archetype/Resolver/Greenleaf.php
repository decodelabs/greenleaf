<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Archetype\Resolver;

use DecodeLabs\Archetype\ResolverTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Generator;

class Greenleaf implements Scanner
{
    use ResolverTrait;
    use ScannerTrait;

    /**
     * @var class-string
     */
    protected string $interface;
    protected string $interfaceName;

    protected bool $named = false;
    protected bool $local = false;

    /**
     * @param class-string $interface
     */
    public function __construct(
        string $interface,
        bool $named = false,
        bool $local = false
    ) {
        $this->interface = $interface;
        $parts = explode('\\', $interface);
        $this->interfaceName = (string)array_pop($parts);
        $this->named = $named;
        $this->local = $local;
    }

    public function getInterface(): string
    {
        return $this->interface;
    }

    public function getPriority(): int
    {
        return 25;
    }

    public function resolve(
        string $name
    ): ?string {
        if (str_contains($name, '/')) {
            $name = LeafUrl::fromString($name)->toClassName();
        } else {
            $name = ucfirst($name);
        }

        $name = trim($name, '\\');

        if ($this->named) {
            $name .= $this->interfaceName;
        }

        foreach ($this->namespaces->map(
            namespace: $this->interface,
            includeRoot: $this->local
        ) as $namespace) {
            if (
                $namespace === $this->interface &&
                !$this->local
            ) {
                continue;
            }

            $class = $namespace . '\\' . $name;

            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    public function scanClasses(): Generator
    {
        foreach ($this->namespaces->map($this->interface, $this->local) as $namespace) {
            yield from $this->scanNamespaceClasses($namespace, $this->interface);
        }
    }
}
