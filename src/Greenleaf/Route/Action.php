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
use DecodeLabs\Harvest\Dispatcher as MiddlewareDispatcher;
use DecodeLabs\Harvest\ResponseProxy;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
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

        if (null !== ($middleware = $action->getMiddleware())) {
            $dispatcher = new MiddlewareDispatcher();

            $dispatcher->add(...$middleware);

            $dispatcher->add(function (
                Request $request,
                Handler $next
            ) use ($action, $parameters): Response {
                $output = $action->execute(
                    new LeafRequest(
                        httpRequest: $request,
                        leafUrl: $this->target,
                        parameters: $parameters,
                        route: $this
                    )
                );

                return Harvest::transform($request, $output);
            });

            return $dispatcher->handle($request);
        }

        $output = $action->execute(
            new LeafRequest(
                httpRequest: $request,
                leafUrl: $this->target,
                parameters: $parameters,
                route: $this
            )
        );

        return Harvest::transform($request, $output);
    }


    /**
     * @param array<string,string|Stringable|int|float|null> $params
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $params = null
    ): ?Hit {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        if ($uri->getPath() !== $this->target->getPath()) {
            return null;
        }

        $query = $uri->parseQuery();
        $targetQuery = $this->target->parseQuery();

        $parameters = [];

        foreach ($query as $key => $node) {
            if (
                !is_string($key) ||
                !$node->hasValue()
            ) {
                continue;
            }

            $parameters[$key] = $node->getValue();
        }

        foreach ($targetQuery->getKeys() as $key) {
            if (!isset($query->{$key})) {
                return null;
            }

            unset($parameters[$key]);
            unset($query->{$key});
        }

        $parameters = array_merge(
            $parameters,
            $params ?? []
        );

        return new Hit($this, $parameters, $query->toDelimitedString());
    }
}
