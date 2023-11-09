<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Archetype;
use DecodeLabs\Archetype\Resolver\Greenleaf as GreenleafResolver;
use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf;
use DecodeLabs\Greenleaf\Compiler\Hit;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Dispatcher implements Handler
{
    protected static bool $setup = false;

    public function __construct()
    {
        $this->setupArchetype();
    }

    /**
     * Setup archetype
     */
    protected static function setupArchetype(): void
    {
        if (Dispatcher::$setup) {
            return;
        }

        Archetype::register(
            new GreenleafResolver(
                interface: Action::class,
                namespaces: Greenleaf::$namespaces,
                named: true
            ),
            unique: true
        );

        Archetype::register(
            new GreenleafResolver(
                interface: Generator::class,
                namespaces: Greenleaf::$namespaces,
            ),
            unique: true
        );

        Dispatcher::$setup = true;
    }

    /**
     * Begin stage stack navigation
     */
    final public function handle(
        Request $request
    ): Response {
        $hit = $this->matchIn($request);
        return $hit->getRoute()->handle($request, $hit->getParameters());
    }

    /**
     * Load route for Request
     */
    public function matchIn(
        Request $request
    ): Hit {
        // Route request
        if (!$hit = Greenleaf::getRouter()->matchIn($request)) {
            throw Exceptional::RouteNotFound(
                'Route not found: ' . $request->getUri()->getPath()
            );
        }

        return $hit;
    }
}
