<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter;

use Attribute;
use DecodeLabs\Coercion;
use DecodeLabs\Greenleaf\Route\Parameter;

#[Attribute]
class Number extends Parameter
{
    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>[0-9]+)';
    }


    public function validate(
        ?string $value
    ): bool {
        $value ??= $this->default;

        return
            $value !== null &&
            ctype_digit($value);
    }

    public function resolve(
        ?string $value
    ): mixed {
        return Coercion::tryInt($value ?? $this->default);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'number',
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
