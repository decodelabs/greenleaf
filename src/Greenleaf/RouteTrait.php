<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use Closure;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Greenleaf\Route\Parameter;
use DecodeLabs\Greenleaf\Route\Parameter\Validator;
use DecodeLabs\Greenleaf\Route\Pattern;
use DecodeLabs\Harvest;
use DecodeLabs\Harvest\Dispatcher as MiddlewareDispatcher;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\UriInterface as Uri;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Stringable;

/**
 * @phpstan-require-implements Route
 */
trait RouteTrait
{
    protected(set) Pattern $pattern;

    /**
     * @var array<string,Parameter>
     */
    final protected(set) array $parameters = [];

    /**
     * @var array<string>
     */
    final protected(set) array $methods = [];

    protected function normalizePattern(
        string|Pattern $pattern
    ): Pattern {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return $pattern;
    }

    public function with(
        string $name,
        string|array|Validator|null $validate = null,
        ?string $default = null
    ): static {
        $parameter = new Parameter($name, $validate, $default);
        $this->addParameter($parameter);
        return $this;
    }

    public function parseParameters(): void
    {
        foreach($this->pattern->parseSegments($this) as $segment) {
            if(!$segment->isDynamic()) {
                continue;
            }

            foreach($segment->getParameters() as $name => $parameter) {
                if(!isset($this->parameters[$name])) {
                    $this->addParameter($parameter);
                }
            }
        }
    }

    /**
     * @return $this
     */
    public function addParameter(
        Parameter $parameter
    ): static {
        $this->parameters[$parameter->name] = $parameter;
        return $this;
    }

    public function getParameter(
        string $name
    ): ?Parameter {
        return $this->parameters[$name] ?? null;
    }

    public function hasParameter(
        string $name
    ): bool {
        return isset($this->parameters[$name]);
    }

    public function removeParameter(
        string $name
    ): static {
        unset($this->parameters[$name]);
        return $this;
    }




    public function forMethod(
        string ...$methods
    ): static {
        $this->methods = array_unique(array_map(strtoupper(...), $methods));
        return $this;
    }

    public function hasMethod(
        string $method
    ): bool {
        return in_array(strtoupper($method), $this->methods);
    }

    public function acceptsMethod(
        string $method
    ): bool {
        if (empty($this->methods)) {
            return true;
        }

        return in_array(strtoupper($method), array_merge($this->methods, ['OPTIONS', 'HEAD']));
    }

    public function removeMethod(
        string $method
    ): static {
        $method = strtoupper($method);

        foreach ($this->methods as $key => $value) {
            if ($value === $method) {
                unset($this->methods[$key]);
            }
        }

        return $this;
    }


    public function matchIn(
        string $method,
        Uri $uri
    ): ?Hit {
        if (!$this->acceptsMethod($method)) {
            return null;
        }

        $path = ltrim($uri->getPath(), '/');

        if ($path === '') {
            $parts = [];
        } else {
            $parts = explode('/', $path);
        }

        $parameters = [];

        foreach ($this->pattern->parseSegments($this) as $i => $segment) {
            if (!isset($parts[$i])) {
                if (!$segment->isWholeParameter()) {
                    return null;
                }

                $paramName = $segment->getParameterNames()[0];

                if (
                    !isset($this->parameters[$paramName]) ||
                    !$this->parameters[$paramName]->hasDefault()
                ) {
                    return null;
                }

                $parts[$i] = $this->parameters[$paramName]->default;
                continue;
            }

            if ($segment->isMultiSegment()) {
                $part = implode('/', $parts);
                $parts = [];
            } else {
                $part = $parts[$i];
                unset($parts[$i]);
            }

            if (null === ($segmentParameters = $segment->match($part))) {
                return null;
            }

            $parameters = array_merge($parameters, $segmentParameters);
        }

        foreach($this->parameters as $name => $parameter) {
            if (isset($parameters[$name])) {
                continue;
            }

            if ($parameter->hasDefault()) {
                $parameters[$name] = $parameter->default;
            } else {
                return null;
            }
        }

        if (!empty($parts)) {
            return null;
        }

        foreach ($parameters as $name => $value) {
            if (!isset($this->parameters[$name])) {
                continue;
            }

            if (!$this->parameters[$name]->validate($value)) {
                return null;
            }

            $parameters[$name] = $this->parameters[$name]->resolve($value);
        }

        /** @var array<string,mixed> $parameters */
        return new Hit($this, $parameters);
    }

    /**
     * @param array<string,string|Stringable|int|float|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit {
        // Only Uri base routes can match out
        return null;
    }


    /**
     * @param array<string,string|Stringable|int|float|null> $parameters
     */
    protected function matchActionOut(
        string|LeafUrl $uri,
        ?array $parameters,
        LeafUrl $target
    ): ?Hit {
        if (is_string($uri)) {
            $uri = LeafUrl::fromString($uri);
        }

        if ($uri->getPath() !== $target->getPath()) {
            return null;
        }

        $query = $uri->parseQuery();
        $targetQuery = $target->parseQuery();

        $queryParameters = [];

        foreach ($query as $key => $node) {
            if (
                !is_string($key) ||
                !$node->hasValue()
            ) {
                continue;
            }

            $queryParameters[$key] = $node->getValue();
        }

        foreach ($targetQuery->getKeys() as $key) {
            if (!isset($query->{$key})) {
                return null;
            }

            unset($queryParameters[$key]);
            unset($query->{$key});
        }

        $parameters = array_merge(
            $queryParameters,
            $parameters ?? []
        );

        return new Hit($this, $parameters, $query->toDelimitedString());
    }


    protected function dispatchAction(
        LeafRequest $request,
        Action $action
    ): Response {
        return $this->dispatchMiddleware(
            request: $request->httpRequest,
            middleware: $action->getMiddleware(),
            action: function(
                Request $httpRequest
            ) use($request, $action): Response {
                $output = $action->execute($request);

                return Harvest::transform($httpRequest, $output);
            }
        );
    }


    /**
     * @param ?array<string|class-string<Middleware>|Middleware|Closure(Request,Handler):Response> $middleware
     * @param Closure(Request):Response $action
     */
    protected function dispatchMiddleware(
        Request $request,
        ?array $middleware,
        Closure $action
    ): Response {
        if (empty($middleware)) {
            return $action($request);
        }

        $dispatcher = new MiddlewareDispatcher();

        $dispatcher->add(...$middleware);

        $dispatcher->add(function (
            Request $request,
            Handler $next
        ) use($action): Response {
            return $action($request);
        });

        return $dispatcher->handle($request);
    }

    /**
     * @param array<string,mixed> $values
     * @return array<string,mixed>
     */
    protected function exportData(
        array $values = []
    ): array {
        $values['class'] = get_class($this);
        $values['pattern'] = (string)$this->pattern;

        if (!empty($this->methods)) {
            $values['methods'] = $this->methods;
        }

        if (!empty($this->parameters)) {
            $values['parameters'] = [];

            foreach ($this->parameters as $name => $parameter) {
                $values['parameters'][$name] = $parameter->jsonSerialize();
            }
        }

        return $values;
    }
}
