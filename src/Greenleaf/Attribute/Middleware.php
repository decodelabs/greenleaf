<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Attribute;

use Attribute;
use Closure;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Server\MiddlewareInterface as PsrMiddleware;
use Psr\Http\Server\RequestHandlerInterface as PsrHandler;

#[Attribute(
    Attribute::TARGET_CLASS
)]
class Middleware
{
    protected string|PsrMiddleware|Closure $middleware;

    /**
     * @var array<string, mixed>
     */
    protected array $parameters;

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

    /**
     * Get middleware
     *
     * @return string|class-string<PsrMiddleware>|PsrMiddleware|Closure(PsrRequest, PsrHandler):PsrResponse
     */
    public function getMiddleware(): string|PsrMiddleware|Closure
    {
        return $this->middleware;
    }

    /**
     * Get parameters
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
