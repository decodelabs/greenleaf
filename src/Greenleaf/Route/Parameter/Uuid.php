<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter;

use Attribute;
use DecodeLabs\Greenleaf\Route\Parameter;
use DecodeLabs\Guidance;

#[Attribute]
class Uuid extends Parameter
{
    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+)?)';
    }


    public function validate(
        ?string $value
    ): bool {
        $value ??= $this->default;

        if (class_exists(Guidance::class)) {
            return Guidance::isValidUuid($value);
        }

        return $value !== null;
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'uuid',
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
