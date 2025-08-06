<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf;

use DecodeLabs\Greenleaf\Route\Hit;
use DecodeLabs\Greenleaf\Route\Parameter;
use DecodeLabs\Greenleaf\Route\Pattern;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Psr\Http\Message\UriInterface as Uri;
use Stringable;

interface Route extends JsonSerializable
{
    public Pattern $pattern { get; }

    /**
     * @var array<string,Parameter>
     */
    public array $parameters { get; }

    /**
     * @var array<string>
     */
    public array $methods { get; }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(
        array $data
    ): static;

    /**
     * @return $this
     */
    public function with(
        string $name
    ): static;

    public function parseParameters(): void;

    /**
     * @return $this
     */
    public function addParameter(
        Parameter $parameter
    ): static;

    public function getParameter(
        string $name
    ): ?Parameter;

    public function hasParameter(
        string $name
    ): bool;

    public function removeParameter(
        string $name
    ): static;


    public function forMethod(
        string ...$method
    ): static;

    public function hasMethod(
        string $method
    ): bool;

    public function acceptsMethod(
        string $method
    ): bool;

    public function removeMethod(
        string $method
    ): static;



    public function matchIn(
        string $method,
        Uri $uri
    ): ?Hit;

    /**
     * @param array<string,string|Stringable|int|float|bool|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit;


    /**
     * @param array<string, mixed> $parameters
     */
    public function handleIn(
        Context $context,
        PsrRequest $request,
        array $parameters
    ): PsrResponse;
}
