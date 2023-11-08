<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Archetype;
use DecodeLabs\Greenleaf\Action as ActionInterface;
use DecodeLabs\Greenleaf\Compiler\Pattern;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
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
        Request $request,
        array $parameters
    ): Response {
        $uri = LeafUrl::fromString($this->target);

        $class = Archetype::resolve(ActionInterface::class, (string)$uri);
        $action = new $class();

        return $action->execute($request, $uri, $parameters);
    }
}
