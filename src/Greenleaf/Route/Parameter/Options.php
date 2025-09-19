<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter;

use Attribute;
use DecodeLabs\Greenleaf\Route\Parameter;

#[Attribute]
class Options extends Parameter
{
    /**
     * @var array<string>
     */
    protected array $values;

    /**
     * @param array<string> $values
     */
    public function __construct(
        string $name,
        array $values,
        ?string $default = null
    ) {
        parent::__construct($name, $default);
        $this->values = $values;
    }

    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>' . implode('|', array_map(
            fn (string $value) => preg_quote($value),
            $this->values
        )) . ')';
    }

    public function validate(
        ?string $value
    ): bool {
        return in_array($value ?? $this->default, $this->values, true);
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
            'type' => 'options',
            'name' => $this->name,
            'default' => $this->default,
            'values' => $this->values,
        ];
    }
}
