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
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface as Uri;
use Stringable;

interface Route
{
    public function getPattern(): Pattern;

    /**
     * @param string|array<string, mixed>|Validator|null $validate
     * @return $this
     */
    public function with(
        string $name,
        string|array|Validator|null $validate = null,
        ?string $default = null
    ): static;

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

    /**
     * @return array<string, Parameter>
     */
    public function getParameters(): array;


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

    /**
     * @return array<string>|null
     */
    public function getMethods(): ?array;



    public function matchIn(
        string $method,
        Uri $uri
    ): ?Hit;

    /**
     * @param array<string, string|Stringable|int|float|null> $params
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $params = null
    ): ?Hit;


    /**
     * @param array<string, mixed> $parameters
     */
    public function handleIn(
        Context $context,
        Request $request,
        array $parameters
    ): Response;
}
