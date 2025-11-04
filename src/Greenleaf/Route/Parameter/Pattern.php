<?php

/**
 * Greenleaf
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route\Parameter;

use Attribute;
use DecodeLabs\Greenleaf\Route\Parameter;

#[Attribute]
class Pattern extends Parameter
{
    public function __construct(
        string $name,
        protected string $pattern,
        ?string $default = null
    ) {
        parent::__construct($name, $default);
    }

    public function getRegexFragment(): string
    {
        return '(?P<' . $this->name . '>(' . $this->pattern . '))';
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
            'type' => 'pattern',
            'pattern' => $this->pattern,
            'name' => $this->name,
            'default' => $this->default,
        ];
    }
}
