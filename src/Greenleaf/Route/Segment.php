<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use Stringable;

class Segment implements
    Stringable,
    Dumpable
{
    protected readonly int $index;

    /**
     * @var array<string|Parameter>
     */
    protected readonly array $tokens;

    /**
     * @var array<string>
     */
    protected array $parameterNames;

    /**
     * Parse string
     */
    public static function fromString(
        int $index,
        string $segment,
        ?Route $route = null
    ): static {
        $tokens = preg_split('/(\{[a-zA-Z0-9_]+\})/', $segment, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($tokens === false) {
            throw Exceptional::UnexpectedValue(
                message: 'Unable to parse segment: ' . $segment
            );
        }

        foreach ($tokens as $i => $token) {
            if (preg_match('/^\{([a-zA-Z0-9_]+)\}$/', $token, $matches)) {
                $tokens[$i] = $route?->getParameter($matches[1]) ?? new Parameter($matches[1]);
            }
        }

        return new static($index, $tokens);
    }

    /**
     * Init with parts
     *
     * @param array<string|Parameter> $tokens
     */
    final public function __construct(
        int $index,
        array $tokens
    ) {
        $this->index = $index;
        $this->tokens = $tokens;
    }


    public function isDynamic(): bool
    {
        return !(
            count($this->tokens) === 1 &&
            is_string($this->tokens[0])
        );
    }


    /**
     * Get parameter names
     *
     * @return array<string>
     */
    public function getParameterNames(): array
    {
        if (!isset($this->parameterNames)) {
            $this->parameterNames = [];

            foreach ($this->tokens as $token) {
                if (!$token instanceof Parameter) {
                    continue;
                }

                $this->parameterNames[] = $token->name;
            }

            $this->parameterNames = array_unique($this->parameterNames);
        }

        return $this->parameterNames;
    }

    /**
     * Is whole parameter
     */
    public function isWholeParameter(): bool
    {
        return
            count($this->tokens) === 1 &&
            $this->tokens[0] instanceof Parameter;
    }

    /**
     * @return array<string,Parameter>
     */
    public function getParameters(): array
    {
        $params = [];

        foreach ($this->tokens as $token) {
            if ($token instanceof Parameter) {
                $params[$token->name] = $token;
            }
        }

        return $params;
    }

    /**
     * Is multi segment
     */
    public function isMultiSegment(): bool
    {
        if (!$this->isWholeParameter()) {
            return false;
        }

        /** @var Parameter $param */
        $param = $this->tokens[0];
        return $param->isMultiSegment();
    }

    /**
     * Check match
     *
     * @return array<?string>|null
     */
    public function match(
        string $part
    ): ?array {
        if ($part === '') {
            return empty($this->tokens) ? [] : null;
        }

        $regex = $this->compile();

        if (!preg_match($regex, $part, $matches)) {
            return null;
        }

        $params = [];

        foreach ($this->getParameterNames() as $name) {
            $params[$name] = $matches[$name];
        }

        return $params;
    }

    /**
     * Compile to regex
     */
    public function compile(): string
    {
        $parts = [];

        foreach ($this->tokens as $token) {
            if (is_string($token)) {
                $parts[] = preg_quote($token, '/');
                continue;
            }

            $parts[] = $token->getRegexFragment();
        }

        return '/^' . implode('', $parts) . '$/';
    }

    /**
     * Convert back to string
     *
     * @param array<string, string|Stringable|int|float|null> $parameters
     */
    public function resolve(
        array $parameters
    ): string {
        $output = [];

        foreach ($this->tokens as $token) {
            if (is_string($token)) {
                $output[] = $token;
                continue;
            }

            $name = $token->name;

            if (!isset($parameters[$name])) {
                throw Exceptional::UnexpectedValue(
                    message: 'Missing parameter value: ' . $name
                );
            }

            $output[] = Coercion::asString($parameters[$name]);
        }

        return implode('', $output);
    }

    public function __toString(): string
    {
        $output = [];

        foreach ($this->tokens as $token) {
            if (is_string($token)) {
                $output[] = $token;
                continue;
            }

            $str = '{' . $token->name;

            if ($token->isMultiSegment()) {
                $str .= '...';
            }

            $str .= '}';
            $output[] = $str;
        }

        return implode('', $output);
    }


    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->setProperty('index', $this->index, 'protected', readOnly: true);
        $entity->values = $this->tokens;
        return $entity;
    }
}
