<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Greenleaf\Action as ActionInterface;
use DecodeLabs\Greenleaf\Compiler\Hit;
use DecodeLabs\Greenleaf\Compiler\Pattern;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Harvest;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;

class Action implements Route
{
    use RouteTrait;

    protected LeafUrl $target;

    /**
     * Init with properties
     */
    public function __construct(
        string|Pattern $pattern,
        string|LeafUrl|null $target
    ) {
        $this->pattern = $this->normalizePattern($pattern);

        if ($target === null) {
            $target = (string)$pattern;
        }

        if (is_string($target)) {
            $target = LeafUrl::fromString($target);
        }

        $this->target = $target;
    }

    /**
     * Get target
     */
    public function getTarget(): LeafUrl
    {
        return $this->target;
    }


    /**
     * Handle request
     */
    public function handleIn(
        Context $context,
        Request $request,
        array $parameters
    ): Response {
        $class = $context->archetype->resolve(ActionInterface::class, (string)$this->target);
        $action = new $class($context);

        $leafRequest = new LeafRequest(
            httpRequest: $request,
            leafUrl: $this->target,
            parameters: $parameters,
            route: $this
        );

        return $this->dispatchAction(
            request: $leafRequest,
            action: $action
        );
    }


    /**
     * @param array<string,string|Stringable|int|float|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit {
        return $this->matchActionOut(
            uri: $uri,
            parameters: $parameters,
            target: $this->target
        );
    }
}
