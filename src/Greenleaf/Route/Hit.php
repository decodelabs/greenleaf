<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Greenleaf\Route;

class Hit
{
    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(
        protected(set) Route $route,
        protected(set) array $parameters,
        protected(set) ?string $queryString = null
    ) {
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function getQueryString(): ?string
    {
        return $this->queryString;
    }
}
