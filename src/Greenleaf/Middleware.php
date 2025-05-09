<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Attribute;
use Closure;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\MiddlewareInterface as PsrMiddleware;
use Psr\Http\Server\RequestHandlerInterface as PsrHandler;

#[Attribute(
    Attribute::TARGET_CLASS |
    Attribute::TARGET_FUNCTION
)]
class Middleware
{
    /**
     * @var string|class-string<PsrMiddleware>|PsrMiddleware|Closure(PsrRequest,PsrHandler):PsrResponse
     */
    protected(set) string|PsrMiddleware|Closure $middleware;

    /**
     * @var array<string,mixed>
     */
    protected(set) array $parameters;

    /**
     * @param string|class-string<PsrMiddleware>|PsrMiddleware|Closure(PsrRequest, PsrHandler):PsrResponse $middleware
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        string|PsrMiddleware|Closure $middleware,
        array $parameters = []
    ) {
        $this->middleware = $middleware;
        $this->parameters = $parameters;
    }
}
