<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Greenleaf\Route;

class Segment implements Dumpable
{
    protected int $index;

    /**
     * @var array<string|Parameter>
     */
    protected array $tokens;

    /**
     * Parse string
     */
    public static function fromString(
        int $index,
        string $segment,
    ): static {
        $tokens = preg_split('/(\{[a-zA-Z0-9_]+\})/', $segment, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($tokens === false) {
            throw Exceptional::UnexpectedValue(
                'Unable to parse segment: ' . $segment
            );
        }

        foreach ($tokens as $i => $token) {
            if (preg_match('/^\{([a-zA-Z0-9_]+)\}$/', $token, $matches)) {
                $tokens[$i] = new Parameter($matches[1]);
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


    /**
     * Get parameter names
     *
     * @return array<string>
     */
    public function getParameterNames(): array
    {
        $output = [];

        foreach ($this->tokens as $token) {
            if (!$token instanceof Parameter) {
                continue;
            }

            $output[] = $token->getName();
        }

        return array_unique($output);
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
     * Check match
     */
    public function matches(
        Route $route,
        string $part
    ): bool {
        if ($part === '') {
            return empty($this->tokens);
        }

        $regex = $this->compile($route);
        return (bool)preg_match($regex, $part);
    }

    /**
     * Compile to regex
     */
    public function compile(
        Route $route
    ): string {
        $parts = [];

        foreach ($this->tokens as $token) {
            if (is_string($token)) {
                $parts[] = preg_quote($token, '/');
                continue;
            }

            $param = $route->getParameter($token->getName()) ?? $token;
            $parts[] = $param->getRegexFragment();
        }

        return '/^' . implode('', $parts) . '$/';
    }


    /**
     * Dump for glitch
     */
    public function glitchDump(): iterable
    {
        yield 'properties' => [
            '*index' => $this->index
        ];

        yield 'values' => $this->tokens;
    }
}
