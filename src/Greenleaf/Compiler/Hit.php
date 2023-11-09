<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Compiler;

use DecodeLabs\Greenleaf\Route;

class Hit
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        protected readonly Route $route,
        protected readonly array $parameters,
        protected readonly ?string $queryString = null
    ) {
    }

    /**
     * Get route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * Get parameters
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get query string
     */
    public function getQueryString(): ?string
    {
        return $this->queryString;
    }
}
