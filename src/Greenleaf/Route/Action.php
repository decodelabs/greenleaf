<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use Attribute;
use DecodeLabs\Greenleaf\Action as ActionInterface;
use DecodeLabs\Greenleaf\Context;
use DecodeLabs\Greenleaf\Request as LeafRequest;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrRequest;
use Stringable;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::IS_REPEATABLE)]
class Action implements Route, Bidirectional
{
    use RouteTrait;

    public LeafUrl $target;

    /**
     * @param array{
     *     pattern: string|Pattern,
     *     target?: string|LeafUrl|null,
     *     methods?: string|array<string>,
     *     parameters?: array<Parameter|array<string,mixed>>
     * } $data
     */
    public static function fromArray(
        array $data
    ): static {
        return new static(
            pattern: $data['pattern'],
            target: $data['target'] ?? null,
            method: $data['methods'] ?? [],
            parameters: $data['parameters'] ?? []
        );
    }

    /**
     * Init with properties
     *
     * @param string|array<string> $method
     * @param array<Parameter|array<string,mixed>> $parameters
     */
    final public function __construct(
        string|Pattern $pattern,
        string|LeafUrl|null $target = null,
        string|array $method = [],
        array $parameters = []
    ) {
        $this->pattern = $this->normalizePattern($pattern);

        if ($target === null) {
            $target = (string)$pattern;
        }

        if (is_string($target)) {
            $target = LeafUrl::fromString($target);
        }

        $this->target = $target;

        if (empty($method)) {
            $method = 'GET';
        }

        $this->forMethod(...(array)$method);

        foreach ($parameters as $parameter) {
            if (is_array($parameter)) {
                $parameter = Parameter::fromArray($parameter);
            }

            $this->addParameter($parameter);
        }
    }


    /**
     * Handle request
     */
    public function handleIn(
        Context $context,
        PsrRequest $request,
        array $parameters
    ): PsrResponse {
        $class = $context->archetype->resolve(ActionInterface::class, (string)$this->target);
        $action = new $class($context);

        $leafRequest = new LeafRequest(
            httpRequest: $request,
            leafUrl: $this->target,
            parameters: $parameters,
            route: $this
        );

        return $this->dispatchAction(
            request: $leafRequest,
            action: $action
        );
    }


    /**
     * @param array<string,string|Stringable|int|float|null> $parameters
     */
    public function matchOut(
        string|LeafUrl $uri,
        ?array $parameters = null
    ): ?Hit {
        return $this->matchActionOut(
            uri: $uri,
            parameters: $parameters,
            target: $this->target
        );
    }


    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->exportData([
            'target' => (string)$this->target
        ]);
    }
}
