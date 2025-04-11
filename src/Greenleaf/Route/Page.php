<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\Compiler\Hit;
use DecodeLabs\Greenleaf\Compiler\Pattern;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\PageAction;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Monarch;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stringable;

class Page implements Route
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
        if(!Monarch::$paths->hasAlias('@pages')) {
            Monarch::$paths->alias('@pages', '@run/src/@components/pages');
        }

        $type = $this->target->parsePath()?->getExtension();

        if(!$type) {
            $type = $context->getDefaultPageType();

            $this->target = $this->target->withPath(function($path) use($type) {
                return $path?->withExtension($type) ?? '.' . $type;
            });
        }

        if(!$class = $context->archetype->tryResolve(
            interface: PageAction::class,
            names: ucfirst($type)
        )) {
            throw Exceptional::NotFound(
                message: 'No page handler for type "' . $type . '"',
                http: 404
            );
        }

        $action = new $class($context);

        $leafRequest = new LeafRequest(
            httpRequest: $request,
            leafUrl: $this->target,
            parameters: $parameters,
            route: $this
        );

        return $this->dispatchAction(
            request: $leafRequest,
            action: $action,
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
