<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;

class Request
{
    public function __construct(
        protected(set) LeafUrl $leafUrl,
        protected(set) PsrRequest $httpRequest,
        /** @var array<string,mixed> */
        protected(set) array $parameters,
        protected(set) Route $route
    ) {
    }

    public function replaceHttpRequest(
        PsrRequest $httpRequest
    ): void {
        $this->httpRequest = $httpRequest;
    }

    public function hasParameter(
        string $name
    ): bool {
        return isset($this->parameters[$name]);
    }

    public function getParameter(
        string $name
    ): mixed {
        return $this->parameters[$name] ?? null;
    }
}
