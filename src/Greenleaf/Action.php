<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Closure;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

interface Action
{
    public function __construct(
        Context $context
    );

    /**
     * Get middleware list
     *
     * @return array<string|class-string<Middleware>|Middleware|Closure(Request, Handler):Response>
     */
    public function getMiddleware(): ?array;

    /**
     * @param array<string, mixed> $parameters
     */
    public function execute(
        Request $request,
        LeafUrl $url,
        array $parameters
    ): Response;
}
