<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Closure;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

interface Action
{
    /**
     * @var array<string|class-string<Middleware>>
     */
    public const array Middleware = [];

    public function __construct(
        Context $context
    );

    /**
     * Get middleware list
     *
     * @return array<string|class-string<Middleware>|Middleware|Closure(Request,Handler):Response>
     */
    public function getMiddleware(): ?array;

    public function execute(
        LeafRequest $request
    ): mixed;
}
