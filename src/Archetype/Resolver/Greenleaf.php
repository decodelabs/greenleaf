<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Archetype\Resolver;

use DecodeLabs\Archetype\NamespaceMap;
use DecodeLabs\Archetype\Scanner;
use DecodeLabs\Archetype\ScannerTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Generator;

class Greenleaf implements Scanner
{
    use ScannerTrait;

    /**
     * @var class-string
     */
    protected string $interface;
    protected string $interfaceName;

    protected NamespaceMap $namespaces;
    protected bool $named = false;
    protected bool $local = false;

    /**
     * Init with interface and namespace map
     *
     * @param class-string $interface
     */
    public function __construct(
        string $interface,
        NamespaceMap $namespaces,
        bool $named = false,
        bool $local = false
    ) {
        $this->interface = $interface;
        $parts = explode('\\', $interface);
        $this->interfaceName = (string)array_pop($parts);
        $this->namespaces = $namespaces;
        $this->named = $named;
        $this->local = $local;
    }

    /**
     * Get mapped interface
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * Get resolver priority
     */
    public function getPriority(): int
    {
        return 25;
    }

    /**
     * Resolve Archetype class location
     */
    public function resolve(
        string $name
    ): ?string {
        if (str_contains($name, '/')) {
            $name = LeafUrl::fromString($name)->toClassName();
        } else {
            $name = ucfirst($name);
        }

        $name = trim($name, '\\');
        $classes = [];

        if ($this->named) {
            $name .= $this->interfaceName;
        }

        if (
            $this->local &&
            !str_contains($name, '\\')
        ) {
            $classes[] = $this->interface . '\\' . $name;
        }

        foreach ($this->namespaces as $namespace) {
            $classes[] = $namespace . '\\' . $name;
        }

        foreach (array_reverse($classes) as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Scan for available for classes
     */
    public function scanClasses(): Generator
    {
        if($this->local) {
            yield from $this->scanNamespaceClasses($this->interface);
        }

        foreach ($this->namespaces as $namespace) {
            yield from $this->scanNamespaceClasses($namespace, $this->interface);
        }
    }
}
