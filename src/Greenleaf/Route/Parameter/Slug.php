<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter;

use Attribute;
use DecodeLabs\Dictum;
use DecodeLabs\Greenleaf\Route\Parameter;

#[Attribute]
class Slug extends Parameter
{
    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>[a-z0-9\-_]+)';
    }


    public function validate(
        ?string $value
    ): bool {
        $value ??= $this->default;
        return $value !== null;
    }

    public function resolve(
        ?string $value
    ): mixed {
        $value ??= $this->default;

        if ($value === null) {
            return null;
        }

        return Dictum::slug($value);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'slug',
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
