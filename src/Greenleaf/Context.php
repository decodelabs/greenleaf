<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Closure;
use DecodeLabs\Archetype\NamespaceMap;
use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Route\Action as ActionRoute;
use DecodeLabs\Greenleaf\Route\Redirect as RedirectRoute;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\LazyLoad;
use DecodeLabs\Veneer\Plugin;

#[LazyLoad]
class Context
{
    #[Plugin]
    public NamespaceMap $namespaces;

    /**
     * Init with namespace map
     */
    public function __construct(
        ?NamespaceMap $namespaces = null
    ) {
        $this->namespaces = $namespaces ?? new NamespaceMap();
    }





    /**
     * Create action route
     */
    public function route(
        string $pattern,
        string $target,
        ?Closure $setup = null
    ): ActionRoute {
        $output = new ActionRoute($pattern, $target);

        if ($setup) {
            $setup($output);
        }

        return $output;
    }

    /**
     * Create redirect route
     */
    public function redirect(
        string $pattern,
        string $target,
        ?Closure $setup = null
    ): RedirectRoute {
        $output = new RedirectRoute($pattern, $target);

        if ($setup) {
            $setup($output);
        }

        return $output;
    }
}


// Veneer
Veneer::register(
    Context::class,
    Greenleaf::class // @phpstan-ignore-line
);
