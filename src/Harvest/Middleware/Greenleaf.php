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
use DecodeLabs\Harvest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Greenleaf implements
    Dispatcher,
    Middleware
{
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
        Request $request
    ): Response {
        if ($request->getMethod() === 'OPTIONS') {
            return Harvest::text('', 200, [
                'allow' => 'OPTIONS, GET, HEAD, POST'
            ]);
        }

        $hit = $this->context->matchIn($request, true);

        return $hit->getRoute()->handleIn(
            $this->context,
            $request,
            $hit->getParameters()
        );
    }

    /**
     * Handle request
     */
    public function process(
        Request $request,
        Handler $next
    ): Response {
        try {
            if ($request->getMethod() === 'OPTIONS') {
                return Harvest::text('', 200, [
                    'allow' => 'OPTIONS, GET, HEAD, POST'
                ]);
            }

            $hit = $this->context->matchIn($request, true);

            return $hit->getRoute()->handleIn(
                $this->context,
                $request,
                $hit->getParameters()
            );
        } catch (RouteNotFoundException $e) {
            return $next->handle($request);
        }
    }
}
