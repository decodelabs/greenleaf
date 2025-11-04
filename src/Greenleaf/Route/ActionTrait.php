<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use DecodeLabs\Archetype;
use DecodeLabs\Greenleaf\Route;
use DecodeLabs\Greenleaf\RouteTrait;
use DecodeLabs\Singularity\Url\Leaf as LeafUrl;
use Stringable;

/**
 * @phpstan-require-implements Route
 */
trait ActionTrait
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
        array $data,
        Archetype $archetype
    ): static {
        $parameters = [];

        foreach ($data['parameters'] ?? [] as $parameter) {
            if (is_array($parameter)) {
                $parameter = Parameter::fromArray($parameter, $archetype);
            }

            // @phpstan-ignore-next-line
            if ($parameter instanceof Parameter) {
                $parameters[] = $parameter;
            }
        }

        return new static(
            pattern: $data['pattern'],
            target: $data['target'] ?? null,
            method: $data['methods'] ?? [],
            parameters: $parameters
        );
    }

    /**
     * @param string|array<string> $method
     * @param array<Parameter> $parameters
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
            $this->addParameter($parameter);
        }
    }



    /**
     * @param array<string,string|Stringable|int|float|bool|null> $parameters
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
