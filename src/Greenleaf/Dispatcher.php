<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Dispatcher implements Handler
{
    /**
     * Begin stage stack navigation
     */
    public function handle(
        Request $request
    ): Response {
        dd($request);
    }
}
