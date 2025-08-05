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
class Path extends Parameter
{
    public function isMultiSegment(): bool
    {
        return true;
    }

    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>.+?)';
    }

    public function validate(
        ?string $value
    ): bool {
        $value ??= $this->default;
        return $value !== null;
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'path',
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
