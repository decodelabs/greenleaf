<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Harvest\Middleware;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf as GreenleafService;
use DecodeLabs\Greenleaf\Dispatcher;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Harvest\Middleware as HarvestMiddleware;
use DecodeLabs\Harvest\MiddlewareGroup;
use DecodeLabs\Harvest\NotFoundException as HarvestNotFoundException;
use DecodeLabs\Monarch;
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


    public function __construct(
        protected GreenleafService $greenleaf,
    ) {
    }

    public function handle(
        PsrRequest $request
    ): PsrResponse {
        if ($hit = $this->getHit($request)) {
            return $hit->getRoute()->handleIn(
                $request,
                $hit->parameters,
                $this->greenleaf->archetype,
            );
        }

        throw Exceptional::{'RouteNotFound,Notfound'}(
            message: 'No route found for: ' . $request->getUri()->getPath(),
            data: $request,
            namespace: 'DecodeLabs\\Greenleaf',
            http: 404
        );
    }

    public function process(
        PsrRequest $request,
        PsrHandler $next
    ): PsrResponse {
        if ($hit = $this->getHit($request)) {
            return $hit->getRoute()->handleIn(
                $request,
                $hit->parameters,
                $this->greenleaf->archetype,
            );
        }

        try {
            return $next->handle($request);
        } catch (HarvestNotFoundException $f) {
            if (Monarch::isDevelopment()) {
                // See if rebuilding the router helps
                $this->greenleaf->clearDevCache();

                if ($hit = $this->getHit($request)) {
                    return $hit->getRoute()->handleIn(
                        $request,
                        $hit->parameters,
                        $this->greenleaf->archetype,
                    );
                }
            }

            throw Exceptional::{'RouteNotFound,Notfound'}(
                message: 'No route found for: ' . $request->getUri()->getPath(),
                data: $request,
                namespace: 'DecodeLabs\\Greenleaf',
                http: 404,
                previous: $f
            );
        }
    }


    protected function getHit(
        PsrRequest &$request
    ): ?Hit {
        $hit = $this->greenleaf->matchIn($request, true);
        $request = $request->withAttribute('route', $hit?->getRoute());
        return $hit;
    }
}
