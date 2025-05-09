<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Harvest\Middleware;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Dispatcher;
use DecodeLabs\Greenleaf\RouteNotFoundException;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Harvest\NotFoundException as HarvestNotFoundException;
use DecodeLabs\Harvest\Middleware as HarvestMiddleware;
use DecodeLabs\Harvest\MiddlewareGroup;
use DecodeLabs\Monarch;
use Exception;
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
        if($hit = $this->getHit($request)) {
            return $hit->getRoute()->handleIn(
                $this->context,
                $request,
                $hit->parameters
            );
        }

        throw Exceptional::{'RouteNotFound,Notfound'}(
            message: 'No route found for: '.$request->getUri()->getPath(),
            data: $request,
            namespace: 'DecodeLabs\\Greenleaf',
            http: 404
        );
    }

    /**
     * Handle request
     */
    public function process(
        PsrRequest $request,
        PsrHandler $next
    ): PsrResponse {
        if($hit = $this->getHit($request)) {
            return $hit->getRoute()->handleIn(
                $this->context,
                $request,
                $hit->parameters
            );
        }

        try {
            return $next->handle($request);
        } catch (HarvestNotFoundException $f) {
            if(Monarch::isDevelopment()) {
                // See if rebuilding the router helps
                $this->context->clearDevCache();

                if($hit = $this->getHit($request)) {
                    return $hit->getRoute()->handleIn(
                        $this->context,
                        $request,
                        $hit->parameters
                    );
                }
            }

            throw Exceptional::{'RouteNotFound,Notfound'}(
                message: 'No route found for: '.$request->getUri()->getPath(),
                data: $request,
                namespace: 'DecodeLabs\\Greenleaf',
                http: 404,
                previous: $f
            );
        }
    }

    /**
     * Perform routing
     */
    protected function getHit(
        PsrRequest &$request
    ): ?Hit {
        $hit = $this->context->matchIn($request, true);
        $request = $request->withAttribute('route', $hit?->getRoute());
        return $hit;
    }
}
