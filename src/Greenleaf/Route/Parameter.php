<?php

/**
 * @package Greenleaf
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Greenleaf\Route;

use Attribute;
use DecodeLabs\Greenleaf\Route\Parameter\Validator;
use DecodeLabs\Greenleaf\Route\Parameter\ValidatorAbstract;
use JsonSerializable;

#[Attribute]
class Parameter implements JsonSerializable
{
    protected(set) readonly string $name;
    public ?Validator $validator;
    public ?string $default = null;

    /**
     * Init with properties
     *
     * @param string|array<string, mixed>|Validator|null $validate
     */
    public function __construct(
        string $name,
        string|array|Validator|null $validate = null,
        ?string $default = null
    ) {
        $this->name = $name;
        $this->validator = ValidatorAbstract::create($validate);
        $this->default = $default;
    }


    /**
     * Has default
     */
    public function hasDefault(): bool
    {
        return $this->default !== null;
    }


    /**
     * Is multi segment
     */
    public function isMultiSegment(): bool
    {
        if (!$this->validator) {
            return false;
        }

        return $this->validator->isMultiSegment();
    }


    /**
     * Get regex fragment
     */
    public function getRegexFragment(): string
    {
        if ($this->validator) {
            return $this->validator->getRegexFragment($this->name);
        }

        return '(?P<' . $this->name . '>\w+)';
    }


    /**
     * Validate value
     */
    public function validate(
        ?string $value
    ): bool {
        if ($value === null) {
            if ($this->default === null) {
                return false;
            }

            $value = $this->default;
        }

        if ($this->validator) {
            return $this->validator->validate($value);
        }

        return true;
    }

    /**
     * Resolve value
     */
    public function resolve(
        ?string $value
    ): mixed {
        if ($value === null) {
            $value = $this->default;
        }

        if ($this->validator) {
            $value = $this->validator->resolve($value);
        }

        return $value;
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'validate' => $this->validator?->jsonSerialize(),
            'default' => $this->default,
        ];
    }
}
