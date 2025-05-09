<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Harvest\Middleware;

use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Dispatcher;
use DecodeLabs\Greenleaf\RouteNotFoundException;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Harvest\Middleware as HarvestMiddleware;
use DecodeLabs\Harvest\MiddlewareGroup;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\RequestHandlerInterface as PsrHandler;

class Greenleaf implements
    Dispatcher,
    HarvestMiddleware
{
    public MiddlewareGroup $group {
        get => MiddlewareGroup::Generator;
    }

    public int $priority {
        get => 0;
    }

    protected Context $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Begin stage stack navigation
     */
    public function handle(
        PsrRequest $request
    ): PsrResponse {
        $hit = $this->getHit($request);

        return $hit->getRoute()->handleIn(
            $this->context,
            $request,
            $hit->parameters
        );
    }

    /**
     * Handle request
     */
    public function process(
        PsrRequest $request,
        PsrHandler $next
    ): PsrResponse {
        try {
            $hit = $this->getHit($request);

            return $hit->getRoute()->handleIn(
                $this->context,
                $request,
                $hit->parameters
            );
        } catch (RouteNotFoundException $e) {
            return $next->handle($request);
        }
    }

    /**
     * Perform routing
     */
    protected function getHit(
        PsrRequest &$request
    ): Hit {
        $hit = $this->context->matchIn($request, true);
        $request = $request->withAttribute('route', $hit->getRoute());
        return $hit;
    }
}
