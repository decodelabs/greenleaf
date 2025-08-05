<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use Attribute;
use DecodeLabs\Archetype;
use DecodeLabs\Coercion;
use JsonSerializable;

#[Attribute]
class Parameter implements JsonSerializable
{
    public protected(set) readonly string $name;
    public ?string $default = null;

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(
        array $data
    ): Parameter {
        if (isset($data['type'])) {
            $type = Coercion::asString($data['type']);
            unset($data['type']);
            $class = Archetype::resolve(Parameter::class, ucfirst($type));
        } else {
            $class = self::class;
        }

        // @phpstan-ignore-next-line
        return new $class(...$data);
    }

    public function __construct(
        string $name,
        ?string $default = null
    ) {
        $this->name = $name;
        $this->default = $default;
    }


    public function hasDefault(): bool
    {
        return $this->default !== null;
    }

    public function isMultiSegment(): bool
    {
        return false;
    }

    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>\w+)';
    }

    public function validate(
        ?string $value
    ): bool {
        if ($value === null) {
            if ($this->default === null) {
                return false;
            }

            $value = $this->default;
        }

        return true;
    }

    public function resolve(
        ?string $value
    ): mixed {
        return $value ?? $this->default;
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
