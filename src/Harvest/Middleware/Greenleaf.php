<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Harvest\Middleware;

use DecodeLabs\Greenleaf\Dispatcher;
use DecodeLabs\Greenleaf\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Greenleaf extends Dispatcher implements Middleware
{
    /**
     * Handle request
     */
    final public function process(
        Request $request,
        Handler $next
    ): Response {
        try {
            return $this->findRoute($request)
                ->handle($request);
        } catch (NotFoundException $e) {
            return $next->handle($request);
        }
    }
}
