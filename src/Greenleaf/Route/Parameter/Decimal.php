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
class Decimal extends Parameter
{
    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>([0-9]+)(\.[0-9]+)?)';
    }


    public function validate(
        ?string $value
    ): bool {
        $value ??= $this->default;

        return
            $value !== null &&
            is_numeric($value);
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
            'type' => 'decimal',
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
