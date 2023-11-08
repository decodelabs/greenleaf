<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Greenleaf\Compiler\Pattern;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Action implements Route
{
    use RouteTrait;

    protected string $target;

    /**
     * Init with properties
     */
    public function __construct(
        string|Pattern $pattern,
        string $target
    ) {
        $this->pattern = $this->normalizePattern($pattern);
        $this->target = $target;
    }

    /**
     * Get target
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Handle request
     */
    public function handle(
        Request $request
    ): Response {
        dd($request);
    }
}
