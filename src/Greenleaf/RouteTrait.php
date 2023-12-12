<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Compiler\Hit;
use DecodeLabs\Greenleaf\Compiler\Parameter;
use DecodeLabs\Greenleaf\Compiler\Parameter\Validator;
use DecodeLabs\Greenleaf\Compiler\Pattern;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\UriInterface as Uri;
use Stringable;

trait RouteTrait
{
    protected Pattern $pattern;

    /**
     * @var array<string, Parameter>
     */
    protected array $parameters = [];

    /**
     * @var array<string>
     */
    protected array $methods = [];

    /**
     * Normalize pattern
     */
    protected function normalizePattern(
        string|Pattern $pattern
    ): Pattern {
        if (is_string($pattern)) {
            $pattern = new Pattern($pattern);
        }

        return $pattern;
    }

    public function getPattern(): Pattern
    {
        return $this->pattern;
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

    /**
     * @return $this
     */
    public function addParameter(
        Parameter $parameter
    ): static {
        $this->parameters[$parameter->getName()] = $parameter;
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

    /**
     * @return array<string, Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
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

    /**
     * @return array<string>|null
     */
    public function getMethods(): ?array
    {
        if (empty($this->methods)) {
            return null;
        }

        return $this->methods;
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

                $parts[$i] = $this->parameters[$paramName]->getDefault();
                continue;
            }

            if ($segment->isMultiSegment()) {
                $part = implode('/', $parts);
                $parts = [];
            } else {
                $part = $parts[$i];
                unset($parts[$i]);
            }

            if (null === ($params = $segment->match($part))) {
                return null;
            }

            $parameters = array_merge($parameters, $params);
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

        return new Hit($this, $parameters);
    }

    /**
     * @param array<string, string|Stringable|int|float|null> $params
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $params = null
    ): ?Hit {
        // Only Uri base routes can match out
        return null;
    }
}
